<?php
session_start();
require_once 'includes/config/Database.php';
require_once 'includes/config/Constants.php';
require_once 'includes/models/Trajet.php';
require_once 'includes/models/Reservation.php';
require_once 'includes/utils/Session.php';
require_once 'includes/utils/Helper.php';

$session = new Session();
if(!$session->checkLogin()) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$trajetModel = new Trajet($db);
$reservationModel = new Reservation($db);
$helper = new Helper();

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Gérer la suppression d'un trajet
if(isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $trajet_id = $_GET['id'];
    $trajet = $trajetModel->getTrajetById($trajet_id);
    
    // Vérifier que l'utilisateur est bien le conducteur
    if($trajet && $trajet['conducteur_id'] == $user_id) {
        if($trajetModel->delete($trajet_id)) {
            $success = "Trajet supprimé avec succès.";
        } else {
            $error = "Erreur lors de la suppression du trajet.";
        }
    }
}

// Récupérer les trajets de l'utilisateur
$trajetsUtilisateur = $trajetModel->getTrajetsByConducteur($user_id);

// Récupérer les réservations de l'utilisateur
$reservationsUtilisateur = $reservationModel->getByPassagerId($user_id);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes trajets - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .dashboard-tabs .nav-link {
            color: #495057;
            font-weight: 500;
        }
        .dashboard-tabs .nav-link.active {
            color: #0d6efd;
            border-bottom: 3px solid #0d6efd;
        }
        .trajet-card {
            border-left: 4px solid #0d6efd;
        }
        .reservation-card {
            border-left: 4px solid #28a745;
        }
        .status-badge {
            font-size: 0.8em;
            padding: 5px 10px;
        }
    </style>
