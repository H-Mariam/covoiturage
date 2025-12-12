// Script principal pour le site de covoiturage

document.addEventListener('DOMContentLoaded', function() {
    // Initialiser les tooltips Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialiser les popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Gestion du compteur de places dans le panier
    const quantityInputs = document.querySelectorAll('.quantity-input');
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const max = parseInt(this.getAttribute('max'));
            const min = parseInt(this.getAttribute('min'));
            let value = parseInt(this.value);
            
            if (value > max) this.value = max;
            if (value < min) this.value = min;
            if (isNaN(value)) this.value = min;
        });
    });

    // Auto-dismiss des alertes après 5 secondes
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // Validation des formulaires
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Calcul dynamique du prix total
    const calculateTotalPrice = () => {
        const priceElements = document.querySelectorAll('.item-price');
        let total = 0;
        
        priceElements.forEach(element => {
            const price = parseFloat(element.dataset.price) || 0;
            const quantity = parseInt(element.closest('.cart-item').querySelector('.quantity-input').value) || 1;
            total += price * quantity;
            
            // Mettre à jour le sous-total
            const subtotalElement = element.closest('.cart-item').querySelector('.item-subtotal');
            if(subtotalElement) {
                subtotalElement.textContent = (price * quantity).toFixed(2) + '€';
            }
        });
        
        const totalElement = document.querySelector('.cart-total-price');
        if(totalElement) {
            totalElement.textContent = total.toFixed(2) + '€';
        }
    };

    // Écouter les changements de quantité
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', calculateTotalPrice);
    });

    // Initialiser le calcul
    calculateTotalPrice();

    // Gestion de la recherche en temps réel
    const searchInput = document.querySelector('.search-input');
    if(searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const items = document.querySelectorAll('.searchable-item');
            
            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }

    // Affichage dynamique de l'heure de départ
    const dateInput = document.getElementById('date_trajet');
    if(dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.min = today;
        
        dateInput.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const now = new Date();
            
            // Si la date est aujourd'hui, limiter l'heure
            if(selectedDate.toDateString() === now.toDateString()) {
                const hourInput = document.getElementById('heure_depart');
                if(hourInput) {
                    const currentHour = now.getHours().toString().padStart(2, '0');
                    const currentMinute = now.getMinutes().toString().padStart(2, '0');
                    hourInput.min = `${currentHour}:${currentMinute}`;
                }
            }
        });
    }

    // Animation des cartes
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.3s ease';
        });
    });

    // Gestion des notifications
    const notificationButtons = document.querySelectorAll('.notification-btn');
    notificationButtons.forEach(button => {
        button.addEventListener('click', function() {
            const notificationId = this.dataset.notificationId;
            if(notificationId) {
                fetch(`notifications.php?action=mark_read&id=${notificationId}`)
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            this.closest('.notification-item').classList.remove('unread');
                        }
                    });
            }
        });
    });

    // Système de favoris
    const favoriteButtons = document.querySelectorAll('.favorite-btn');
    favoriteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const trajetId = this.dataset.trajetId;
            const isFavorite = this.classList.contains('active');
            
            fetch('favorites.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `trajet_id=${trajetId}&action=${isFavorite ? 'remove' : 'add'}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    this.classList.toggle('active');
                    const icon = this.querySelector('i');
                    if(isFavorite) {
                        icon.classList.remove('bi-heart-fill');
                        icon.classList.add('bi-heart');
                    } else {
                        icon.classList.remove('bi-heart');
                        icon.classList.add('bi-heart-fill');
                    }
                }
            });
        });
    });

    // Confirmation avant suppression
    const deleteButtons = document.querySelectorAll('.confirm-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if(!confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')) {
                e.preventDefault();
            }
        });
    });

    // Loader pour les soumissions de formulaire
    const submitButtons = document.querySelectorAll('form');
    submitButtons.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if(submitBtn) {
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Traitement...';
                submitBtn.disabled = true;
            }
        });
    });
});

// Fonction pour afficher un message
function showMessage(type, message) {
    const container = document.createElement('div');
    container.className = `alert alert-${type} alert-dismissible fade show`;
    container.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.prepend(container);
    
    setTimeout(() => {
        container.remove();
    }, 5000);
}

// Fonction pour formater une date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Fonction pour calculer l'âge
function calculateAge(birthdate) {
    const birthDate = new Date(birthdate);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    
    return age;
}

// Export des fonctions utiles
window.Covoiturage = {
    showMessage,
    formatDate,
    calculateAge
};