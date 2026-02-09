<?php
/**
 * Ping endpoint - Keep session alive
 * Panel Pracowniczy Firma KOT
 */

require_once __DIR__ . '/../config/config.php';

// Update last activity
if (isLoggedIn()) {
    $_SESSION['last_activity'] = time();
    http_response_code(200);
    echo json_encode(['success' => true]);
} else {
    http_response_code(401);
    echo json_encode(['success' => false]);
}
