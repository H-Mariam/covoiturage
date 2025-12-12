<?php
require_once 'includes/models/User.php';
require_once 'includes/utils/Validator.php';

class AuthController {
    private $db;
    private $userModel;
    private $validator;

    public function __construct($db) {
        $this->db = $db;
        $this->userModel = new User($db);
        $this->validator = new Validator();
    }

    // Inscription
    public function register($nom, $email, $password, $confirm_password, $telephone = '') {
        $response = array('success' => false, 'message' => '');

        // Validation
        if(empty($nom) || empty($email) || empty($password) || empty($confirm_password)) {
            $response['message'] = 'Tous les champs obligatoires doivent être remplis';
            return $response;
        }

        if(!$this->validator->validateEmail($email)) {
            $response['message'] = 'Format d\'email invalide';
            return $response;
        }

        if(strlen($password) < 8) {
            $response['message'] = 'Le mot de passe doit contenir au moins 8 caractères';
            return $response;
        }

        if($password !== $confirm_password) {
            $response['message'] = 'Les mots de passe ne correspondent pas';
            return $response;
        }

        // Vérifier si l'email existe déjà
        if($this->userModel->emailExists($email)) {
            $response['message'] = 'Cet email est déjà utilisé';
            return $response;
        }

        // Créer l'utilisateur
        $this->userModel->nom = $nom;
        $this->userModel->email = $email;
        $this->userModel->password = $password;
        $this->userModel->telephone = $telephone;
        $this->userModel->role = 'user'; // Rôle par défaut
        $this->userModel->status = 1; // Actif

        if($this->userModel->create()) {
            $response['success'] = true;
            $response['message'] = 'Inscription réussie ! Vous pouvez vous connecter.';
        } else {
            $response['message'] = 'Erreur lors de l\'inscription';
        }

        return $response;
    }

    // Connexion
    public function login($email, $password) {
        $response = array('success' => false, 'message' => '', 'user' => null);

        // Validation
        if(empty($email) || empty($password)) {
            $response['message'] = 'Email et mot de passe requis';
            return $response;
        }

        // Vérifier si l'email existe
        if($this->userModel->emailExists($email)) {
            // Vérifier le statut
            if($this->userModel->status == 0) {
                $response['message'] = 'Votre compte est désactivé. Contactez l\'administrateur.';
                return $response;
            }

            // Vérifier le mot de passe
            if(password_verify($password, $this->userModel->password)) {
                $response['success'] = true;
                $response['user'] = array(
                    'id' => $this->userModel->id,
                    'nom' => $this->userModel->nom,
                    'email' => $this->userModel->email,
                    'role' => $this->userModel->role,
                    'status' => $this->userModel->status
                );
            } else {
                $response['message'] = 'Mot de passe incorrect';
            }
        } else {
            $response['message'] = 'Email non trouvé';
        }

        return $response;
    }

    // Récupérer les informations utilisateur
    public function getUserInfo($id) {
        return $this->userModel->getUserById($id);
    }

    // Mettre à jour le profil
    public function updateProfile($id, $data) {
        $response = array('success' => false, 'message' => '');

        // Validation
        if(empty($data['nom'])) {
            $response['message'] = 'Le nom est requis';
            return $response;
        }

        if($this->userModel->updateProfile($id, $data)) {
            $response['success'] = true;
            $response['message'] = 'Profil mis à jour avec succès';
        } else {
            $response['message'] = 'Erreur lors de la mise à jour';
        }

        return $response;
    }
}
?>