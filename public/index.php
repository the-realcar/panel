<?php
/**
 * Index Page - Redirect to appropriate dashboard
 * Panel Pracowniczy Firma KOT
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/RBAC.php';

// Check if logged in
if (!isLoggedIn()) {
    header('Location: /public/login.php');
    exit;
}

// Redirect based on role
$rbac = new RBAC();

if ($rbac->isAdmin()) {
    header('Location: /admin/dashboard.php');
} elseif ($rbac->hasRole('Kierowca')) {
    header('Location: /public/driver/dashboard.php');
} elseif ($rbac->hasRole('Dyspozytor')) {
    header('Location: /admin/dashboard.php');
} else {
    header('Location: /public/driver/dashboard.php');
}
exit;
