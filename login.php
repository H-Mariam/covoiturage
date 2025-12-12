<?php
session_start();
require_once 'includes/config/Database.php';
require_once 'includes/config/Constants.php';
require_once 'includes/controllers/AuthController.php';

$database = new Database();
//$db = $database->getConnection();
$db = new PDO("mysql:host=localhost;dbname=covoituragee_db", "root", "");
// Ajoutez ceci APRÈS pour voir les erreurs :
if(!$db) {
    die("Erreur connexion DB");
}
$authController = new AuthController($db);

$error = '';
$success = '';

// Vérifier si l'utilisateur est déjà connecté
if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Traitement du formulaire de connexion
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $result = $authController->login($email, $password);
    
    if($result['success']) {
        // Réinitialiser la session pour éviter les restes d'un ancien utilisateur
        if(session_status() === PHP_SESSION_ACTIVE){
            session_unset();
            session_destroy();
        }
        session_start(); // nouvelle session propre
        $_SESSION['user_id'] = $result['user']['id'];
        $_SESSION['user_email'] = $result['user']['email'];
        $_SESSION['user_name'] = $result['user']['nom'];
        $_SESSION['role'] = $result['user']['role'];
        
        // Redirection selon le rôle
        if($result['user']['role'] == ROLE_ADMIN) {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo h2 {
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="logo">
                <h2><?php echo SITE_NAME; ?></h2>
                <p class="text-muted">Connectez-vous à votre compte</p>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">Adresse email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Se souvenir de moi</label>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Se connecter</button>
            </form>
            
            <div class="mt-3 text-center">
                <p>Pas encore de compte ? <a href="register.php">S'inscrire</a></p>
                <p><a href="index.php">Retour à l'accueil</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>