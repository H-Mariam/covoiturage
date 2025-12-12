// Validation côté client pour les formulaires

class FormValidator {
    constructor(formId) {
        this.form = document.getElementById(formId);
        this.errors = {};
        this.init();
    }

    init() {
        if (!this.form) return;

        // Validation à la soumission
        this.form.addEventListener('submit', (e) => this.validateForm(e));

        // Validation en temps réel
        const inputs = this.form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', () => this.validateField(input));
            input.addEventListener('input', () => this.clearFieldError(input));
        });
    }

    validateForm(e) {
        e.preventDefault();
        this.errors = {};
        
        const inputs = this.form.querySelectorAll('[data-validation]');
        inputs.forEach(input => this.validateField(input));
        
        if (Object.keys(this.errors).length === 0) {
            this.form.submit();
        } else {
            this.displayErrors();
        }
    }

    validateField(input) {
        const value = input.value.trim();
        const validationRules = input.dataset.validation?.split('|') || [];
        const fieldName = input.name || input.id;

        this.clearFieldError(input);

        for (const rule of validationRules) {
            const [ruleName, ruleValue] = rule.split(':');
            
            switch (ruleName) {
                case 'required':
                    if (!value) {
                        this.addError(fieldName, 'Ce champ est obligatoire');
                        return;
                    }
                    break;

                case 'email':
                    if (value && !this.isValidEmail(value)) {
                        this.addError(fieldName, 'Email invalide');
                        return;
                    }
                    break;

                case 'min':
                    if (value && value.length < parseInt(ruleValue)) {
                        this.addError(fieldName, `Minimum ${ruleValue} caractères`);
                        return;
                    }
                    break;

                case 'max':
                    if (value && value.length > parseInt(ruleValue)) {
                        this.addError(fieldName, `Maximum ${ruleValue} caractères`);
                        return;
                    }
                    break;

                case 'numeric':
                    if (value && isNaN(value)) {
                        this.addError(fieldName, 'Doit être un nombre');
                        return;
                    }
                    break;

                case 'match':
                    const matchField = document.getElementById(ruleValue);
                    if (matchField && value !== matchField.value) {
                        this.addError(fieldName, 'Les valeurs ne correspondent pas');
                        return;
                    }
                    break;

                case 'phone':
                    if (value && !this.isValidPhone(value)) {
                        this.addError(fieldName, 'Numéro de téléphone invalide');
                        return;
                    }
                    break;

                case 'date':
                    if (value && !this.isValidDate(value)) {
                        this.addError(fieldName, 'Date invalide');
                        return;
                    }
                    break;

                case 'price':
                    if (value && (isNaN(value) || parseFloat(value) < 0)) {
                        this.addError(fieldName, 'Prix invalide');
                        return;
                    }
                    break;
            }
        }
    }

    addError(fieldName, message) {
        this.errors[fieldName] = message;
        
        const input = this.form.querySelector(`[name="${fieldName}"]`) || 
                     this.form.querySelector(`#${fieldName}`);
        if (input) {
            input.classList.add('is-invalid');
            
            let errorElement = input.nextElementSibling;
            if (!errorElement || !errorElement.classList.contains('invalid-feedback')) {
                errorElement = document.createElement('div');
                errorElement.className = 'invalid-feedback';
                input.parentNode.insertBefore(errorElement, input.nextSibling);
            }
            errorElement.textContent = message;
        }
    }

    clearFieldError(input) {
        const fieldName = input.name || input.id;
        delete this.errors[fieldName];
        
        input.classList.remove('is-invalid');
        
        const errorElement = input.nextElementSibling;
        if (errorElement && errorElement.classList.contains('invalid-feedback')) {
            errorElement.textContent = '';
        }
    }

    displayErrors() {
        // Scroll vers la première erreur
        const firstErrorField = Object.keys(this.errors)[0];
        if (firstErrorField) {
            const input = this.form.querySelector(`[name="${firstErrorField}"]`) || 
                         this.form.querySelector(`#${firstErrorField}`);
            if (input) {
                input.scrollIntoView({ behavior: 'smooth', block: 'center' });
                input.focus();
            }
        }
    }

    // Méthodes de validation
    isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    isValidPhone(phone) {
        const re = /^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/;
        return re.test(phone);
    }

    isValidDate(date) {
        return !isNaN(Date.parse(date));
    }

    isValidPassword(password) {
        return password.length >= 8;
    }
}

// Initialisation des validateurs
document.addEventListener('DOMContentLoaded', function() {
    // Validation du formulaire d'inscription
    if (document.getElementById('registerForm')) {
        new FormValidator('registerForm');
    }

    // Validation du formulaire de connexion
    if (document.getElementById('loginForm')) {
        new FormValidator('loginForm');
    }

    // Validation du formulaire de publication de trajet
    if (document.getElementById('trajetForm')) {
        new FormValidator('trajetForm');
    }

    // Validation du formulaire de réservation
    if (document.getElementById('reservationForm')) {
        new FormValidator('reservationForm');
    }

    // Validation personnalisée pour les mots de passe
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        input.addEventListener('input', function() {
            const password = this.value;
            const requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /\d/.test(password),
                special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
            };

            // Afficher les critères de validation
            const requirementList = this.nextElementSibling;
            if (requirementList && requirementList.classList.contains('password-requirements')) {
                const items = requirementList.querySelectorAll('li');
                items.forEach(item => {
                    const type = item.dataset.requirement;
                    if (requirements[type]) {
                        item.classList.add('valid');
                        item.classList.remove('invalid');
                    } else {
                        item.classList.add('invalid');
                        item.classList.remove('valid');
                    }
                });
            }
        });
    });

    // Validation en temps réel pour les prix
    const priceInputs = document.querySelectorAll('input[data-validation*="price"]');
    priceInputs.forEach(input => {
        input.addEventListener('input', function() {
            let value = this.value.replace(',', '.');
            if (value && (isNaN(value) || parseFloat(value) < 0)) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    });

    // Validation des dates futures
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        const today = new Date().toISOString().split('T')[0];
        input.min = today;
        
        input.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const today = new Date();
            
            if (selectedDate < today) {
                this.classList.add('is-invalid');
                this.setCustomValidity('La date doit être future');
            } else {
                this.classList.remove('is-invalid');
                this.setCustomValidity('');
            }
        });
    });

    // Validation des nombres de places
    const placesInputs = document.querySelectorAll('input[data-validation*="numeric"]');
    placesInputs.forEach(input => {
        input.addEventListener('input', function() {
            const value = parseInt(this.value);
            const max = parseInt(this.max) || 8;
            const min = parseInt(this.min) || 1;
            
            if (value < min || value > max) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    });
});

// Fonctions utilitaires de validation
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePhone(phone) {
    const re = /^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/;
    return re.test(phone);
}

function validatePassword(password) {
    return password.length >= 8;
}

function validateRequired(value) {
    return value.trim() !== '';
}

function validateMinLength(value, min) {
    return value.length >= min;
}

function validateMaxLength(value, max) {
    return value.length <= max;
}

function validateNumeric(value) {
    return !isNaN(value);
}

function validatePrice(value) {
    const num = parseFloat(value);
    return !isNaN(num) && num >= 0;
}

function validateDate(date) {
    return !isNaN(Date.parse(date));
}

// Export des fonctions de validation
window.Validation = {
    validateEmail,
    validatePhone,
    validatePassword,
    validateRequired,
    validateMinLength,
    validateMaxLength,
    validateNumeric,
    validatePrice,
    validateDate
};