<?php
/**
 * Database Configuration
 * Panel Pracowniczy Firma KOT
 */

// Database connection parameters
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '5432');
define('DB_NAME', getenv('DB_NAME') ?: 'panel_firmakot');
define('DB_USER', getenv('DB_USER') ?: 'postgres');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: 'postgres');
define('DB_CHARSET', 'utf8');

// PDO connection string
define('DB_DSN', sprintf(
    'pgsql:host=%s;port=%s;dbname=%s',
    DB_HOST,
    DB_PORT,
    DB_NAME
));

// PDO options
define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::ATTR_STRINGIFY_FETCHES => false
]);

/**
 * Get database connection
 * 
 * @return PDO
 * @throws PDOException
 */
function getDatabaseConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD, DB_OPTIONS);
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw new PDOException('Nie można połączyć się z bazą danych');
        }
    }
    
    return $pdo;
}
