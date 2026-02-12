<?php
/**
 * Session Configuration
 * Panel Pracowniczy Firma KOT
 */

// Session security settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
ini_set('session.cookie_samesite', 'Strict');

// Session name
session_name('FIRMAKOT_SESSION');

// Session lifetime
ini_set('session.gc_maxlifetime', SESSION_TIMEOUT);
ini_set('session.cookie_lifetime', SESSION_TIMEOUT);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 * 
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

/**
 * Check session timeout
 * 
 * @return bool True if session is valid, false if timed out
 */
function checkSessionTimeout() {
    if (!isLoggedIn()) {
        return false;
    }
    
    if (isset($_SESSION['last_activity'])) {
        $inactive = time() - $_SESSION['last_activity'];
        
        if ($inactive > SESSION_TIMEOUT) {
            session_unset();
            session_destroy();
            return false;
        }
    }
    
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Require login - redirect to login page if not logged in
 * 
 * @param string $redirect_to URL to redirect to after login
 */
function requireLogin($redirect_to = null) {
    if (!isLoggedIn() || !checkSessionTimeout()) {
        $redirect_url = '/login.php';
        
        if ($redirect_to) {
            $redirect_url .= '?redirect=' . urlencode($redirect_to);
        }
        
        header('Location: ' . $redirect_url);
        exit;
    }
}

/**
 * Get current user ID
 * 
 * @return int|null
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current username
 * 
 * @return string|null
 */
function getCurrentUsername() {
    return $_SESSION['username'] ?? null;
}

/**
 * Set flash message
 * 
 * @param string $type success, error, warning, info
 * @param string $message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 * 
 * @return array|null
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Regenerate session ID for security
 */
function regenerateSession() {
    session_regenerate_id(true);
}
