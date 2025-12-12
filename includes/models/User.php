<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $nom;
    public $email;
    public $password;
    public $telephone;
    public $role;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Créer un nouvel utilisateur
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET nom = :nom, email = :email, password = :password, 
                      telephone = :telephone, role = :role, status = :status";

        $stmt = $this->conn->prepare($query);

        // Nettoyer les données
        $this->nom = htmlspecialchars(strip_tags($this->nom));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->telephone = htmlspecialchars(strip_tags($this->telephone));

        // Hasher le mot de passe
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);

        // Liaison des valeurs
        $stmt->bindParam(":nom", $this->nom);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":telephone", $this->telephone);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Vérifier si l'email existe
    public function emailExists($email) {
        $query = "SELECT id, nom, email, password, role, status 
                  FROM " . $this->table_name . " 
                  WHERE email = ? 
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->nom = $row['nom'];
            $this->email = $row['email'];
            $this->password = $row['password'];
            $this->role = $row['role'];
            $this->status = $row['status'];
            return true;
        }
        return false;
    }

    // Récupérer tous les utilisateurs (pour admin)
    public function getAllUsers($role = null) {
        $query = "SELECT * FROM " . $this->table_name;
        
        if($role) {
            $query .= " WHERE role = :role";
        }
        
        $query .= " ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        
        if($role) {
            $stmt->bindParam(":role", $role);
        }
        
        $stmt->execute();
        return $stmt;
    }

    // Mettre à jour le statut d'un utilisateur
    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = :status 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    // Supprimer un utilisateur
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    // Récupérer un utilisateur par ID
    public function getUserById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }

    // Mettre à jour le profil utilisateur
    public function updateProfile($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET nom = :nom, telephone = :telephone 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nom", $data['nom']);
        $stmt->bindParam(":telephone", $data['telephone']);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }
    
}
?>