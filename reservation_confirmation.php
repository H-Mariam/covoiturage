<?php
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$reservation_id = $_GET['id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation Réservation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="text-center">
            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
            <h1 class="mt-3">Réservation confirmée !</h1>
            <p class="lead">Votre réservation a été enregistrée avec succès</p>
            
            <div class="card mt-4">
                <div class="card-body">
                    <h3>N° de réservation: <strong>#<?php echo $reservation_id; ?></strong></h3>
                    <p class="text-muted">Gardez ce numéro pour référence</p>
                    
                    <div class="alert alert-success mt-3">
                        <h5>Prochaines étapes :</h5>
                        <ol class="text-start">
                            <li>Contactez le conducteur pour les détails</li>
                            <li>Soyez à l'heure au point de rendez-vous</li>
                            <li>Présentez votre numéro de réservation</li>
                        </ol>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <a href="mes_trajets.php" class="btn btn-primary">Voir mes réservations</a>
                <a href="index.php" class="btn btn-success">Retour à l'accueil</a>
            </div>
        </div>
    </div>
</body>
</html>