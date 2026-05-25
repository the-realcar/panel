<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);

$is_https = false;
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    $is_https = true;
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $is_https = true;
} elseif (defined('BASE_URL') && parse_url(BASE_URL, PHP_URL_SCHEME) === 'https') {
    $is_https = true;
}

session_name('FIRMAKOT_SESSION');

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
    }

    return $resolved_timeout;
}

$session_timeout_seconds = getSessionTimeoutSeconds();
$session_path = BASE_PATH . '/storage/sessions';

if (!is_dir($session_path)) {
    @mkdir($session_path, 0775, true);
}

if (is_dir($session_path) && is_writable($session_path)) {
    session_save_path($session_path);
}

session_set_cookie_params([
    'lifetime' => $session_timeout_seconds,
    'path' => '/',
    'secure' => $is_https,
    'httponly' => true,
    'samesite' => 'Lax'
]);

ini_set('session.gc_maxlifetime', (string)$session_timeout_seconds);
ini_set('session.cookie_lifetime', (string)$session_timeout_seconds);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

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

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUsername() {
    return $_SESSION['username'] ?? null;
}

function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

function regenerateSession() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    session_regenerate_id(true);
    $_SESSION['last_activity'] = time();
}
