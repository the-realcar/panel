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
define('SESSION_TIMEOUT', 1800); // 30 minutes in seconds
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes in seconds

// Pagination
define('ITEMS_PER_PAGE', 20);

// Date/Time
define('TIMEZONE', 'Europe/Warsaw');
date_default_timezone_set(TIMEZONE);

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
