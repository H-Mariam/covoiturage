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

// Récupérer les trajets les plus récents
$trajets = $trajetModel->getLatestTrajets(6);

// Vérifier si l'utilisateur est connecté
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - <?php echo SITE_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            margin-bottom: 50px;
        }
        .trajet-card {
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        .trajet-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .badge-dispo {
            background-color: #28a745;
        }
        .badge-complet {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
    <?php include 'includes/views/navbar.php'; ?>
    
    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 mb-4">Covoiturez facilement</h1>
            <p class="lead mb-4">Trouvez ou proposez un trajet en quelques clics</p>
            <?php if(!$isLoggedIn): ?>
                <a href="register.php" class="btn btn-primary btn-lg me-2">S'inscrire</a>
                <a href="login.php" class="btn btn-outline-light btn-lg">Se connecter</a>
            <?php else: ?>
                <a href="publier.php" class="btn btn-primary btn-lg me-2">Publier un trajet</a>
                <a href="trajets.php" class="btn btn-outline-light btn-lg">Voir tous les trajets</a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Liste des trajets récents -->
    <div class="container">
        <h2 class="mb-4">Trajets récents</h2>
        
        <?php if(empty($trajets)): ?>
            <div class="alert alert-info">
                Aucun trajet disponible pour le moment. Soyez le premier à en proposer !
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach($trajets as $trajet): ?>
                    <div class="col-md-4">
                        <div class="card trajet-card">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?php echo htmlspecialchars($trajet['lieu_depart']); ?> → 
                                    <?php echo htmlspecialchars($trajet['lieu_arrivee']); ?>
                                </h5>
                                <h6 class="card-subtitle mb-2 text-muted">
                                    <?php echo $helper->formatDate($trajet['date_trajet']); ?> à 
                                    <?php echo $trajet['heure_depart']; ?>
                                </h6>
                                <p class="card-text">
                                    <strong>Conducteur:</strong> <?php echo htmlspecialchars($trajet['conducteur_nom']); ?><br>
                                    <strong>Places:</strong> <?php echo $trajet['places_disponibles']; ?><br>
                                    <strong>Prix:</strong> 
                                    <?php echo $trajet['prix'] ? $trajet['prix'] . '€' : 'Gratuit'; ?>
                                </p>
                                <span class="badge <?php echo $trajet['places_disponibles'] > 0 ? 'badge-dispo' : 'badge-complet'; ?>">
                                    <?php echo $trajet['places_disponibles'] > 0 ? 'Disponible' : 'Complet'; ?>
                                </span>
                                <a href="trajet_detail.php?id=<?php echo $trajet['id']; ?>" class="btn btn-sm btn-outline-primary float-end">
                                    Voir détails
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="trajets.php" class="btn btn-primary">Voir tous les trajets</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Statistiques -->
    <div class="container mt-5">
        <div class="row text-center">
            <div class="col-md-4">
                <div class="stat-box p-4 border rounded">
                    <h3><?php echo count($trajets); ?></h3>
                    <p>Trajets disponibles</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box p-4 border rounded">
                    <h3>0</h3>
                    <p>Économie moyenne</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-box p-4 border rounded">
                    <h3>100%</h3>
                    <p>Sécurisé</p>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/views/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>