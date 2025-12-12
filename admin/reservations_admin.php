<?php
session_start();
require_once '../includes/utils/Session.php';
require_once '../includes/controllers/ReservationController.php';
require_once '../includes/models/Reservation.php';
require_once '../includes/config/Database.php';

$session = new Session();
if (!$session->checkLogin() || !$session->isAdmin()) {
    header("Location: ../login.php");
    exit();
}

$db = (new Database())->getConnection();
$reservationController = new ReservationController($db);

$reservations = $reservationController->getAllReservations();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réservations - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include 'includes/admin_navbar.php'; ?>
<?php include 'includes/admin_sidebar.php'; ?>

<div class="container mt-4">
    <h2><i class="bi bi-calendar-check"></i> Liste des réservations</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>ID Trajet</th>
                <th>Utilisateur</th>
                <th>Places Réservées</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reservations as $res): ?>
                <tr>
                    <td><?= $res['id'] ?></td>
                    <td><?= $res['trajet_id'] ?></td>
                    <td><?= $res['user_id'] ?></td>
                    <td><?= $res['nbr_places'] ?></td>
                    <td><?= $res['created_at'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
