<?php
session_start();
require_once 'includes/config/Database.php';
require_once 'includes/config/Constants.php';
require_once 'includes/controllers/ReservationController.php';
require_once 'includes/utils/Session.php';

$session = new Session();
if(!$session->checkLogin()) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$reservationController = new ReservationController($db);

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Traiter la réservation
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirmer_reservation'])) {
    $trajet_id = $_POST['trajet_id'];
    $nombre_places = $_POST['nombre_places'];
    $moyen_paiement = $_POST['moyen_paiement'];
    
    $result = $reservationController->createReservation(
        $trajet_id,
        $user_id,
        $nombre_places,
        $moyen_paiement
    );
    
    if($result['success']) {
        $success = $result['message'];
        // Rediriger vers la page de confirmation
        header("Location: reservation_confirmation.php?id=" . $result['reservation_id']);
        exit();
    } else {
        $error = $result['message'];
    }
}

// Vérifier si on vient du panier
if(isset($_GET['action']) && $_GET['action'] == 'checkout') {
    // Récupérer les éléments du panier
    $panierModel = new Panier($db);
    $cartItems = $panierModel->getCartItems($user_id);
    
    if(empty($cartItems)) {
        header("Location: panier.php");
        exit();
    }
    
    // Pour simplifier, on prend le premier élément
    $firstItem = reset($cartItems);
    $trajet_id = $firstItem['trajet_id'];
    $nombre_places = $firstItem['nombre_places'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .reservation-container {
            max-width: 800px;
            margin: 30px auto;
        }
        .payment-methods .form-check {
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
        }
        .payment-methods .form-check-input:checked + .form-check-label {
            border-color: #0d6efd;
            background-color: #f0f8ff;
        }
        .reservation-summary {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <?php include 'includes/views/navbar.php'; ?>
    
    <div class="reservation-container">
        <div class="text-center mb-4">
            <h1>Finaliser votre réservation</h1>
            <p class="text-muted">Étape 3/3 - Paiement et confirmation</p>
        </div>
        
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-8">
                <!-- Formulaire de réservation -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Informations de paiement</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-4">
                                <h6>Moyen de paiement</h6>
                                <div class="payment-methods">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="moyen_paiement" 
                                               id="carte" value="carte" checked>
                                        <label class="form-check-label w-100" for="carte">
                                            <i class="bi bi-credit-card"></i> Carte bancaire
                                            <small class="d-block text-muted">Paiement sécurisé par Stripe</small>
                                        </label>
                                    </div>
                                    
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="moyen_paiement" 
                                               id="paypal" value="paypal">
                                        <label class="form-check-label w-100" for="paypal">
                                            <i class="bi bi-paypal"></i> PayPal
                                            <small class="d-block text-muted">Paiement rapide et sécurisé</small>
                                        </label>
                                    </div>
                                    
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="moyen_paiement" 
                                               id="especes" value="especes">
                                        <label class="form-check-label w-100" for="especes">
                                            <i class="bi bi-cash"></i> Paiement en espèces
                                            <small class="d-block text-muted">À régler directement au conducteur</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Pour la démo, on simplifie -->
                            <input type="hidden" name="trajet_id" value="<?php echo $trajet_id ?? ''; ?>">
                            <input type="hidden" name="nombre_places" value="<?php echo $nombre_places ?? 1; ?>">
                            
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> 
                                Pour cette démonstration, le paiement est simulé. 
                                En production, intégrez un système de paiement réel.
                            </div>
                            
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="conditions" required>
                                <label class="form-check-label" for="conditions">
                                    J'accepte les <a href="#" data-bs-toggle="modal" data-bs-target="#conditionsModal">conditions générales</a> 
                                    et j'ai pris connaissance du <a href="#" data-bs-toggle="modal" data-bs-target="#reglementModal">règlement intérieur</a>
                                </label>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" name="confirmer_reservation" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-circle"></i> Confirmer et payer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <!-- Récapitulatif -->
                <div class="reservation-summary">
                    <h5 class="mb-3">Récapitulatif</h5>
                    
                    <div class="mb-3">
                        <h6>Trajet</h6>
                        <p class="mb-1">
                            <strong><?php echo htmlspecialchars($trajet['lieu_depart'] ?? 'N/A'); ?> 
                            → <?php echo htmlspecialchars($trajet['lieu_arrivee'] ?? 'N/A'); ?></strong>
                        </p>
                        <small class="text-muted">
                            <?php echo date('d/m/Y', strtotime($trajet['date_trajet'] ?? date('Y-m-d'))); ?> 
                            à <?php echo $trajet['heure_depart'] ?? '--:--'; ?>
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <h6>Détails</h6>
                        <div class="d-flex justify-content-between">
                            <span>Places :</span>
                            <span><?php echo $nombre_places ?? 1; ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Prix unitaire :</span>
                            <span><?php echo $trajet['prix'] ?? 0; ?>€</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between h5">
                            <strong>Total :</strong>
                            <strong><?php echo ($trajet['prix'] ?? 0) * ($nombre_places ?? 1); ?>€</strong>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning small">
                        <i class="bi bi-exclamation-triangle"></i> 
                        Cette réservation est soumise à confirmation du conducteur.
                    </div>
                </div>
                
                <!-- Aide -->
                <div class="card">
                    <div class="card-body">
                        <h6><i class="bi bi-headset"></i> Assistance</h6>
                        <p class="small mb-2">Besoin d'aide pour votre réservation ?</p>
                        <a href="contact.php" class="btn btn-outline-secondary btn-sm">Contactez-nous</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div class="modal fade" id="conditionsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Conditions générales</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Conditions générales d'utilisation du service de covoiturage...</p>
                    <!-- Ajoutez ici vos CGV complètes -->
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="reglementModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Règlement intérieur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Règlement intérieur pour les trajets de covoiturage...</p>
                    <!-- Ajoutez ici votre règlement -->
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/views/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validation avant soumission
        document.querySelector('form').addEventListener('submit', function(e) {
            const conditions = document.getElementById('conditions');
            if(!conditions.checked) {
                e.preventDefault();
                alert('Vous devez accepter les conditions générales.');
                return false;
            }
        });
    </script>
</body>
</html>