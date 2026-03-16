<?php
/**
 * Session Configuration
 * Panel Pracowniczy Firma KOT
 */

// Session security settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
// Enable secure cookies automatically when running over HTTPS
$is_https = false;
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    $is_https = true;
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $is_https = true;
} elseif (defined('BASE_URL') && parse_url(BASE_URL, PHP_URL_SCHEME) === 'https') {
    $is_https = true;
}

ini_set('session.cookie_secure', $is_https ? 1 : 0);
ini_set('session.cookie_samesite', 'Strict');

// Session name
session_name('FIRMAKOT_SESSION');

/**
 * Resolve effective session timeout (seconds).
 * Falls back to SESSION_TIMEOUT when DB value is unavailable or invalid.
 *
 * @return int
 */
function getSessionTimeoutSeconds() {
    static $resolved_timeout = null;

    if ($resolved_timeout !== null) {
        return $resolved_timeout;
    }

    $resolved_timeout = (int)SESSION_TIMEOUT;

    try {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare('SELECT value FROM settings WHERE key = :key LIMIT 1');
        $stmt->execute([':key' => 'session_timeout']);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && isset($row['value']) && ctype_digit((string)$row['value'])) {
            $db_timeout = (int)$row['value'];
            if ($db_timeout >= 300 && $db_timeout <= 86400) {
                $resolved_timeout = $db_timeout;
            }
        }
    } catch (Throwable $e) {
        // Keep default timeout if settings table/value is not available.
    }

    return $resolved_timeout;
}

$session_timeout_seconds = getSessionTimeoutSeconds();

// Session lifetime
ini_set('session.gc_maxlifetime', $session_timeout_seconds);
ini_set('session.cookie_lifetime', $session_timeout_seconds);

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

    $session_timeout_seconds = getSessionTimeoutSeconds();
    
    if (isset($_SESSION['last_activity'])) {
        $inactive = time() - $_SESSION['last_activity'];
        
        if ($inactive > $session_timeout_seconds) {
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
