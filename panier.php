<?php
session_start();
require_once 'includes/config/Database.php';
require_once 'includes/config/Constants.php';
require_once 'includes/models/Trajet.php';
require_once 'includes/models/Panier.php';
require_once 'includes/utils/Session.php';

$session = new Session();
if(!$session->checkLogin()) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$panierModel = new Panier($db);
$trajetModel = new Trajet($db);

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Gérer les actions sur le panier
if(isset($_GET['action'])) {
    $action = $_GET['action'];
    $trajet_id = $_GET['trajet_id'] ?? null;
    
    switch($action) {
        case 'add':
            if($trajet_id) {
                $nombre_places = $_GET['places'] ?? 1;
                $result = $panierModel->addToCart($user_id, $trajet_id, $nombre_places);
                if($result) {
                    $success = "Trajet ajouté au panier !";
                } else {
                    $error = "Erreur lors de l'ajout au panier.";
                }
            }
            break;
            
        case 'remove':
            if($trajet_id) {
                $result = $panierModel->removeFromCart($user_id, $trajet_id);
                if($result) {
                    $success = "Trajet retiré du panier.";
                } else {
                    $error = "Erreur lors de la suppression.";
                }
            }
            break;
            
        case 'clear':
            $result = $panierModel->clearCart($user_id);
            if($result) {
                $success = "Panier vidé avec succès.";
            } else {
                $error = "Erreur lors du vidage du panier.";
            }
            break;
            
        case 'update':
            if($trajet_id && isset($_POST['quantite'])) {
                $quantite = $_POST['quantite'];
                $result = $panierModel->updateCartItem($user_id, $trajet_id, $quantite);
                if($result) {
                    $success = "Quantité mise à jour.";
                } else {
                    $error = "Erreur lors de la mise à jour.";
                }
            }
            break;
    }
}

// Récupérer le contenu du panier
$panierItems = $panierModel->getCartItems($user_id);
$total = 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon panier - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .cart-container {
            max-width: 1200px;
            margin: 30px auto;
        }
        .cart-item {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .cart-summary {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            position: sticky;
            top: 20px;
        }
        .quantity-input {
            width: 70px;
            text-align: center;
        }
        .empty-cart {
            text-align: center;
            padding: 50px;
        }
    </style>
</head>
<body>
    <?php include 'includes/views/navbar.php'; ?>
    
    <div class="cart-container">
        <h1 class="mb-4">Mon panier</h1>
        
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-lg-8">
                <?php if(empty($panierItems)): ?>
                    <div class="empty-cart">
                        <i class="bi bi-cart-x" style="font-size: 4rem; color: #ccc;"></i>
                        <h3 class="mt-3">Votre panier est vide</h3>
                        <p class="text-muted">Ajoutez des trajets à votre panier pour les réserver.</p>
                        <a href="trajets.php" class="btn btn-primary mt-3">
                            <i class="bi bi-search"></i> Chercher des trajets
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach($panierItems as $item): 
                        $trajet = $trajetModel->getTrajetById($item['trajet_id']);
                        if(!$trajet) continue;
                        
                        $itemTotal = $trajet['prix'] * $item['nombre_places'];
                        $total += $itemTotal;
                    ?>
                        <div class="cart-item">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5><?php echo htmlspecialchars($trajet['lieu_depart']); ?> → <?php echo htmlspecialchars($trajet['lieu_arrivee']); ?></h5>
                                    <p class="text-muted mb-2">
                                        <i class="bi bi-calendar"></i> 
                                        <?php echo date('d/m/Y', strtotime($trajet['date_trajet'])); ?> 
                                        à <?php echo $trajet['heure_depart']; ?>
                                    </p>
                                    <p class="mb-2">
                                        <strong>Conducteur:</strong> <?php echo htmlspecialchars($trajet['conducteur_nom']); ?>
                                    </p>
                                    <p class="mb-0">
                                        <strong>Prix unitaire:</strong> <?php echo $trajet['prix']; ?>€
                                    </p>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div>
                                            <label class="form-label">Quantité</label>
                                            <form method="POST" action="?action=update&trajet_id=<?php echo $item['trajet_id']; ?>" class="d-flex">
                                                <input type="number" name="quantite" min="1" max="<?php echo $trajet['places_disponibles']; ?>" 
                                                       value="<?php echo $item['nombre_places']; ?>" class="form-control quantity-input me-2">
                                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-check"></i>
                                                </button>
                                            </form>
                                        </div>
                                        
                                        <div class="text-end">
                                            <div class="h5"><?php echo number_format($itemTotal, 2); ?>€</div>
                                            <a href="?action=remove&trajet_id=<?php echo $item['trajet_id']; ?>" 
                                               class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i> Supprimer
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-info small">
                                        <i class="bi bi-info-circle"></i> 
                                        <?php echo $trajet['places_disponibles']; ?> place(s) disponible(s)
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="d-flex justify-content-between">
                        <a href="trajets.php" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-left"></i> Continuer mes recherches
                        </a>
                        <a href="?action=clear" class="btn btn-outline-danger" 
                           onclick="return confirm('Vider tout le panier ?')">
                            <i class="bi bi-trash"></i> Vider le panier
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="col-lg-4">
                <div class="cart-summary">
                    <h3 class="mb-4">Résumé</h3>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Sous-total</span>
                            <span><?php echo number_format($total, 2); ?>€</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Frais de service</span>
                            <span>0.00€</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between h4">
                            <strong>Total</strong>
                            <strong><?php echo number_format($total, 2); ?>€</strong>
                        </div>
                    </div>
                    
                    <?php if(!empty($panierItems)): ?>
                        <a href="reservation.php?action=checkout" class="btn btn-primary btn-lg w-100 mb-3">
                            <i class="bi bi-credit-card"></i> Procéder au paiement
                        </a>
                    <?php endif; ?>
                    
                    <div class="small text-muted">
                        <p><i class="bi bi-shield-check"></i> Paiement 100% sécurisé</p>
                        <p><i class="bi bi-arrow-counterclockwise"></i> Annulation gratuite 24h avant</p>
                    </div>
                </div>
                
                <!-- Aide -->
                <div class="mt-4">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title"><i class="bi bi-question-circle"></i> Besoin d'aide ?</h6>
                            <p class="card-text small">
                                Pour toute question concernant votre réservation, contactez-nous.
                            </p>
                            <a href="contact.php" class="btn btn-outline-secondary btn-sm">Nous contacter</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/views/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>