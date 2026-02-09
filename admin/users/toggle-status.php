<?php
/**
 * Toggle User Status (Activate/Deactivate)
 * Panel Pracowniczy Firma KOT
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/RBAC.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

$rbac = new RBAC();
$rbac->requirePermission('users', 'update');

// Verify CSRF token
if (!verifyCsrfToken($_GET['csrf_token'] ?? '')) {
    setFlashMessage('error', 'Nieprawidłowy token CSRF.');
    redirect('/admin/users/index.php');
}

// Get user ID
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = $_GET['action'] ?? '';

if (!$user_id || !in_array($action, ['activate', 'deactivate'])) {
    setFlashMessage('error', 'Nieprawidłowe parametry.');
    redirect('/admin/users/index.php');
}

// Cannot deactivate yourself
if ($user_id == getCurrentUserId()) {
    setFlashMessage('error', 'Nie możesz dezaktywować własnego konta.');
    redirect('/admin/users/index.php');
}

$db = new Database();

// Check if user exists
$query = "SELECT * FROM users WHERE id = :id";
$user = $db->queryOne($query, [':id' => $user_id]);

if (!$user) {
    setFlashMessage('error', 'Użytkownik nie został znaleziony.');
    redirect('/admin/users/index.php');
}

try {
    $new_status = $action === 'activate' ? 'true' : 'false';
    
    $update_query = "UPDATE users SET active = :active, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
    $db->execute($update_query, [
        ':active' => $new_status,
        ':id' => $user_id
    ]);
    
    $message = $action === 'activate' ? 'Użytkownik został aktywowany.' : 'Użytkownik został dezaktywowany.';
    setFlashMessage('success', $message);
} catch (Exception $e) {
    error_log('Error toggling user status: ' . $e->getMessage());
    setFlashMessage('error', 'Wystąpił błąd podczas zmiany statusu użytkownika.');
}

redirect('/admin/users/index.php');
