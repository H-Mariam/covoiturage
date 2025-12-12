<footer class="bg-dark text-white mt-5">
    <div class="container py-5">
        <div class="row">
            <div class="col-md-4">
                <h5><i class="bi bi-car-front-fill"></i> Covoiturage</h5>
                <p class="text-muted">
                    La plateforme de covoiturage simple, rapide et sécurisée.
                    Partagez vos trajets et économisez ensemble.
                </p>
            </div>
            
            <div class="col-md-2">
                <h5>Navigation</h5>
                <ul class="list-unstyled">
                    <li><a href="index.php" class="text-decoration-none text-muted">Accueil</a></li>
                    <li><a href="trajets.php" class="text-decoration-none text-muted">Chercher un trajet</a></li>
                    <li><a href="publier.php" class="text-decoration-none text-muted">Publier un trajet</a></li>
                    <li><a href="contact.php" class="text-decoration-none text-muted">Contact</a></li>
                </ul>
            </div>
            
            <div class="col-md-3">
                <h5>Légal</h5>
                <ul class="list-unstyled">
                    <li><a href="conditions.php" class="text-decoration-none text-muted">Conditions d'utilisation</a></li>
                    <li><a href="confidentialite.php" class="text-decoration-none text-muted">Politique de confidentialité</a></li>
                    <li><a href="cookies.php" class="text-decoration-none text-muted">Cookies</a></li>
                    <li><a href="mentions.php" class="text-decoration-none text-muted">Mentions légales</a></li>
                </ul>
            </div>
            
            <div class="col-md-3">
                <h5>Contact</h5>
                <ul class="list-unstyled">
                    <li><i class="bi bi-envelope"></i> contact@covoiturage.fr</li>
                    <li><i class="bi bi-telephone"></i> 01 23 45 67 89</li>
                    <li><i class="bi bi-geo-alt"></i> Paris, France</li>
                </ul>
                <div class="mt-3">
                    <a href="#" class="text-white me-3"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-white me-3"><i class="bi bi-twitter"></i></a>
                    <a href="#" class="text-white me-3"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="text-white"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>
        </div>
        
        <hr class="bg-secondary">
        
        <div class="row">
            <div class="col-md-6">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> Covoiturage Marketplace. Tous droits réservés.</p>
            </div>
            <div class="col-md-6 text-end">
                <p class="mb-0">Développé avec <i class="bi bi-heart-fill text-danger"></i> pour la planète</p>
            </div>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo SITE_URL; ?>assets/js/script.js"></script>
<?php if(isset($custom_js)): ?>
    <script><?php echo $custom_js; ?></script>
<?php endif; ?>
</body>
</html>