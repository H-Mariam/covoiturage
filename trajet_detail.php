<?php
session_start();
require_once 'includes/config/Database.php';
require_once 'includes/config/Constants.php';
require_once 'includes/models/Trajet.php';
require_once 'includes/models/Reservation.php';
require_once 'includes/utils/Helper.php';

$database = new Database();
$db = $database->getConnection();
$trajetModel = new Trajet($db);
$reservationModel = new Reservation($db);
$helper = new Helper();

// Vérifier si l'ID du trajet est passé en paramètre
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: trajets.php");
    exit();
}

$trajet_id = $_GET['id'];
$trajet = $trajetModel->getTrajetById($trajet_id);

// Si le trajet n'existe pas
if(!$trajet) {
    header("Location: trajets.php");
    exit();
}

// Vérifier si l'utilisateur est connecté
$isLoggedIn = isset($_SESSION['user_id']);
$isConducteur = $isLoggedIn && $_SESSION['user_id'] == $trajet['conducteur_id'];
$canReserve = $isLoggedIn && !$isConducteur && $trajet['places_disponibles'] > 0;

// Gérer l'ajout au panier
if($isLoggedIn && isset($_POST['add_to_cart']) && $canReserve) {
    $nombre_places = $_POST['nombre_places'] ?? 1;
    
    // Ajouter au panier
    $query = "INSERT INTO panier (user_id, trajet_id, nombre_places) 
              VALUES (:user_id, :trajet_id, :nombre_places)
              ON DUPLICATE KEY UPDATE nombre_places = :nombre_places";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->bindParam(':trajet_id', $trajet_id);
    $stmt->bindParam(':nombre_places', $nombre_places);
    
    if($stmt->execute()) {
        $success = "Trajet ajouté au panier !";
    } else {
        $error = "Erreur lors de l'ajout au panier.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du trajet - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .trajet-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
        }
        .info-card {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .price-display {
            font-size: 2rem;
            font-weight: bold;
            color: #198754;
        }
        .places-badge {
            font-size: 1rem;
            padding: 10px 20px;
        }
    </style>
</head>
<body>
    <?php include 'includes/views/navbar.php'; ?>
    
    <!-- En-tête du trajet -->
    <div class="trajet-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-4">
                        <?php echo htmlspecialchars($trajet['lieu_depart']); ?> 
                        <i class="bi bi-arrow-right"></i> 
                        <?php echo htmlspecialchars($trajet['lieu_arrivee']); ?>
                    </h1>
                    <p class="lead">
                        <i class="bi bi-calendar"></i> 
                        <?php echo $helper->formatDate($trajet['date_trajet']); ?> 
                        à <?php echo $trajet['heure_depart']; ?>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <?php if($trajet['prix'] > 0): ?>
                        <div class="price-display"><?php echo $trajet['prix']; ?>€</div>
                    <?php else: ?>
                        <div class="price-display text-warning">GRATUIT</div>
                    <?php endif; ?>
                    <span class="badge <?php echo $trajet['places_disponibles'] > 0 ? 'bg-success' : 'bg-danger'; ?> places-badge">
                        <?php echo $trajet['places_disponibles']; ?> place(s) disponible(s)
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if(isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Informations principales -->
            <div class="col-md-8">
                <div class="info-card">
                    <h3>Informations du trajet</h3>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Lieu de départ :</strong><br>
                            <?php echo htmlspecialchars($trajet['lieu_depart']); ?></p>
                            
                            <p><strong>Lieu d'arrivée :</strong><br>
                            <?php echo htmlspecialchars($trajet['lieu_arrivee']); ?></p>
                            
                            <p><strong>Date et heure :</strong><br>
                            <?php echo $helper->formatDate($trajet['date_trajet']); ?> à <?php echo $trajet['heure_depart']; ?></p>
                        </div>
                        
                        <div class="col-md-6">
                            <p><strong>Places disponibles :</strong><br>
                            <?php echo $trajet['places_disponibles']; ?> place(s)</p>
                            
                            <p><strong>Prix par personne :</strong><br>
                            <?php echo $trajet['prix'] > 0 ? $trajet['prix'] . '€' : 'Gratuit'; ?></p>
                            
                            <?php if($trajet['vehicule']): ?>
                                <p><strong>Véhicule :</strong><br>
                                <?php echo htmlspecialchars($trajet['vehicule']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if($trajet['description']): ?>
                        <div class="mt-3">
                            <h5>Description :</h5>
                            <p class="text-muted"><?php echo nl2br(htmlspecialchars($trajet['description'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Informations conducteur -->
                <div class="info-card">
                    <h3>Informations du conducteur</h3>
                    <hr>
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" 
                                 style="width: 60px; height: 60px; font-size: 1.5rem;">
                                <?php echo strtoupper(substr($trajet['conducteur_nom'], 0, 1)); ?>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5><?php echo htmlspecialchars($trajet['conducteur_nom']); ?></h5>
                            <p class="text-muted mb-0">
                                <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($trajet['conducteur_email']); ?>
                            </p>
                            <?php if($trajet['telephone']): ?>
                                <p class="text-muted mb-0">
                                    <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($trajet['telephone']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Réservation -->
            <div class="col-md-4">
                <div class="info-card bg-light">
                    <h3>Réserver ce trajet</h3>
                    <hr>
                    
                    <?php if(!$isLoggedIn): ?>
                        <div class="alert alert-info">
                            <p>Vous devez être connecté pour réserver ce trajet.</p>
                            <div class="d-grid gap-2">
                                <a href="login.php" class="btn btn-primary">Se connecter</a>
                                <a href="register.php" class="btn btn-outline-primary">S'inscrire</a>
                            </div>
                        </div>
                    
                    <?php elseif($isConducteur): ?>
                        <div class="alert alert-warning">
                            <p>Vous êtes le conducteur de ce trajet.</p>
                            <a href="mes_trajets.php" class="btn btn-outline-primary">Gérer mes trajets</a>
                        </div>
                    
                    <?php elseif($trajet['places_disponibles'] <= 0): ?>
                        <div class="alert alert-danger">
                            <p>Ce trajet est complet.</p>
                        </div>
                    
                    <?php else: ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="nombre_places" class="form-label">Nombre de places</label>
                                <select class="form-select" id="nombre_places" name="nombre_places">
                                    <?php for($i = 1; $i <= min(4, $trajet['places_disponibles']); $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?> place(s)</option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Prix total</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="prix_total" 
                                           value="<?php echo $trajet['prix']; ?>€" readonly>
                                    <span class="input-group-text" id="prix_dynamic">par personne</span>
                                </div>
                                <small class="text-muted" id="total_calc">Total : <?php echo $trajet['prix']; ?>€</small>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" name="add_to_cart" class="btn btn-primary btn-lg">
                                    <i class="bi bi-cart-plus"></i> Ajouter au panier
                                </button>
                                <a href="panier.php" class="btn btn-outline-primary">
                                    <i class="bi bi-cart"></i> Voir mon panier
                                </a>
                            </div>
                        </form>
                    <?php endif; ?>
                    
                    <hr>
                    <div class="text-center">
                        <small class="text-muted">
                            <i class="bi bi-shield-check"></i> Paiement sécurisé<br>
                            <i class="bi bi-arrow-counterclockwise"></i> Annulation possible sous conditions
                        </small>
                    </div>
                </div>
                
                <!-- Actions supplémentaires -->
                <div class="info-card mt-3">
                    <h5>Actions</h5>
                    <div class="d-grid gap-2">
                        <a href="trajets.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Retour aux trajets
                        </a>
                        <?php if($isLoggedIn && $_SESSION['role'] == 'admin'): ?>
                            <a href="admin/trajets_admin.php?action=edit&id=<?php echo $trajet_id; ?>" 
                               class="btn btn-outline-warning">
                                <i class="bi bi-pencil"></i> Modifier (Admin)
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/views/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Calcul dynamique du prix total
        document.getElementById('nombre_places').addEventListener('change', function() {
            const places = this.value;
            const prixUnitaire = <?php echo $trajet['prix']; ?>;
            const total = places * prixUnitaire;
            
            document.getElementById('prix_total').value = total.toFixed(2) + '€';
            document.getElementById('total_calc').textContent = 'Total : ' + total.toFixed(2) + '€';
            
            if(places > 1) {
                document.getElementById('prix_dynamic').textContent = places + ' personnes';
            } else {
                document.getElementById('prix_dynamic').textContent = 'par personne';
            }
        });
    </script>
</body>
</html>