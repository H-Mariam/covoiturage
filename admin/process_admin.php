<?php
session_start();
require_once '../includes/config/Database.php';
require_once '../includes/config/Constants.php';
require_once '../includes/utils/Session.php';

$session = new Session();
if(!$session->checkLogin() || !$session->isAdmin()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Non autorisé']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$action = $_GET['action'] ?? '';

switch($action) {
    case 'stats':
        // Récupérer les statistiques
        $stats = [];
        
        // Total utilisateurs
        $query = "SELECT COUNT(*) as count FROM users";
        $stmt = $db->query($query);
        $stats['totalUsers'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Total trajets
        $query = "SELECT COUNT(*) as count FROM trajets";
        $stmt = $db->query($query);
        $stats['totalTrajets'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Trajets actifs
        $query = "SELECT COUNT(*) as count FROM trajets WHERE status = 'active'";
        $stmt = $db->query($query);
        $stats['activeTrajets'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        header('Content-Type: application/json');
        echo json_encode($stats);
        break;
        
    case 'delete_user':
        if(isset($_POST['user_id'])) {
            $user_id = $_POST['user_id'];
            
            // Vérifier que ce n'est pas l'admin actuel
            if($user_id != $_SESSION['user_id']) {
                $query = "DELETE FROM users WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $user_id);
                
                if($stmt->execute()) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['error' => 'Erreur de suppression']);
                }
            } else {
                echo json_encode(['error' => 'Vous ne pouvez pas supprimer votre propre compte']);
            }
        }
        break;
        
    default:
        echo json_encode(['error' => 'Action non reconnue']);
}
?>