<?php
require_once 'includes/models/Reservation.php';
require_once 'includes/models/Trajet.php';
require_once 'includes/models/Panier.php';

class ReservationController {
    private $db;
    private $reservationModel;
    private $trajetModel;
    private $panierModel;

    public function __construct($db) {
        $this->db = $db;
        $this->reservationModel = new Reservation($db);
        $this->trajetModel = new Trajet($db);
        $this->panierModel = new Panier($db);
    }

    // Créer une réservation
    public function createReservation($trajet_id, $passager_id, $nombre_places, $moyen_paiement) {
        $response = array('success' => false, 'message' => '', 'reservation_id' => null);

        // Validation
        if(empty($trajet_id) || empty($passager_id) || empty($nombre_places)) {
            $response['message'] = 'Données de réservation incomplètes';
            return $response;
        }

        // Vérifier le trajet
        $trajet = $this->trajetModel->getTrajetById($trajet_id);
        if(!$trajet) {
            $response['message'] = 'Trajet introuvable';
            return $response;
        }

        // Vérifier les places disponibles
        if($trajet['places_disponibles'] < $nombre_places) {
            $response['message'] = 'Pas assez de places disponibles';
            return $response;
        }

        // Vérifier que l'utilisateur n'est pas le conducteur
        if($trajet['conducteur_id'] == $passager_id) {
            $response['message'] = 'Vous ne pouvez pas réserver votre propre trajet';
            return $response;
        }

        // Calculer le prix total
        $prix_total = $trajet['prix'] * $nombre_places;

        // Créer la réservation
        $reservation_id = $this->reservationModel->create(
            $trajet_id,
            $passager_id,
            $nombre_places,
            $prix_total
        );

        if($reservation_id) {
            // Mettre à jour les places disponibles
            $this->trajetModel->updatePlacesDisponibles(
                $trajet_id,
                $trajet['places_disponibles'] - $nombre_places
            );

            // Vider le panier pour ce trajet
            $this->panierModel->removeFromCart($passager_id, $trajet_id);

            $response['success'] = true;
            $response['message'] = 'Réservation confirmée !';
            $response['reservation_id'] = $reservation_id;

            // --------------------------
            // Génération automatique de notification pour l'admin
            $notif_message = "Nouvelle réservation pour le trajet #$trajet_id par l'utilisateur #$passager_id";
            $admin_user_id = 1; // ID de l'admin (adapter si besoin)
            $stmt = $this->db->prepare("
                INSERT INTO notifications (user_id, type, message, is_read, created_at)
                VALUES (:user_id, 'reservation', :message, 0, NOW())
            ");
            $stmt->bindParam(':user_id', $admin_user_id);
            $stmt->bindParam(':message', $notif_message);
            $stmt->execute();
            // --------------------------
        } else {
            $response['message'] = 'Erreur lors de la création de la réservation';
        }

        return $response;
    }

    // Annuler une réservation
    public function cancelReservation($reservation_id, $user_id) {
        $response = array('success' => false, 'message' => '');

        $reservation = $this->reservationModel->getReservationById($reservation_id);
        
        if(!$reservation) {
            $response['message'] = 'Réservation introuvable';
            return $response;
        }

        // Vérifier que l'utilisateur peut annuler
        if($reservation['passager_id'] != $user_id) {
            $response['message'] = 'Vous n\'avez pas le droit d\'annuler cette réservation';
            return $response;
        }

        // Annuler la réservation
        if($this->reservationModel->cancel($reservation_id)) {
            // Rembourser les places
            $trajet = $this->trajetModel->getTrajetById($reservation['trajet_id']);
            $this->trajetModel->updatePlacesDisponibles(
                $reservation['trajet_id'],
                $trajet['places_disponibles'] + $reservation['nombre_places']
            );

            $response['success'] = true;
            $response['message'] = 'Réservation annulée avec succès';

            // --------------------------
            // Génération automatique de notification pour l'admin
            $notif_message = "Réservation annulée pour le trajet #{$reservation['trajet_id']} par l'utilisateur #$user_id";
            $admin_user_id = 1;
            $stmt = $this->db->prepare("
                INSERT INTO notifications (user_id, type, message, is_read, created_at)
                VALUES (:user_id, 'annulation', :message, 0, NOW())
            ");
            $stmt->bindParam(':user_id', $admin_user_id);
            $stmt->bindParam(':message', $notif_message);
            $stmt->execute();
            // --------------------------

        } else {
            $response['message'] = 'Erreur lors de l\'annulation';
        }

        return $response;
    }

    // Récupérer les réservations d'un utilisateur
    public function getUserReservations($user_id) {
        return $this->reservationModel->getByPassagerId($user_id);
    }

    // Confirmer une réservation (pour le conducteur)
    public function confirmReservation($reservation_id, $conducteur_id) {
        $response = array('success' => false, 'message' => '');

        $reservation = $this->reservationModel->getReservationById($reservation_id);
        
        if(!$reservation) {
            $response['message'] = 'Réservation introuvable';
            return $response;
        }

        // Vérifier que l'utilisateur est le conducteur
        $trajet = $this->trajetModel->getTrajetById($reservation['trajet_id']);
        if($trajet['conducteur_id'] != $conducteur_id) {
            $response['message'] = 'Vous n\'êtes pas le conducteur de ce trajet';
            return $response;
        }

        if($this->reservationModel->confirm($reservation_id)) {
            $response['success'] = true;
            $response['message'] = 'Réservation confirmée';

            // --------------------------
            // Génération automatique de notification pour le passager
            $notif_message = "Votre réservation pour le trajet #{$reservation['trajet_id']} a été confirmée";
            $passager_id = $reservation['passager_id'];
            $stmt = $this->db->prepare("
                INSERT INTO notifications (user_id, type, message, is_read, created_at)
                VALUES (:user_id, 'confirmation', :message, 0, NOW())
            ");
            $stmt->bindParam(':user_id', $passager_id);
            $stmt->bindParam(':message', $notif_message);
            $stmt->execute();
            // --------------------------

        } else {
            $response['message'] = 'Erreur lors de la confirmation';
        }

        return $response;
    }
}
?>
