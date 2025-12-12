<?php
class Panier {
    private $conn;
    private $table_name = "panier";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Ajouter un élément au panier
    public function addToCart($user_id, $trajet_id, $nombre_places = 1) {
        // Vérifier si l'élément existe déjà
        $query_check = "SELECT id FROM " . $this->table_name . " 
                       WHERE user_id = :user_id AND trajet_id = :trajet_id";
        
        $stmt_check = $this->conn->prepare($query_check);
        $stmt_check->bindParam(":user_id", $user_id);
        $stmt_check->bindParam(":trajet_id", $trajet_id);
        $stmt_check->execute();
        
        if($stmt_check->rowCount() > 0) {
            // Mettre à jour la quantité
            $query = "UPDATE " . $this->table_name . " 
                     SET nombre_places = :nombre_places 
                     WHERE user_id = :user_id AND trajet_id = :trajet_id";
        } else {
            // Insérer un nouvel élément
            $query = "INSERT INTO " . $this->table_name . " 
                     (user_id, trajet_id, nombre_places) 
                     VALUES (:user_id, :trajet_id, :nombre_places)";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":trajet_id", $trajet_id);
        $stmt->bindParam(":nombre_places", $nombre_places);
        
        return $stmt->execute();
    }

    // Récupérer les éléments du panier
    public function getCartItems($user_id) {
        $query = "SELECT p.*, t.lieu_depart, t.lieu_arrivee, t.prix, t.places_disponibles 
                  FROM " . $this->table_name . " p
                  INNER JOIN trajets t ON p.trajet_id = t.id
                  WHERE p.user_id = :user_id 
                  AND t.status = 'active' 
                  AND t.date_trajet >= CURDATE()
                  ORDER BY p.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Supprimer un élément du panier
    public function removeFromCart($user_id, $trajet_id) {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE user_id = :user_id AND trajet_id = :trajet_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":trajet_id", $trajet_id);
        
        return $stmt->execute();
    }

    // Vider le panier
    public function clearCart($user_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        return $stmt->execute();
    }

    // Mettre à jour la quantité
    public function updateCartItem($user_id, $trajet_id, $quantite) {
        $query = "UPDATE " . $this->table_name . " 
                  SET nombre_places = :quantite 
                  WHERE user_id = :user_id AND trajet_id = :trajet_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":quantite", $quantite);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":trajet_id", $trajet_id);
        
        return $stmt->execute();
    }

    // Compter les éléments du panier
    public function countCartItems($user_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    // Calculer le total du panier
    public function getCartTotal($user_id) {
        $query = "SELECT SUM(p.nombre_places * t.prix) as total 
                  FROM " . $this->table_name . " p
                  INNER JOIN trajets t ON p.trajet_id = t.id
                  WHERE p.user_id = :user_id 
                  AND t.status = 'active' 
                  AND t.date_trajet >= CURDATE()";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
}
?>