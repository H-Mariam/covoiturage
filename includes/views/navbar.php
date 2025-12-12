<?php
// Démarrer la session si pas déjà fait
if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? 'guest';
$userName = $_SESSION['user_name'] ?? '';
$panierCount = 0; // À remplacer par le vrai compte

if($isLoggedIn) {
    // Compter les éléments du panier
    require_once 'includes/config/Database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT COUNT(*) as count FROM panier WHERE user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $panierCount = $result['count'] ?? 0;
}
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="bi bi-car-front-fill text-primary"></i>
            <span class="fw-bold">Covoiturini</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="bi bi-house"></i> Accueil
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="trajets.php">
                        <i class="bi bi-search"></i> Chercher un trajet
                    </a>
                </li>
                <?php if($isLoggedIn): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="publier.php">
                            <i class="bi bi-plus-circle"></i> Publier un trajet
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="mes_trajets.php">
                            <i class="bi bi-journals"></i> Mes trajets
                        </a>
                    </li>
                <?php endif; ?>
                <?php if($isLoggedIn && $userRole == 'admin'): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-shield-check"></i> Admin
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="admin/dashboard.php">Tableau de bord</a></li>
                            <li><a class="dropdown-item" href="admin/users.php">Gestion utilisateurs</a></li>
                            <li><a class="dropdown-item" href="admin/trajets_admin.php">Gestion trajets</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="admin/statistiques.php">Statistiques</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
            
            <ul class="navbar-nav">
                <?php if($isLoggedIn): ?>
                    <!-- Notifications -->
                    <li class="nav-item dropdown">
                        <a class="nav-link position-relative" href="notifications.php" id="notificationsDropdown">
                            <i class="bi bi-bell"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                3
                            </span>
                        </a>
                    </li>
                    
                    <!-- Panier -->
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="panier.php">
                            <i class="bi bi-cart"></i>
                            <?php if($panierCount > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary" style="font-size: 0.6rem;">
                                    <?php echo $panierCount; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    
                    <!-- Profil utilisateur -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($userName); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="mes_trajets.php">
                                <i class="bi bi-person"></i> Mon profil
                            </a></li>
                            <li><a class="dropdown-item" href="notifications.php">
                                <i class="bi bi-bell"></i> Notifications
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i> Déconnexion
                            </a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="bi bi-box-arrow-in-right"></i> Connexion
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary ms-2" href="register.php">
                            <i class="bi bi-person-plus"></i> S'inscrire
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Messages flash -->
<?php if(isset($_SESSION['flash'])): ?>
    <div class="container mt-3">
        <?php foreach($_SESSION['flash'] as $key => $message): ?>
            <div class="alert alert-<?php echo $key; ?> alert-dismissible fade show">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
        <?php unset($_SESSION['flash']); ?>
    </div>
<?php endif; ?>