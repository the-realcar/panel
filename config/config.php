<?php
/**
 * Application Configuration
 * Panel Pracowniczy Firma KOT
 */

// Application settings
define('APP_NAME', 'Panel Pracowniczy Firma KOT');
define('APP_VERSION', '1.0.0');
define('APP_ENV', getenv('APP_ENV') ?: 'production'); // development, production

// Paths
define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', BASE_PATH . '/public');
define('CORE_PATH', BASE_PATH . '/core');
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('CONFIG_PATH', BASE_PATH . '/config');

// URLs
define('BASE_URL', getenv('BASE_URL') ?: 'http://localhost');
define('ASSETS_URL', BASE_URL . '/assets');

// OAuth configuration
define('OAUTH_REDIRECT_BASE', getenv('OAUTH_REDIRECT_BASE') ?: BASE_URL);

define('DISCORD_CLIENT_ID', getenv('DISCORD_CLIENT_ID') ?: '');
define('DISCORD_CLIENT_SECRET', getenv('DISCORD_CLIENT_SECRET') ?: '');
define('DISCORD_SCOPE', getenv('DISCORD_SCOPE') ?: 'identify guilds');
define('DISCORD_AUTHORIZE_URL', 'https://discord.com/oauth2/authorize');
define('DISCORD_TOKEN_URL', 'https://discord.com/api/oauth2/token');
define('DISCORD_USER_URL', 'https://discord.com/api/users/@me');
define('DISCORD_REDIRECT_URI', OAUTH_REDIRECT_BASE . '/oauth/discord-callback.php');

define('ROBLOX_CLIENT_ID', getenv('ROBLOX_CLIENT_ID') ?: '');
define('ROBLOX_CLIENT_SECRET', getenv('ROBLOX_CLIENT_SECRET') ?: '');
define('ROBLOX_SCOPE', getenv('ROBLOX_SCOPE') ?: 'openid profile');
define('ROBLOX_AUTHORIZE_URL', 'https://apis.roblox.com/oauth/v1/authorize');
define('ROBLOX_TOKEN_URL', 'https://apis.roblox.com/oauth/v1/token');
define('ROBLOX_USER_URL', 'https://apis.roblox.com/oauth/v1/userinfo');
define('ROBLOX_REDIRECT_URI', OAUTH_REDIRECT_BASE . '/oauth/roblox-callback.php');

// Security
define('SESSION_TIMEOUT', 7200); // 2 hours in seconds
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes in seconds

// Email / SMTP
define('MAIL_HOST',       getenv('MAIL_HOST')       ?: 'smtp.gmail.com');
define('MAIL_PORT',       (int)(getenv('MAIL_PORT') ?: 587));
define('MAIL_USERNAME',   getenv('MAIL_USERNAME')   ?: '');
define('MAIL_PASSWORD',   getenv('MAIL_PASSWORD')   ?: '');
define('MAIL_FROM',       getenv('MAIL_FROM')       ?: 'noreply@firmakot.pl');
define('MAIL_FROM_NAME',  getenv('MAIL_FROM_NAME')  ?: APP_NAME);
define('MAIL_ENCRYPTION', getenv('MAIL_ENCRYPTION') ?: 'tls'); // tls or ssl

// Pagination
define('ITEMS_PER_PAGE', 20);

// Nonfunctional SLA targets
define('SLA_MAX_RESPONSE_MS', 2000);
define('SLA_UPTIME_TARGET_PERCENT', 99.9);

// Date/Time
define('TIMEZONE', 'Europe/Warsaw');
date_default_timezone_set(TIMEZONE);

/**
 * Persist an application error to error_logs table (best effort).
 *
 * @param string $type
 * @param string $message
 * @param string|null $filePath
 * @param int|null $lineNumber
 * @param array|null $context
 */
function logErrorToDatabase($type, $message, $filePath = null, $lineNumber = null, $context = null) {
    static $is_logging = false;

    if ($is_logging) {
        return;
    }

    $is_logging = true;

    try {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO error_logs (error_type, message, file_path, line_number, context, ip_address, user_agent, user_id)
             VALUES (:error_type, :message, :file_path, :line_number, :context, :ip_address, :user_agent, :user_id)'
        );

        $stmt->execute([
            ':error_type' => (string)$type,
            ':message' => (string)$message,
            ':file_path' => $filePath !== null ? (string)$filePath : null,
            ':line_number' => $lineNumber !== null ? (int)$lineNumber : null,
            ':context' => $context !== null ? json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ':user_id' => isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null
        ]);
    } catch (Throwable $e) {
        // Keep default PHP logging behavior if DB logging is unavailable.
    }

    $is_logging = false;
}

set_error_handler(function ($severity, $message, $file, $line) {
    $type_map = [
        E_ERROR => 'E_ERROR',
        E_WARNING => 'E_WARNING',
        E_PARSE => 'E_PARSE',
        E_NOTICE => 'E_NOTICE',
        E_CORE_ERROR => 'E_CORE_ERROR',
        E_CORE_WARNING => 'E_CORE_WARNING',
        E_COMPILE_ERROR => 'E_COMPILE_ERROR',
        E_COMPILE_WARNING => 'E_COMPILE_WARNING',
        E_USER_ERROR => 'E_USER_ERROR',
        E_USER_WARNING => 'E_USER_WARNING',
        E_USER_NOTICE => 'E_USER_NOTICE',
        E_STRICT => 'E_STRICT',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        E_DEPRECATED => 'E_DEPRECATED',
        E_USER_DEPRECATED => 'E_USER_DEPRECATED'
    ];

    $error_type = $type_map[$severity] ?? ('E_' . (string)$severity);

    logErrorToDatabase($error_type, (string)$message, (string)$file, (int)$line, [
        'severity' => $severity
    ]);

    return false;
});

set_exception_handler(function ($exception) {
    logErrorToDatabase(
        'UNCAUGHT_EXCEPTION',
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        [
            'trace' => $exception->getTraceAsString()
        ]
    );
});

register_shutdown_function(function () {
    $error = error_get_last();
    if (!$error) {
        return;
    }

    $fatal = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
    if (in_array($error['type'], $fatal, true)) {
        logErrorToDatabase(
            'FATAL_ERROR',
            (string)$error['message'],
            (string)$error['file'],
            (int)$error['line'],
            ['type' => (int)$error['type']]
        );
    }
});

// Error handling
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', BASE_PATH . '/logs/error.log');
}

// Locale
setlocale(LC_TIME, 'pl_PL.UTF-8', 'Polish_Poland.1250');

// Load database configuration
require_once CONFIG_PATH . '/database.php';

// Load session configuration
require_once CONFIG_PATH . '/session.php';
