<?php
session_start();
require_once 'includes/config/Database.php';
require_once 'includes/config/Constants.php';
require_once 'includes/utils/Session.php';

$session = new Session();
if(!$session->checkLogin()) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Marquer toutes les notifications comme lues
if(isset($_GET['action']) && $_GET['action'] == 'mark_all_read') {
    $query = "UPDATE notifications SET is_read = 1 WHERE user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    
    header("Location: notifications.php");
    exit();
}

// Marquer une notification comme lue
if(isset($_GET['action']) && $_GET['action'] == 'mark_read' && isset($_GET['id'])) {
    $query = "UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $_GET['id']);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    
    header("Location: notifications.php");
    exit();
}

// Récupérer les notifications
$query = "SELECT * FROM notifications 
          WHERE user_id = :user_id 
          ORDER BY created_at DESC 
          LIMIT 50";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Compter les notifications non lues
$query_unread = "SELECT COUNT(*) as count FROM notifications 
                 WHERE user_id = :user_id AND is_read = 0";
$stmt_unread = $db->prepare($query_unread);
$stmt_unread->bindParam(":user_id", $user_id);
$stmt_unread->execute();
$unread_count = $stmt_unread->fetch(PDO::FETCH_ASSOC)['count'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .notification-item {
            border-left: 4px solid transparent;
            transition: all 0.3s;
        }
        .notification-item.unread {
            border-left-color: #0d6efd;
            background-color: #f8f9fa;
        }
        .notification-item:hover {
            background-color: #f0f8ff;
            transform: translateX(5px);
        }
        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        .icon-reservation {
            background-color: #d4edda;
            color: #155724;
        }
        .icon-confirmation {
            background-color: #cce5ff;
            color: #004085;
        }
        .icon-annulation {
            background-color: #f8d7da;
            color: #721c24;
        }
        .icon-system {
            background-color: #e2e3e5;
            color: #383d41;
        }
    </style>
</head>
<body>
    <?php include 'includes/views/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Notifications</h1>
            <div>
                <?php if($unread_count > 0): ?>
                    <a href="?action=mark_all_read" class="btn btn-outline-primary">
                        <i class="bi bi-check-all"></i> Tout marquer comme lu
                    </a>
                <?php endif; ?>
                <span class="badge bg-primary ms-2">
                    <?php echo $unread_count; ?> non lue(s)
                </span>
            </div>
        </div>
        
        <?php if(empty($notifications)): ?>
            <div class="alert alert-info text-center py-5">
                <i class="bi bi-bell" style="font-size: 3rem; opacity: 0.5;"></i>
                <h3 class="mt-3">Aucune notification</h3>
                <p class="text-muted">Vous n'avez pas encore de notification.</p>
            </div>
        <?php else: ?>
            <div class="list-group">
                <?php foreach($notifications as $notification): 
                    // Déterminer l'icône en fonction du type
                    $icon_class = 'icon-system';
                    $icon = 'bi-bell';
                    
                    switch($notification['type']) {
                        case 'reservation':
                            $icon_class = 'icon-reservation';
                            $icon = 'bi-calendar-plus';
                            break;
                        case 'confirmation':
                            $icon_class = 'icon-confirmation';
                            $icon = 'bi-check-circle';
                            break;
                        case 'annulation':
                            $icon_class = 'icon-annulation';
                            $icon = 'bi-x-circle';
                            break;
                        case 'message':
                            $icon_class = 'icon-system';
                            $icon = 'bi-chat';
                            break;
                    }
                ?>
                    <a href="?action=mark_read&id=<?php echo $notification['id']; ?>" 
                       class="list-group-item list-group-item-action notification-item <?php echo $notification['is_read'] == 0 ? 'unread' : ''; ?>">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="notification-icon <?php echo $icon_class; ?>">
                                    <i class="bi <?php echo $icon; ?>"></i>
                                </div>
                            </div>
                            <div class="col">
                                <div class="mb-1">
                                    <?php echo htmlspecialchars($notification['message']); ?>
                                </div>
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i> 
                                    <?php 
                                        $date = new DateTime($notification['created_at']);
                                        echo $date->format('d/m/Y H:i');
                                    ?>
                                </small>
                            </div>
                            <div class="col-auto">
                                <?php if($notification['is_read'] == 0): ?>
                                    <span class="badge bg-primary">Nouveau</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination (simplifiée) -->
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1">Précédent</a>
                    </li>
                    <li class="page-item active">
                        <a class="page-link" href="#">1</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">2</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">3</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">Suivant</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
        
        <!-- Statistiques -->
        <div class="row mt-5">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="card-title"><?php echo count($notifications); ?></h3>
                        <p class="card-text text-muted">Total notifications</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="card-title"><?php echo $unread_count; ?></h3>
                        <p class="card-text text-muted">Non lues</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="card-title"><?php echo count($notifications) - $unread_count; ?></h3>
                        <p class="card-text text-muted">Lues</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="card-title"><?php echo date('Y'); ?></h3>
                        <p class="card-text text-muted">Année en cours</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/views/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>