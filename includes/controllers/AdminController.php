<?php
require_once 'includes/models/User.php';
require_once 'includes/models/Trajet.php';

class AdminController {
    private $db;
    private $userModel;
    private $trajetModel;

    public function __construct($db) {
        $this->db = $db;
        $this->userModel = new User($db);
        $this->trajetModel = new Trajet($db);
    }

    // Récupérer les statistiques
    public function getStats() {
        $stats = array();

        // Nombre d'utilisateurs
        $query = "SELECT COUNT(*) as count FROM users";
        $stmt = $this->db->query($query);
        $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Nombre de trajets
        $query = "SELECT COUNT(*) as count FROM trajets";
        $stmt = $this->db->query($query);
        $stats['total_trajets'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Trajets actifs
        $query = "SELECT COUNT(*) as count FROM trajets WHERE status = 'active'";
        $stmt = $this->db->query($query);
        $stats['active_trajets'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Trajets en attente
        $query = "SELECT COUNT(*) as count FROM trajets WHERE status = 'pending'";
        $stmt = $this->db->query($query);
        $stats['pending_trajets'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Réservations aujourd'hui
        $query = "SELECT COUNT(*) as count FROM reservations WHERE DATE(created_at) = CURDATE()";
        $stmt = $this->db->query($query);
        $stats['reservations_today'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        return $stats;
    }

    // Récupérer tous les utilisateurs
    public function getAllUsers($role = null) {
        $stmt = $this->userModel->getAllUsers($role);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Changer le statut d'un utilisateur
    public function toggleUserStatus($user_id) {
        $user = $this->userModel->getUserById($user_id);
        
        if(!$user) {
            return array('success' => false, 'message' => 'Utilisateur introuvable');
        }

        $new_status = $user['status'] == 1 ? 0 : 1;
        
        if($this->userModel->updateStatus($user_id, $new_status)) {
            return array('success' => true, 'message' => 'Statut mis à jour', 'new_status' => $new_status);
        } else {
            return array('success' => false, 'message' => 'Erreur lors de la mise à jour');
        }
    }

    // Supprimer un utilisateur
    public function deleteUser($user_id, $current_admin_id) {
        // Empêcher la suppression de soi-même
        if($user_id == $current_admin_id) {
            return array('success' => false, 'message' => 'Vous ne pouvez pas supprimer votre propre compte');
        }

        if($this->userModel->delete($user_id)) {
            return array('success' => true, 'message' => 'Utilisateur supprimé');
        } else {
            return array('success' => false, 'message' => 'Erreur lors de la suppression');
        }
    }

    // Récupérer tous les trajets
    public function getAllTrajets($status = null) {
        return $this->trajetModel->getAllTrajets($status);
    }

    // Valider un trajet
    public function validateTrajet($trajet_id) {
        if($this->trajetModel->updateStatus($trajet_id, 'active')) {
            return array('success' => true, 'message' => 'Trajet validé');
        } else {
            return array('success' => false, 'message' => 'Erreur lors de la validation');
        }
    }

    // Rejeter un trajet
    public function rejectTrajet($trajet_id) {
        if($this->trajetModel->updateStatus($trajet_id, 'cancelled')) {
            return array('success' => true, 'message' => 'Trajet rejeté');
        } else {
            return array('success' => false, 'message' => 'Erreur lors du rejet');
        }
    }

    // Supprimer un trajet (admin)
    public function deleteTrajet($trajet_id) {
        if($this->trajetModel->delete($trajet_id)) {
            return array('success' => true, 'message' => 'Trajet supprimé');
        } else {
            return array('success' => false, 'message' => 'Erreur lors de la suppression');
        }
    }

    // Promouvoir un utilisateur admin
    public function promoteToAdmin($user_id) {
        $query = "UPDATE users SET role = 'admin' WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $user_id);
        
        if($stmt->execute()) {
            return array('success' => true, 'message' => 'Utilisateur promu administrateur');
        } else {
            return array('success' => false, 'message' => 'Erreur lors de la promotion');
        }
    }
}
?>