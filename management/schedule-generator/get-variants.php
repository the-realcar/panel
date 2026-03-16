<?php

require_once __DIR__ . '/../../app/bootstrap.php';

requireLogin();

$rbac = new RBAC();
if (!$rbac->hasRole('Zarząd') && !$rbac->isAdmin()) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

$line_id = isset($_GET['line_id']) ? (int)$_GET['line_id'] : 0;

if (!$line_id) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

$variants = RouteVariant::listByLine($line_id, true);

$output = [];
foreach ($variants as $v) {
    $output[] = [
        'id'           => (int)$v['id'],
        'variant_name' => $v['variant_name'],
        'direction'    => $v['direction'],
        'stops_count'  => (int)$v['stops_count'],
    ];
}

header('Content-Type: application/json');
echo json_encode($output);