</head>
<body>
    <?php include 'includes/views/navbar.php'; ?>
    
    <div class="container mt-4">
        <h1 class="mb-4">Mes trajets</h1>
        
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <!-- Onglets -->
        <ul class="nav nav-tabs dashboard-tabs mb-4" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="trajets-tab" data-bs-toggle="tab" data-bs-target="#trajets" type="button">
                    <i class="bi bi-car-front"></i> Mes trajets proposés
                    <span class="badge bg-primary ms-1"><?php echo count($trajetsUtilisateur); ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="reservations-tab" data-bs-toggle="tab" data-bs-target="#reservations" type="button">
                    <i class="bi bi-calendar-check"></i> Mes réservations
                    <span class="badge bg-success ms-1"><?php echo count($reservationsUtilisateur); ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="publier-tab" data-bs-toggle="tab" data-bs-target="#publier" type="button">
                    <i class="bi bi-plus-circle"></i> Publier un nouveau trajet
                </button>
            </li>
        </ul>
        
        <!-- Contenu des onglets -->
        <div class="tab-content" id="myTabContent">
            <!-- Onglet 1: Mes trajets proposés -->
            <div class="tab-pane fade show active" id="trajets" role="tabpanel">
                <?php if(empty($trajetsUtilisateur)): ?>
                    <div class="alert alert-info">
                        <h5>Vous n'avez pas encore proposé de trajet.</h5>
                        <p>Partagez votre trajet et économisez sur vos frais de déplacement !</p>
                        <a href="publier.php" class="btn btn-primary">Publier mon premier trajet</a>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach($trajetsUtilisateur as $trajet): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card trajet-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <h5 class="card-title mb-0">
                                                <?php echo htmlspecialchars($trajet['lieu_depart']); ?> 
                                                → 
                                                <?php echo htmlspecialchars($trajet['lieu_arrivee']); ?>
                                            </h5>
                                            <span class="badge bg-<?php echo $trajet['status'] == 'active' ? 'success' : 'secondary'; ?> status-badge">
                                                <?php echo $trajet['status']; ?>
                                            </span>
                                        </div>
                                        
                                        <p class="card-text text-muted">
                                            <i class="bi bi-calendar"></i> 
                                            <?php echo $helper->formatDate($trajet['date_trajet']); ?> 
                                            à <?php echo $trajet['heure_depart']; ?>
                                        </p>
                                        
                                        <div class="mb-3">
                                            <p class="mb-1">
                                                <strong>Places :</strong> 
                                                <?php echo $trajet['places_disponibles']; ?> disponible(s)
                                            </p>
                                            <p class="mb-1">
                                                <strong>Prix :</strong> 
                                                <?php echo $trajet['prix'] > 0 ? $trajet['prix'] . '€' : 'Gratuit'; ?>
                                            </p>
                                            <p class="mb-0">
                                                <strong>Réservations :</strong> 
                                                <?php echo $trajet['reservations_count']; ?>
                                            </p>
                                        </div>
                                        
                                        <div class="btn-group w-100">
                                            <a href="trajet_detail.php?id=<?php echo $trajet['id']; ?>" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-eye"></i> Voir
                                            </a>
                                            <a href="publier.php?edit=<?php echo $trajet['id']; ?>" 
                                               class="btn btn-outline-warning btn-sm">
                                                <i class="bi bi-pencil"></i> Modifier
                                            </a>
                                            <a href="?action=delete&id=<?php echo $trajet['id']; ?>" 
                                               class="btn btn-outline-danger btn-sm"
                                               onclick="return confirm('Supprimer ce trajet ?')">
                                                <i class="bi bi-trash"></i> Supprimer
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Onglet 2: Mes réservations -->
            <div class="tab-pane fade" id="reservations" role="tabpanel">
                <?php if(empty($reservationsUtilisateur)): ?>
                    <div class="alert alert-info">
                        <h5>Vous n'avez pas encore réservé de trajet.</h5>
                        <p>Découvrez les trajets disponibles et voyagez à moindre coût !</p>
                        <a href="trajets.php" class="btn btn-primary">Chercher un trajet</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Trajet</th>
                                    <th>Date</th>
                                    <th>Places</th>
                                    <th>Prix total</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($reservationsUtilisateur as $reservation): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($reservation['lieu_depart']); ?> 
                                            → <?php echo htmlspecialchars($reservation['lieu_arrivee']); ?></strong><br>
                                            <small class="text-muted">
                                                Conducteur: <?php echo htmlspecialchars($reservation['conducteur_nom']); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php echo $helper->formatDate($reservation['date_trajet']); ?><br>
                                            <small><?php echo $reservation['heure_depart']; ?></small>
                                        </td>
                                        <td><?php echo $reservation['nombre_places']; ?></td>
                                        <td><?php echo $reservation['prix_total']; ?>€</td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                switch($reservation['status']) {
                                                    case 'confirmed': echo 'success'; break;
                                                    case 'pending': echo 'warning'; break;
                                                    case 'cancelled': echo 'danger'; break;
                                                    default: echo 'secondary';
                                                }
                                            ?>">
                                                <?php echo $reservation['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="trajet_detail.php?id=<?php echo $reservation['trajet_id']; ?>" 
                                                class="btn btn-outline-primary">  <!-- Espace ici aussi si besoin -->
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <?php if($reservation['status'] == 'confirmed'): ?>
                                                    <a href="contact.php?to=<?php echo $reservation['conducteur_id']; ?>" 
                                                    class="btn btn-outline-info">  <!-- ← CORRECTION ICI -->
                                                        <i class="bi bi-chat"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Onglet 3: Publier un trajet -->
            <div class="tab-pane fade" id="publier" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Publier un nouveau trajet</h5>
                        <p class="card-text">
                            Partagez votre trajet et réduisez vos frais de déplacement.
                            Remplissez le formulaire ci-dessous pour publier votre trajet.
                        </p>
                        <a href="publier.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Publier un trajet
                        </a>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <i class="bi bi-currency-euro text-primary" style="font-size: 2rem;"></i>
                                <h5 class="card-title mt-3">Économisez</h5>
                                <p class="card-text">Partagez vos frais de route avec d'autres voyageurs.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <i class="bi bi-people text-success" style="font-size: 2rem;"></i>
                                <h5 class="card-title mt-3">Rencontrez</h5>
                                <p class="card-text">Faites de nouvelles rencontres pendant vos trajets.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <i class="bi bi-tree text-warning" style="font-size: 2rem;"></i>
                                <h5 class="card-title mt-3">Écologique</h5>
                                <p class="card-text">Réduisez votre empreinte carbone en covoiturant.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/views/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Activation des onglets Bootstrap
        const triggerTabList = [].slice.call(document.querySelectorAll('#myTab button'))
        triggerTabList.forEach(function (triggerEl) {
            const tabTrigger = new bootstrap.Tab(triggerEl)
            triggerEl.addEventListener('click', function (event) {
                event.preventDefault()
                tabTrigger.show()
            })
        })
    </script>
</body>
</html>