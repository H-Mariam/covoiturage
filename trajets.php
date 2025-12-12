<?php
session_start();
require_once 'includes/config/Database.php';
require_once 'includes/config/Constants.php';
require_once 'includes/models/Trajet.php';
require_once 'includes/utils/Helper.php';

$database = new Database();
$db = $database->getConnection();
$trajetModel = new Trajet($db);
$helper = new Helper();

// Récupérer les filtres
$lieu_depart = $_GET['lieu_depart'] ?? '';
$lieu_arrivee = $_GET['lieu_arrivee'] ?? '';
$date = $_GET['date'] ?? '';
$min_places = $_GET['min_places'] ?? 1;

// Récupérer les trajets avec filtres
$trajets = $trajetModel->getTrajetsWithFilters($lieu_depart, $lieu_arrivee, $date, $min_places);

// Vérifier si l'utilisateur est connecté
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tous les trajets - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .trajet-item {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        .trajet-item:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .price-badge {
            font-size: 1.2em;
            padding: 8px 15px;
        }
        .places-badge {
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <?php include 'includes/views/navbar.php'; ?>
    
    <div class="container mt-4">
        <h1 class="mb-4">Rechercher un trajet</h1>
        
        <!-- Filtres -->
        <div class="filter-section">
            <form method="GET" action="">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="lieu_depart" class="form-label">Départ</label>
                        <input type="text" class="form-control" id="lieu_depart" name="lieu_depart" 
                               value="<?php echo htmlspecialchars($lieu_depart); ?>" placeholder="Ville de départ">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="lieu_arrivee" class="form-label">Arrivée</label>
                        <input type="text" class="form-control" id="lieu_arrivee" name="lieu_arrivee" 
                               value="<?php echo htmlspecialchars($lieu_arrivee); ?>" placeholder="Ville d'arrivée">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date" name="date" 
                               value="<?php echo htmlspecialchars($date); ?>" min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="col-md-2">
                        <label for="min_places" class="form-label">Places min</label>
                        <select class="form-control" id="min_places" name="min_places">
                            <option value="1" <?php echo $min_places == 1 ? 'selected' : ''; ?>>1+</option>
                            <option value="2" <?php echo $min_places == 2 ? 'selected' : ''; ?>>2+</option>
                            <option value="3" <?php echo $min_places == 3 ? 'selected' : ''; ?>>3+</option>
                            <option value="4" <?php echo $min_places == 4 ? 'selected' : ''; ?>>4+</option>
                        </select>
                    </div>
                    
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Résultats -->
        <div class="row">
            <div class="col-md-12">
                <h3 class="mb-3">
                    <?php echo count($trajets); ?> trajet<?php echo count($trajets) > 1 ? 's' : ''; ?> trouvé<?php echo count($trajets) > 1 ? 's' : ''; ?>
                </h3>
                
                <?php if(empty($trajets)): ?>
                    <div class="alert alert-info">
                        Aucun trajet ne correspond à vos critères. Essayez d'élargir votre recherche.
                    </div>
                <?php else: ?>
                    <?php foreach($trajets as $trajet): ?>
                        <div class="trajet-item">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h4>
                                        <?php echo htmlspecialchars($trajet['lieu_depart']); ?> 
                                        → 
                                        <?php echo htmlspecialchars($trajet['lieu_arrivee']); ?>
                                    </h4>
                                    <p class="text-muted mb-2">
                                        <i class="bi bi-calendar"></i> 
                                        <?php echo $helper->formatDate($trajet['date_trajet']); ?> 
                                        à <?php echo $trajet['heure_depart']; ?>
                                    </p>
                                    <p class="mb-2">
                                        <strong>Conducteur:</strong> 
                                        <?php echo htmlspecialchars($trajet['conducteur_nom']); ?>
                                    </p>
                                    <?php if($trajet['vehicule']): ?>
                                        <p class="mb-2">
                                            <strong>Véhicule:</strong> 
                                            <?php echo htmlspecialchars($trajet['vehicule']); ?>
                                        </p>
                                    <?php endif; ?>
                                    <?php if($trajet['description']): ?>
                                        <p class="mb-2">
                                            <em><?php echo htmlspecialchars($trajet['description']); ?></em>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-4 text-end">
                                    <?php if($trajet['prix'] > 0): ?>
                                        <span class="badge bg-success price-badge">
                                            <?php echo $trajet['prix']; ?>€
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-info price-badge">GRATUIT</span>
                                    <?php endif; ?>
                                    
                                    <div class="mt-2">
                                        <span class="badge bg-secondary places-badge">
                                            <?php echo $trajet['places_disponibles']; ?> place<?php echo $trajet['places_disponibles'] > 1 ? 's' : ''; ?> disponible<?php echo $trajet['places_disponibles'] > 1 ? 's' : ''; ?>
                                        </span>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <a href="trajet_detail.php?id=<?php echo $trajet['id']; ?>" 
                                           class="btn btn-outline-primary me-2">
                                            Voir détails
                                        </a>
                                        <?php if($isLoggedIn && $trajet['places_disponibles'] > 0 && $trajet['conducteur_id'] != $_SESSION['user_id']): ?>
                                            <a href="panier.php?action=add&trajet_id=<?php echo $trajet['id']; ?>" 
                                               class="btn btn-primary">
                                                Réserver
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/views/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>