<?php
/**
 * Check session validity endpoint
 * Panel Pracowniczy Firma KOT
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$valid = isLoggedIn() && checkSessionTimeout();

echo json_encode([
    'valid' => $valid,
    'timestamp' => time()
]);
