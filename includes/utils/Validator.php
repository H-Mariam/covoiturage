<?php
class Validator {
    // Valider un email
    public function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    // Valider un téléphone français
    public function validatePhone($phone) {
        // Format français : 01 23 45 67 89 ou 0123456789
        $pattern = '/^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/';
        return preg_match($pattern, $phone);
    }

    // Valider une date (format YYYY-MM-DD)
    public function validateDate($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    // Valider un prix
    public function validatePrice($price) {
        return is_numeric($price) && $price >= 0;
    }

    // Valider un nombre de places
    public function validatePlaces($places) {
        return is_numeric($places) && $places > 0 && $places <= 8;
    }

    // Valider un mot de passe (au moins 8 caractères)
    public function validatePassword($password) {
        return strlen($password) >= 8;
    }

    // Valider un nom (lettres, espaces, apostrophes)
    public function validateName($name) {
        return preg_match('/^[a-zA-ZÀ-ÿ\s\'-]{2,100}$/u', $name);
    }

    // Nettoyer une chaîne de caractères
    public function sanitize($input) {
        return htmlspecialchars(strip_tags(trim($input)));
    }

    // Valider une adresse
    public function validateAddress($address) {
        return !empty($address) && strlen($address) <= 255;
    }

    // Valider une description
    public function validateDescription($description) {
        return strlen($description) <= 1000;
    }

    // Valider un rôle
    public function validateRole($role) {
        return in_array($role, ['user', 'admin']);
    }

    // Valider un statut
    public function validateStatus($status) {
        return in_array($status, ['active', 'inactive', 'pending']);
    }

    // Valider un code postal français
    public function validateZipCode($zip) {
        return preg_match('/^\d{5}$/', $zip);
    }

    // Valider une ville
    public function validateCity($city) {
        return preg_match('/^[a-zA-ZÀ-ÿ\s\-]{2,50}$/u', $city);
    }

    // Générer des erreurs de validation
    public function getValidationErrors($data, $rules) {
        $errors = [];
        
        foreach($rules as $field => $rule) {
            $value = $data[$field] ?? '';
            
            foreach($rule as $validation => $message) {
                switch($validation) {
                    case 'required':
                        if(empty($value)) {
                            $errors[$field] = $message;
                        }
                        break;
                        
                    case 'email':
                        if(!empty($value) && !$this->validateEmail($value)) {
                            $errors[$field] = $message;
                        }
                        break;
                        
                    case 'min':
                        $min = $message['value'];
                        if(strlen($value) < $min) {
                            $errors[$field] = $message['message'];
                        }
                        break;
                        
                    case 'max':
                        $max = $message['value'];
                        if(strlen($value) > $max) {
                            $errors[$field] = $message['message'];
                        }
                        break;
                        
                    case 'numeric':
                        if(!is_numeric($value)) {
                            $errors[$field] = $message;
                        }
                        break;
                }
            }
        }
        
        return $errors;
    }
}
?>