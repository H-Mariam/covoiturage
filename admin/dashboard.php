<?php
session_start();
require_once '../includes/config/Database.php';
require_once '../includes/config/Constants.php';

// Vérifier admin
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Connexion directe SIMPLE
try {
    $db = new PDO("mysql:host=localhost;dbname=covoituragee_db", "root", "");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // ========== STATISTIQUES ==========
    // Utilisateurs
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $totalUsers = $stmt->fetch()['count'] ?? 0;
    
    // Trajets
    $stmt = $db->query("SELECT COUNT(*) as count FROM trajets");
    $totalTrajets = $stmt->fetch()['count'] ?? 0;
    
    // Trajets actifs
    $stmt = $db->query("SELECT COUNT(*) as count FROM trajets WHERE status = 'active'");
    $activeTrajets = $stmt->fetch()['count'] ?? 0;
    // =============================
    // AJOUT : nombre de réservations
    // =============================
    $stmt = $db->query("SELECT COUNT(*) as count FROM reservations");
    $totalReservations = $stmt->fetch()['count'] ?? 0;
    
    // ========== DONNÉES RÉCENTES ==========
    // Derniers utilisateurs
    $stmt = $db->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
    $recentUsers = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    
    // Derniers trajets
    $stmt = $db->query("SELECT t.*, u.nom as conducteur_nom FROM trajets t 
                       INNER JOIN users u ON t.conducteur_id = u.id 
                       ORDER BY t.created_at DESC LIMIT 5");
    $recentTrajets = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    
} catch(PDOException $e) {
    die("Erreur base de données: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Covoiturage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            color: white;
            margin-bottom: 20px;
            text-align: center;
        }
        .card-users { background: linear-gradient(45deg, #4e54c8, #8f94fb); }
        .card-trajets { background: linear-gradient(45deg, #11998e, #38ef7d); }
        .card-active { background: linear-gradient(45deg, #ff416c, #ff4b2b); }
        .card-reservations { background: linear-gradient(45deg, #f46b45, #eea849); }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-speedometer2"></i> Admin Dashboard
            </a>
            <div class="navbar-nav ms-auto">
                <span class="nav-item nav-link text-light">
                    <i class="bi bi-person-circle"></i> <?php echo $_SESSION['user_name']; ?>
                </span>
                <a class="nav-item nav-link" href="../index.php">
                    <i class="bi bi-house"></i> Site public
                </a>
                <a class="nav-item nav-link" href="../logout.php">
                    <i class="bi bi-box-arrow-right"></i> Déconnexion
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="mb-4">Tableau de bord Administrateur</h1>
        
        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card card-users">
                    <i class="bi bi-people-fill" style="font-size: 2.5rem;"></i>
                    <h3><?php echo $totalUsers; ?></h3>
                    <p class="mb-0">Utilisateurs</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card card-trajets">
                    <i class="bi bi-car-front-fill" style="font-size: 2.5rem;"></i>
                    <h3><?php echo $totalTrajets; ?></h3>
                    <p class="mb-0">Trajets</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card card-active">
                    <i class="bi bi-check-circle-fill" style="font-size: 2.5rem;"></i>
                    <h3><?php echo $activeTrajets; ?></h3>
                    <p class="mb-0">Trajets actifs</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card card-reservations">
                    <i class="bi bi-calendar-check-fill" style="font-size: 2.5rem;"></i>
                    <h3><?php echo $totalReservations; ?></h3>
                    <p class="mb-0">Réservations</p>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Derniers utilisateurs -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-people"></i> Derniers utilisateurs inscrits
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if(empty($recentUsers)): ?>
                            <p class="text-muted">Aucun utilisateur</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nom</th>
                                            <th>Email</th>
                                            <th>Rôle</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($recentUsers as $user): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($user['nom']); ?></td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'primary'; ?>">
                                                        <?php echo $user['role']; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                        <a href="users.php" class="btn btn-outline-primary btn-sm">
                            Voir tous les utilisateurs
                        </a>
                    </div>
                </div>
            </div>

            <!-- Derniers trajets -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-car-front"></i> Derniers trajets publiés
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if(empty($recentTrajets)): ?>
                            <p class="text-muted">Aucun trajet</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Trajet</th>
                                            <th>Date</th>
                                            <th>Conducteur</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($recentTrajets as $trajet): ?>
                                            <tr>
                                                <td>
                                                    <?php echo htmlspecialchars($trajet['lieu_depart']); ?> 
                                                    → 
                                                    <?php echo htmlspecialchars($trajet['lieu_arrivee']); ?>
                                                </td>
                                                <td><?php echo date('d/m/Y', strtotime($trajet['date_trajet'])); ?></td>
                                                <td><?php echo htmlspecialchars($trajet['conducteur_nom']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                        <a href="trajets_admin.php" class="btn btn-outline-success btn-sm">
                            Voir tous les trajets
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="bi bi-lightning"></i> Actions rapides
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="users.php" class="btn btn-primary w-100">
                            <i class="bi bi-people"></i> Gérer utilisateurs
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="trajets_admin.php" class="btn btn-success w-100">
                            <i class="bi bi-car-front"></i> Gérer trajets
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="#" class="btn btn-info w-100">
                            <i class="bi bi-bar-chart"></i> Statistiques
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="../index.php" class="btn btn-warning w-100">
                            <i class="bi bi-house"></i> Retour au site
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>