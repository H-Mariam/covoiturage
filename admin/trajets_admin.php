<?php
session_start();
require_once '../includes/config/Database.php';
require_once '../includes/config/Constants.php';
require_once '../includes/utils/Session.php';
require_once '../includes/models/Trajet.php';
require_once '../includes/models/User.php';

$session = new Session();
if(!$session->checkLogin() || !$session->isAdmin()) {
    header("Location: ../login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$trajetModel = new Trajet($db);
$userModel = new User($db);

$error = '';
$success = '';

// Actions sur les trajets
if(isset($_GET['action'])) {
    $action = $_GET['action'];
    $trajet_id = $_GET['id'] ?? null;
    
    switch($action) {
        case 'validate':
            if($trajet_id) {
                if($trajetModel->updateStatus($trajet_id, 'active')) {
                    $success = "Trajet validé avec succès.";
                }
            }
            break;
            
        case 'reject':
            if($trajet_id) {
                if($trajetModel->updateStatus($trajet_id, 'cancelled')) {
                    $success = "Trajet rejeté.";
                }
            }
            break;
            
        case 'delete':
            if($trajet_id) {
                if($trajetModel->delete($trajet_id)) {
                    $success = "Trajet supprimé.";
                }
            }
            break;
    }
}

// CORRECTION ICI : LIGNE 73 - Ajouter fetchAll()
$trajets = $trajetModel->getAllTrajets()->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Trajets - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .status-badge {
            font-size: 0.8em;
            padding: 5px 10px;
        }
        .trajet-row {
            transition: all 0.3s;
        }
        .trajet-row:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <?php include 'includes/admin_navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/admin_sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="bi bi-car-front"></i> Gestion des trajets
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-download"></i> Exporter
                            </button>
                        </div>
                    </div>
                </div>
                
                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <!-- Filtres -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <form class="row g-2">
                            <div class="col-md-3">
                                <select class="form-select" name="status">
                                    <option value="">Tous les statuts</option>
                                    <option value="active">Actifs</option>
                                    <option value="pending">En attente</option>
                                    <option value="cancelled">Annulés</option>
                                    <option value="completed">Terminés</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="date" class="form-control" name="date" placeholder="Date">
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="search" placeholder="Rechercher...">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-filter"></i> Filtrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Tableau des trajets -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Trajet</th>
                                <th>Conducteur</th>
                                <th>Date/Heure</th>
                                <th>Places</th>
                                <th>Prix</th>
                                <th>Statut</th>
                                <th>Créé le</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($trajets as $trajet): ?>
                                <tr class="trajet-row">
                                    <td>#<?php echo $trajet['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($trajet['lieu_depart']); ?></strong><br>
                                        <small>→ <?php echo htmlspecialchars($trajet['lieu_arrivee']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($trajet['conducteur_nom']); ?><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($trajet['conducteur_email']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y', strtotime($trajet['date_trajet'])); ?><br>
                                        <small><?php echo $trajet['heure_depart']; ?></small>
                                    </td>
                                    <td><?php echo $trajet['places_disponibles']; ?></td>
                                    <td><?php echo $trajet['prix']; ?>€</td>
                                    <td>
                                        <?php
                                        $status_class = 'secondary';
                                        switch($trajet['status']) {
                                            case 'active': $status_class = 'success'; break;
                                            case 'pending': $status_class = 'warning'; break;
                                            case 'cancelled': $status_class = 'danger'; break;
                                            case 'completed': $status_class = 'info'; break;
                                        }
                                        ?>
                                        <span class="badge bg-<?php echo $status_class; ?> status-badge">
                                            <?php echo $trajet['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($trajet['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="../trajet_detail.php?id=<?php echo $trajet['id']; ?>" 
                                               target="_blank" class="btn btn-outline-info" title="Voir">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            
                                            <?php if($trajet['status'] == 'pending'): ?>
                                                <a href="?action=validate&id=<?php echo $trajet['id']; ?>" 
                                                   class="btn btn-outline-success" title="Valider">
                                                    <i class="bi bi-check-circle"></i>
                                                </a>
                                                <a href="?action=reject&id=<?php echo $trajet['id']; ?>" 
                                                   class="btn btn-outline-warning" title="Rejeter">
                                                    <i class="bi bi-x-circle"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <a href="?action=delete&id=<?php echo $trajet['id']; ?>" 
                                               class="btn btn-outline-danger" title="Supprimer"
                                               onclick="return confirm('Supprimer ce trajet ?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Statistiques -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <!-- CORRECTION ICI : LIGNE ~225 - count() au lieu de rowCount() -->
                                <h5><?php echo count($trajets); ?></h5>
                                <p class="text-muted mb-0">Total trajets</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <!-- Ces lignes fonctionnent maintenant car $trajets est un tableau -->
                                <h5><?php echo count(array_filter($trajets, fn($t) => $t['status'] == 'active')); ?></h5>
                                <p class="text-muted mb-0">Trajets actifs</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5><?php echo count(array_filter($trajets, fn($t) => $t['status'] == 'pending')); ?></h5>
                                <p class="text-muted mb-0">En attente</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h5><?php echo count(array_filter($trajets, fn($t) => $t['status'] == 'cancelled')); ?></h5>
                                <p class="text-muted mb-0">Annulés</p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>