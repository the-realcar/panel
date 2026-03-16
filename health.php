<?php

$start = microtime(true);

require_once __DIR__ . '/config/config.php';

$db_ok = true;
$db_error = null;
$db_ms = null;

try {
    $pdo = getDatabaseConnection();
    $query_start = microtime(true);
    $stmt = $pdo->query('SELECT 1');
    $stmt->fetchColumn();
    $db_ms = (microtime(true) - $query_start) * 1000;
} catch (Throwable $e) {
    $db_ok = false;
    $db_error = $e->getMessage();
}

$https_enabled = false;
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    $https_enabled = true;
} elseif (defined('BASE_URL') && parse_url(BASE_URL, PHP_URL_SCHEME) === 'https') {
    $https_enabled = true;
}

$total_ms = (microtime(true) - $start) * 1000;

$status_code = $db_ok ? 200 : 503;
http_response_code($status_code);
header('Content-Type: application/json; charset=UTF-8');

echo json_encode([
    'status' => $db_ok ? 'ok' : 'fail',
    'timestamp' => date('c'),
    'sla' => [
        'max_response_ms' => (int)SLA_MAX_RESPONSE_MS,
        'uptime_target_percent' => (float)SLA_UPTIME_TARGET_PERCENT
    ],
    'checks' => [
        'db_ok' => $db_ok,
        'db_response_ms' => $db_ms !== null ? round($db_ms, 2) : null,
        'https_enabled' => $https_enabled,
        'response_ms' => round($total_ms, 2)
    ],
    'error' => $db_error
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
