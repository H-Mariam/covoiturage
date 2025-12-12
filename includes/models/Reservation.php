<?php
class Reservation {
    private $conn;
    private $table_name = "reservations";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Créer une réservation
    public function create($trajet_id, $passager_id, $nombre_places, $prix_total) {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET trajet_id = :trajet_id, 
                      passager_id = :passager_id,
                      nombre_places = :nombre_places,
                      prix_total = :prix_total,
                      status = 'confirmed'";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":trajet_id", $trajet_id);
        $stmt->bindParam(":passager_id", $passager_id);
        $stmt->bindParam(":nombre_places", $nombre_places);
        $stmt->bindParam(":prix_total", $prix_total);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Récupérer une réservation par ID
    public function getReservationById($id) {
        $query = "SELECT r.*, t.lieu_depart, t.lieu_arrivee, t.date_trajet, t.heure_depart,
                         u.nom as passager_nom, u.email as passager_email
                  FROM " . $this->table_name . " r
                  INNER JOIN trajets t ON r.trajet_id = t.id
                  INNER JOIN users u ON r.passager_id = u.id
                  WHERE r.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }

    // Récupérer les réservations d'un passager
    public function getByPassagerId($passager_id) {
        $query = "SELECT r.*, t.lieu_depart, t.lieu_arrivee, t.date_trajet, t.heure_depart,t.conducteur_id AS conducteur_id,
                         u.nom as conducteur_nom, u.telephone as conducteur_telephone
                  FROM " . $this->table_name . " r
                  INNER JOIN trajets t ON r.trajet_id = t.id
                  INNER JOIN users u ON t.conducteur_id = u.id
                  WHERE r.passager_id = :passager_id
                  ORDER BY r.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":passager_id", $passager_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer les réservations d'un trajet
    public function getByTrajetId($trajet_id) {
        $query = "SELECT r.*, u.nom as passager_nom, u.email as passager_email
                  FROM " . $this->table_name . " r
                  INNER JOIN users u ON r.passager_id = u.id
                  WHERE r.trajet_id = :trajet_id
                  ORDER BY r.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":trajet_id", $trajet_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Annuler une réservation
    public function cancel($id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = 'cancelled' 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    // Confirmer une réservation
    public function confirm($id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = 'confirmed' 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    // Compter les réservations actives
    public function countActiveReservations($user_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE passager_id = :user_id 
                  AND status = 'confirmed'
                  AND EXISTS (
                      SELECT 1 FROM trajets t 
                      WHERE t.id = trajet_id 
                      AND t.date_trajet >= CURDATE()
                  )";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }
}
?>