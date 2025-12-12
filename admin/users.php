<?php
session_start();
require_once '../includes/config/Database.php';
require_once '../includes/config/Constants.php';
require_once '../includes/utils/Session.php';
require_once '../includes/models/User.php';

$session = new Session();
if(!$session->checkLogin() || !$session->isAdmin()) {
    header("Location: ../login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$userModel = new User($db);

$error = '';
$success = '';

// Actions sur les utilisateurs
if(isset($_GET['action'])) {
    $action = $_GET['action'];
    $user_id = $_GET['id'] ?? null;
    
    switch($action) {
        case 'toggle_status':
            if($user_id) {
                $user = $userModel->getUserById($user_id);
                if($user) {
                    $new_status = $user['status'] == 1 ? 0 : 1;
                    if($userModel->updateStatus($user_id, $new_status)) {
                        $success = "Statut de l'utilisateur mis à jour.";
                    } else {
                        $error = "Erreur lors de la mise à jour.";
                    }
                }
            }
            break;
            
        case 'delete':
            if($user_id && $user_id != $_SESSION['user_id']) {
                if($userModel->delete($user_id)) {
                    $success = "Utilisateur supprimé.";
                } else {
                    $error = "Erreur lors de la suppression.";
                }
            } else {
                $error = "Vous ne pouvez pas supprimer votre propre compte.";
            }
            break;
            
        case 'make_admin':
            if($user_id) {
                // Mettre à jour le rôle
                $query = "UPDATE users SET role = 'admin' WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(":id", $user_id);
                if($stmt->execute()) {
                    $success = "Utilisateur promu administrateur.";
                }
            }
            break;
            case 'add':
                include 'user_add_form.php';
                exit();
        case 'export':
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=users.csv');
    $output = fopen('php://output', 'w');
    // En-tête CSV
    fputcsv($output, ['ID', 'Nom', 'Email', 'Téléphone', 'Rôle', 'Statut', 'Inscrit le']);
    
    // Récupérer tous les utilisateurs
    foreach($users as $user) {
        fputcsv($output, [
            $user['id'],
            $user['nom'],
            $user['email'],
            $user['telephone'],
            $user['role'],
            $user['status'] == 1 ? 'Actif' : 'Bloqué',
            $user['created_at']
        ]);
    }
    fclose($output);
    exit();
    break;


    }
}

// Récupérer tous les utilisateurs
$stmt = $userModel->getAllUsers();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Utilisateurs - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #0d6efd;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .table-actions {
            white-space: nowrap;
        }
        .search-box {
            max-width: 300px;
        }
    </style>
</head>
<body>
    <?php include 'includes/admin_navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/admin_sidebar.php'; ?>
            
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="bi bi-people"></i> Gestion des utilisateurs
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="users.php?action=export" class="btn btn-outline-secondary me-2">
                            <i class="bi bi-download"></i> Exporter
                        </a>
                        <a href="users.php?action=add" class="btn btn-primary">
                            <i class="bi bi-person-plus"></i> Ajouter un utilisateur
                        </a>
                    </div>
                </div>
                
                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <!-- Filtres et recherche -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <form class="row g-2">
                            <div class="col-auto">
                                <input type="text" class="form-control" placeholder="Rechercher..." name="search">
                            </div>
                            <div class="col-auto">
                                <select class="form-select" name="role">
                                    <option value="">Tous les rôles</option>
                                    <option value="user">Utilisateurs</option>
                                    <option value="admin">Administrateurs</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <select class="form-select" name="status">
                                    <option value="">Tous les statuts</option>
                                    <option value="1">Actifs</option>
                                    <option value="0">Bloqués</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-filter"></i> Filtrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Tableau des utilisateurs -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Utilisateur</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Rôle</th>
                                <th>Statut</th>
                                <th>Inscrit le</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $user): ?>
                                <tr>
                                    <td>#<?php echo $user['id']; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-2">
                                                <?php echo strtoupper(substr($user['nom'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <strong><?php echo htmlspecialchars($user['nom']); ?></strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['telephone'] ?? '-'); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'primary'; ?>">
                                            <?php echo $user['role']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if($user['status'] == 1): ?>
                                            <span class="badge bg-success">Actif</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Bloqué</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                    <td class="table-actions">
                                        <div class="btn-group btn-group-sm">
                                            <a href="users.php?action=view&id=<?php echo $user['id']; ?>" 
                                               class="btn btn-outline-info" title="Voir">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="users.php?action=edit&id=<?php echo $user['id']; ?>" 
                                               class="btn btn-outline-warning" title="Modifier">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            
                                            <?php if($user['id'] != $_SESSION['user_id']): ?>
                                                <?php if($user['role'] != 'admin'): ?>
                                                    <a href="users.php?action=make_admin&id=<?php echo $user['id']; ?>" 
                                                       class="btn btn-outline-success" 
                                                       title="Promouvoir admin"
                                                       onclick="return confirm('Promouvoir cet utilisateur en administrateur ?')">
                                                        <i class="bi bi-shield-check"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <a href="users.php?action=toggle_status&id=<?php echo $user['id']; ?>" 
                                                   class="btn btn-outline-<?php echo $user['status'] == 1 ? 'warning' : 'success'; ?>"
                                                   title="<?php echo $user['status'] == 1 ? 'Bloquer' : 'Activer'; ?>">
                                                    <i class="bi bi-<?php echo $user['status'] == 1 ? 'slash-circle' : 'check-circle'; ?>"></i>
                                                </a>
                                                
                                                <a href="users.php?action=delete&id=<?php echo $user['id']; ?>" 
                                                   class="btn btn-outline-danger"
                                                   title="Supprimer"
                                                   onclick="return confirm('Supprimer définitivement cet utilisateur ?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <nav>
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled">
                            <a class="page-link" href="#">Précédent</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">Suivant</a>
                        </li>
                    </ul>
                </nav>
                
                <!-- Statistiques -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5><?php echo count($users); ?></h5>
                                <p class="text-muted mb-0">Utilisateurs total</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5><?php echo count(array_filter($users, fn($u) => $u['role'] == 'admin')); ?></h5>
                                <p class="text-muted mb-0">Administrateurs</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5><?php echo count(array_filter($users, fn($u) => $u['status'] == 1)); ?></h5>
                                <p class="text-muted mb-0">Utilisateurs actifs</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5><?php echo count(array_filter($users, fn($u) => $u['status'] == 0)); ?></h5>
                                <p class="text-muted mb-0">Utilisateurs bloqués</p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Recherche en temps réel
        document.querySelector('input[name="search"]').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    </script>
</body>
</html>