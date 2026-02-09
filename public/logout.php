<?php
/**
 * Logout Page
 * Panel Pracowniczy Firma KOT
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Auth.php';

$auth = new Auth();
$auth->logout();

// Redirect to login page
header('Location: /public/login.php');
exit;
