<?php
/**
 * Delete Vehicle
 * Panel Pracowniczy Firma KOT
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/RBAC.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

$rbac = new RBAC();
$rbac->requirePermission('vehicles', 'delete');

// Verify CSRF token
if (!verifyCsrfToken($_GET['csrf_token'] ?? '')) {
    setFlashMessage('error', 'Nieprawidłowy token CSRF.');
    redirect('/admin/vehicles/index.php');
}

// Get vehicle ID
$vehicle_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$vehicle_id) {
    setFlashMessage('error', 'Nieprawidłowy ID pojazdu.');
    redirect('/admin/vehicles/index.php');
}

$db = new Database();

// Check if vehicle exists
$query = "SELECT * FROM vehicles WHERE id = :id";
$vehicle = $db->queryOne($query, [':id' => $vehicle_id]);

if (!$vehicle) {
    setFlashMessage('error', 'Pojazd nie został znaleziony.');
    redirect('/admin/vehicles/index.php');
}

try {
    // Delete vehicle
    $delete_query = "DELETE FROM vehicles WHERE id = :id";
    $db->execute($delete_query, [':id' => $vehicle_id]);
    
    setFlashMessage('success', 'Pojazd został usunięty pomyślnie.');
} catch (Exception $e) {
    error_log('Error deleting vehicle: ' . $e->getMessage());
    setFlashMessage('error', 'Nie można usunąć pojazdu. Może być używany w innych miejscach systemu.');
}

redirect('/admin/vehicles/index.php');
