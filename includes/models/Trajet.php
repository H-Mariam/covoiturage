<?php
class Trajet {
    private $conn;
    private $table_name = "trajets";

    public $id;
    public $conducteur_id;
    public $lieu_depart;
    public $lieu_arrivee;
    public $date_trajet;
    public $heure_depart;
    public $places_disponibles;
    public $prix;
    public $description;
    public $vehicule;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Créer un nouveau trajet
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET conducteur_id = :conducteur_id, 
                      lieu_depart = :lieu_depart, 
                      lieu_arrivee = :lieu_arrivee,
                      date_trajet = :date_trajet, 
                      heure_depart = :heure_depart,
                      places_disponibles = :places_disponibles, 
                      prix = :prix,
                      description = :description, 
                      vehicule = :vehicule,
                      status = :status";

        $stmt = $this->conn->prepare($query);

        // Nettoyer les données
        $this->lieu_depart = htmlspecialchars(strip_tags($this->lieu_depart));
        $this->lieu_arrivee = htmlspecialchars(strip_tags($this->lieu_arrivee));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->vehicule = htmlspecialchars(strip_tags($this->vehicule));

        // Liaison des valeurs
        $stmt->bindParam(":conducteur_id", $this->conducteur_id);
        $stmt->bindParam(":lieu_depart", $this->lieu_depart);
        $stmt->bindParam(":lieu_arrivee", $this->lieu_arrivee);
        $stmt->bindParam(":date_trajet", $this->date_trajet);
        $stmt->bindParam(":heure_depart", $this->heure_depart);
        $stmt->bindParam(":places_disponibles", $this->places_disponibles);
        $stmt->bindParam(":prix", $this->prix);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":vehicule", $this->vehicule);
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Récupérer tous les trajets avec filtres
    public function getTrajetsWithFilters($lieu_depart = '', $lieu_arrivee = '', $date = '', $min_places = 1) {
        $query = "SELECT t.*, u.nom as conducteur_nom, u.email as conducteur_email 
                  FROM " . $this->table_name . " t
                  INNER JOIN users u ON t.conducteur_id = u.id
                  WHERE t.status = 'active' 
                  AND t.date_trajet >= CURDATE() 
                  AND t.places_disponibles >= :min_places";

        $params = array(':min_places' => $min_places);

        if(!empty($lieu_depart)) {
            $query .= " AND t.lieu_depart LIKE :lieu_depart";
            $params[':lieu_depart'] = "%" . $lieu_depart . "%";
        }

        if(!empty($lieu_arrivee)) {
            $query .= " AND t.lieu_arrivee LIKE :lieu_arrivee";
            $params[':lieu_arrivee'] = "%" . $lieu_arrivee . "%";
        }

        if(!empty($date)) {
            $query .= " AND t.date_trajet = :date";
            $params[':date'] = $date;
        }

        $query .= " ORDER BY t.date_trajet ASC, t.heure_depart ASC";

        $stmt = $this->conn->prepare($query);
        
        foreach($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer les trajets les plus récents
    public function getLatestTrajets($limit = 6) {
        $query = "SELECT t.*, u.nom as conducteur_nom 
                  FROM " . $this->table_name . " t
                  INNER JOIN users u ON t.conducteur_id = u.id
                  WHERE t.status = 'active' 
                  AND t.date_trajet >= CURDATE()
                  ORDER BY t.created_at DESC 
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer un trajet par ID
    public function getTrajetById($id) {
        $query = "SELECT t.*, u.nom as conducteur_nom, u.email as conducteur_email, u.telephone 
                  FROM " . $this->table_name . " t
                  INNER JOIN users u ON t.conducteur_id = u.id
                  WHERE t.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }

    // Récupérer les trajets d'un conducteur
    public function getTrajetsByConducteur($conducteur_id) {
        $query = "SELECT t.*, 
                  (SELECT COUNT(*) FROM reservations r WHERE r.trajet_id = t.id) as reservations_count
                  FROM " . $this->table_name . " t
                  WHERE t.conducteur_id = :conducteur_id
                  ORDER BY t.date_trajet DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":conducteur_id", $conducteur_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Mettre à jour les places disponibles
    public function updatePlacesDisponibles($id, $places) {
        $query = "UPDATE " . $this->table_name . " 
                  SET places_disponibles = :places 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":places", $places);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    // Supprimer un trajet
    public function delete($id) {
        // Vérifier s'il y a des réservations
        $query_check = "SELECT COUNT(*) as count FROM reservations WHERE trajet_id = :id";
        $stmt_check = $this->conn->prepare($query_check);
        $stmt_check->bindParam(":id", $id);
        $stmt_check->execute();
        $result = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if($result['count'] > 0) {
            // Annuler les réservations
            $query_update = "UPDATE reservations SET status = 'annulee' WHERE trajet_id = :id";
            $stmt_update = $this->conn->prepare($query_update);
            $stmt_update->bindParam(":id", $id);
            $stmt_update->execute();
        }

        // Supprimer le trajet
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    // Récupérer tous les trajets (pour admin)
    public function getAllTrajets($status = null) {
        $query = "SELECT t.*, u.nom as conducteur_nom, u.email as conducteur_email 
                  FROM " . $this->table_name . " t
                  INNER JOIN users u ON t.conducteur_id = u.id";

        if($status) {
            $query .= " WHERE t.status = :status";
        }

        $query .= " ORDER BY t.created_at DESC";

        $stmt = $this->conn->prepare($query);
        
        if($status) {
            $stmt->bindParam(":status", $status);
        }

        $stmt->execute();
        return $stmt;
    }

    // Mettre à jour le statut d'un trajet
    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = :status 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }
}
?>