<?php
class Session {
    public function __construct() {
        if(session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Vérifier si l'utilisateur est connecté
    public function checkLogin() {
        return isset($_SESSION['user_id']);
    }

    // Vérifier si l'utilisateur est administrateur
    public function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
    }

    // Récupérer l'ID utilisateur
    public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    // Récupérer le rôle utilisateur
    public function getUserRole() {
        return $_SESSION['role'] ?? null;
    }

    // Définir un message flash
    public function setFlash($key, $message) {
        $_SESSION['flash'][$key] = $message;
    }

    // Récupérer un message flash
    public function getFlash($key) {
        if(isset($_SESSION['flash'][$key])) {
            $message = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $message;
        }
        return null;
    }

    // Vérifier si un message flash existe
    public function hasFlash($key) {
        return isset($_SESSION['flash'][$key]);
    }

    // Détruire la session
    public function destroy() {
        session_destroy();
    }
}
?>