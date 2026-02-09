<?php
/**
 * Delete Line
 * Panel Pracowniczy Firma KOT
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/RBAC.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

$rbac = new RBAC();
$rbac->requirePermission('lines', 'delete');

// Verify CSRF token
if (!verifyCsrfToken($_GET['csrf_token'] ?? '')) {
    setFlashMessage('error', 'Nieprawidłowy token CSRF.');
    redirect('/admin/lines/index.php');
}

// Get line ID
$line_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$line_id) {
    setFlashMessage('error', 'Nieprawidłowy ID linii.');
    redirect('/admin/lines/index.php');
}

$db = new Database();

// Check if line exists
$query = "SELECT * FROM lines WHERE id = :id";
$line = $db->queryOne($query, [':id' => $line_id]);

if (!$line) {
    setFlashMessage('error', 'Linia nie została znaleziona.');
    redirect('/admin/lines/index.php');
}

try {
    // Delete line
    $delete_query = "DELETE FROM lines WHERE id = :id";
    $db->execute($delete_query, [':id' => $line_id]);
    
    setFlashMessage('success', 'Linia została usunięta pomyślnie.');
} catch (Exception $e) {
    error_log('Error deleting line: ' . $e->getMessage());
    setFlashMessage('error', 'Nie można usunąć linii. Może być używana w innych miejscach systemu.');
}

redirect('/admin/lines/index.php');
