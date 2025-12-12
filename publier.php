<?php
session_start();
require_once 'includes/config/Database.php';
require_once 'includes/config/Constants.php';
require_once 'includes/controllers/TrajetController.php';
require_once 'includes/utils/Session.php';

$session = new Session();
if(!$session->checkLogin()) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$trajetController = new TrajetController($db);

$error = '';
$success = '';

// Traitement du formulaire
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lieu_depart = $_POST['lieu_depart'] ?? '';
    $lieu_arrivee = $_POST['lieu_arrivee'] ?? '';
    $date_trajet = $_POST['date_trajet'] ?? '';
    $heure_depart = $_POST['heure_depart'] ?? '';
    $places_disponibles = $_POST['places_disponibles'] ?? '';
    $prix = $_POST['prix'] ?? '';
    $description = $_POST['description'] ?? '';
    $vehicule = $_POST['vehicule'] ?? '';
    
    $result = $trajetController->publierTrajet(
        $_SESSION['user_id'],
        $lieu_depart,
        $lieu_arrivee,
        $date_trajet,
        $heure_depart,
        $places_disponibles,
        $prix,
        $description,
        $vehicule
    );
    
    if($result['success']) {
        $success = $result['message'];
        // Réinitialiser le formulaire
        $_POST = array();
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publier un trajet - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .publier-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .required::after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body>
    <?php include 'includes/views/navbar.php'; ?>
    
    <div class="container">
        <div class="publier-container">
            <h2 class="text-center mb-4">Publier un nouveau trajet</h2>
            
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="lieu_depart" class="form-label required">Lieu de départ</label>
                        <input type="text" class="form-control" id="lieu_depart" name="lieu_depart" 
                               value="<?php echo $_POST['lieu_depart'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="lieu_arrivee" class="form-label required">Lieu d'arrivée</label>
                        <input type="text" class="form-control" id="lieu_arrivee" name="lieu_arrivee" 
                               value="<?php echo $_POST['lieu_arrivee'] ?? ''; ?>" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="date_trajet" class="form-label required">Date du trajet</label>
                        <input type="date" class="form-control" id="date_trajet" name="date_trajet" 
                               value="<?php echo $_POST['date_trajet'] ?? ''; ?>" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="heure_depart" class="form-label required">Heure de départ</label>
                        <input type="time" class="form-control" id="heure_depart" name="heure_depart" 
                               value="<?php echo $_POST['heure_depart'] ?? ''; ?>" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="places_disponibles" class="form-label required">Places disponibles</label>
                        <select class="form-control" id="places_disponibles" name="places_disponibles" required>
                            <option value="">Sélectionner</option>
                            <?php for($i = 1; $i <= 8; $i++): ?>
                                <option value="<?php echo $i; ?>" 
                                    <?php echo (isset($_POST['places_disponibles']) && $_POST['places_disponibles'] == $i) ? 'selected' : ''; ?>>
                                    <?php echo $i; ?> place<?php echo $i > 1 ? 's' : ''; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="prix" class="form-label">Prix par personne (€)</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="prix" name="prix" 
                                   value="<?php echo $_POST['prix'] ?? ''; ?>" min="0" step="0.5">
                            <span class="input-group-text">€</span>
                        </div>
                        <small class="text-muted">Laissez vide pour un trajet gratuit</small>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="vehicule" class="form-label">Véhicule</label>
                    <input type="text" class="form-control" id="vehicule" name="vehicule" 
                           value="<?php echo $_POST['vehicule'] ?? ''; ?>" 
                           placeholder="Ex: Renault Clio, Gris, 5 places">
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description supplémentaire</label>
                    <textarea class="form-control" id="description" name="description" rows="3" 
                              placeholder="Informations complémentaires (arrêts possibles, bagages, animaux, etc.)"><?php echo $_POST['description'] ?? ''; ?></textarea>
                </div>
                
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" id="regles" name="regles" required>
                    <label class="form-check-label" for="regles">
                        Je certifie que mon véhicule est en bon état et assuré pour le covoiturage
                    </label>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">Publier le trajet</button>
                    <a href="index.php" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>

    <?php include 'includes/views/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Définir la date minimale comme aujourd'hui
        document.getElementById('date_trajet').min = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>