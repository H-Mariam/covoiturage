<?php
class Helper {
    // Formater une date en français
    public function formatDate($date, $format = 'd/m/Y') {
        if(empty($date)) return '';
        
        $dateTime = new DateTime($date);
        return $dateTime->format($format);
    }

    // Formater un prix
    public function formatPrice($price) {
        return number_format($price, 2, ',', ' ') . ' €';
    }

    // Générer un slug à partir d'une chaîne
    public function slugify($text) {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        
        return empty($text) ? 'n-a' : $text;
    }

    // Tronquer un texte
    public function truncate($text, $length = 100, $ending = '...') {
        if(strlen($text) > $length) {
            $text = substr($text, 0, $length);
            $text = substr($text, 0, strrpos($text, ' '));
            $text .= $ending;
        }
        return $text;
    }

    // Vérifier si une date est passée
    public function isPastDate($date) {
        $today = new DateTime();
        $checkDate = new DateTime($date);
        return $checkDate < $today;
    }

    // Calculer l'âge à partir d'une date de naissance
    public function calculateAge($birthdate) {
        $birth = new DateTime($birthdate);
        $today = new DateTime();
        $age = $today->diff($birth);
        return $age->y;
    }

    // Générer un code aléatoire
    public function generateRandomCode($length = 8) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        
        for($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        return $code;
    }

    // Formater une durée en heures/minutes
    public function formatDuration($minutes) {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        
        if($hours > 0) {
            return $hours . 'h' . ($mins > 0 ? $mins . 'min' : '');
        } else {
            return $mins . 'min';
        }
    }

    // Calculer la distance entre deux points (formule simplifiée)
    public function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $earth_radius = 6371; // Rayon de la Terre en km
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) + 
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
             sin($dLon/2) * sin($dLon/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earth_radius * $c;
        
        return round($distance, 1);
    }

    // Formater un numéro de téléphone
    public function formatPhone($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if(strlen($phone) == 10) {
            return preg_replace('/(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/', '$1 $2 $3 $4 $5', $phone);
        }
        
        return $phone;
    }

    // Vérifier si l'utilisateur est connecté
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    // Rediriger avec un message flash
    public function redirectWithMessage($url, $type, $message) {
        $_SESSION['flash_message'] = [
            'type' => $type,
            'message' => $message
        ];
        header("Location: $url");
        exit();
    }

    // Obtenir l'URL de base
    public function baseUrl($path = '') {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $base = dirname($_SERVER['SCRIPT_NAME']);
        
        $url = $protocol . '://' . $host . $base;
        
        if(!empty($path)) {
            $url .= '/' . ltrim($path, '/');
        }
        
        return $url;
    }

    // Convertir en booléen
    public function toBool($value) {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    // Nettoyer un tableau
    public function cleanArray($array) {
        return array_map([$this, 'sanitize'], $array);
    }

    // Sanitizer général
    public function sanitize($input) {
        if(is_array($input)) {
            return $this->cleanArray($input);
        }
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
}
?>