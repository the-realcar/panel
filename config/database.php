<?php
/**
 * =============================================================================
 * PANEL PRACOWNICZY PPUT OSTRANS - KONFIGURACJA BAZY DANYCH
 * =============================================================================
 * Plik konfiguracyjny dla połączenia z bazą danych PostgreSQL
 * Wersja: 1.0
 * Data: 2026-01-23
 * =============================================================================
 */

// Konfiguracja połączenia z bazą danych PostgreSQL
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '5432');
define('DB_NAME', getenv('DB_NAME') ?: 'ostrans_panel');
define('DB_USER', getenv('DB_USER') ?: 'ostrans_user');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');

// Opcje połączenia PDO
define('DB_CHARSET', 'utf8');

/**
 * Funkcja nawiązująca połączenie z bazą danych
 * 
 * @return PDO|null Zwraca obiekt PDO lub null w przypadku błędu
 */
function getDatabaseConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            // Budowanie DSN (Data Source Name) dla PostgreSQL
            $dsn = sprintf(
                "pgsql:host=%s;port=%s;dbname=%s",
                DB_HOST,
                DB_PORT,
                DB_NAME
            );
            
            // Opcje połączenia PDO
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => false, // Wyłączamy persistent connections dla lepszej kontroli
            ];
            
            // Utworzenie połączenia
            $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
            
            // Ustawienie kodowania znaków
            $pdo->exec("SET NAMES 'UTF8'");
            
        } catch (PDOException $e) {
            // Logowanie błędu (w produkcji należy użyć właściwego systemu logowania)
            error_log("Database Connection Error: " . $e->getMessage());
            
            // W środowisku deweloperskim można wyświetlić błąd
            if (getenv('APP_ENV') === 'development') {
                die("Błąd połączenia z bazą danych: " . $e->getMessage());
            } else {
                die("Błąd połączenia z bazą danych. Skontaktuj się z administratorem.");
            }
        }
    }
    
    return $pdo;
}

/**
 * Funkcja zamykająca połączenie z bazą danych
 */
function closeDatabaseConnection() {
    global $pdo;
    $pdo = null;
}

/**
 * Funkcja sprawdzająca dostępność połączenia z bazą danych
 * 
 * @return bool True jeśli połączenie działa, false w przeciwnym razie
 */
function testDatabaseConnection() {
    try {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->query("SELECT 1");
        return $stmt !== false;
    } catch (Exception $e) {
        error_log("Database Test Failed: " . $e->getMessage());
        return false;
    }
}

// =============================================================================
// PRZYKŁAD UŻYCIA:
// =============================================================================
// $pdo = getDatabaseConnection();
// $stmt = $pdo->prepare("SELECT * FROM konta_uzytkownikow WHERE login = :login");
// $stmt->execute(['login' => $username]);
// $user = $stmt->fetch();
// =============================================================================
