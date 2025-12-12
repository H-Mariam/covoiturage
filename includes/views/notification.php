<?php
/**
 * Fonction pour afficher une notification
 */
function displayNotification($type, $message) {
    $icons = [
        'success' => 'check-circle-fill',
        'error' => 'exclamation-triangle-fill',
        'warning' => 'exclamation-circle-fill',
        'info' => 'info-circle-fill'
    ];
    
    $icon = $icons[$type] ?? 'info-circle-fill';
    
    return '
    <div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">
        <i class="bi bi-' . $icon . ' me-2"></i>
        ' . htmlspecialchars($message) . '
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
}

/**
 * Fonction pour ajouter un message flash
 */
function addFlashMessage($type, $message) {
    if(session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if(!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message,
        'timestamp' => time()
    ];
}

/**
 * Fonction pour afficher les messages flash
 */
function displayFlashMessages() {
    if(session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if(isset($_SESSION['flash_messages']) && !empty($_SESSION['flash_messages'])) {
        $html = '';
        foreach($_SESSION['flash_messages'] as $flash) {
            $html .= displayNotification($flash['type'], $flash['message']);
        }
        
        // Effacer les messages après affichage
        unset($_SESSION['flash_messages']);
        
        return $html;
    }
    
    return '';
}
?>