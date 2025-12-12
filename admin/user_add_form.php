<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/config/Database.php';
require_once '../includes/models/User.php';

$database = new Database();
$db = $database->getConnection();
$userModel = new User($db);
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $password = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $query = "INSERT INTO users (nom, email, telephone, password, role) 
              VALUES (:nom, :email, :telephone, :password, :role)";
    $stmt = $db->prepare($query);

    $stmt->bindParam(":nom", $nom);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":telephone", $telephone);
    $stmt->bindParam(":password", $password);
    $stmt->bindParam(":role", $role);

    if ($stmt->execute()) {
        $msg = "<div class='alert alert-success'>Utilisateur ajouté avec succès.</div>";
    } else {
        $msg = "<div class='alert alert-danger'>Erreur lors de l'ajout.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Ajouter utilisateur</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        background-color: #f8f9fa;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    h2 {
        color: #0d6efd;
        margin-bottom: 30px;
        text-align: center;
    }

    form {
        max-width: 500px;
        margin: 0 auto;
        padding: 30px;
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .form-control, .form-select {
        border-radius: 0.5rem;
        padding: 0.5rem 0.75rem;
        font-size: 1rem;
    }

    .btn-primary {
        background-color: #0d6efd;
        border-color: #0d6efd;
        border-radius: 0.5rem;
        padding: 0.5rem 1.2rem;
        font-weight: 500;
        transition: background-color 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #0b5ed7;
    }

    .btn-secondary {
        border-radius: 0.5rem;
        padding: 0.5rem 1.2rem;
        transition: background-color 0.3s ease;
    }

    .alert {
        max-width: 500px;
        margin: 20px auto;
        border-radius: 0.5rem;
    }

    label {
        font-weight: 500;
    }
</style>
</head>
<body>

<h2><i class="bi bi-person-plus"></i> Ajouter un utilisateur</h2>

<?php echo $msg; ?>

<form method="POST">
    <div class="mb-3">
        <label>Nom complet</label>
        <input type="text" name="nom" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Téléphone</label>
        <input type="text" name="telephone" class="form-control">
    </div>

    <div class="mb-3">
        <label>Mot de passe</label>
        <input type="password" name="mot_de_passe" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Rôle</label>
        <select class="form-select" name="role">
            <option value="user">Utilisateur</option>
            <option value="admin">Administrateur</option>
        </select>
    </div>

    <div class="d-flex justify-content-between">
        <button class="btn btn-primary" type="submit">Ajouter</button>
        <a href="users.php" class="btn btn-secondary">Retour</a>
    </div>
</form>

</body>
</html>

<?php