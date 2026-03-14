<?php

require_once __DIR__ . '/../app/bootstrap.php';
require_once BASE_PATH . '/app/Controllers/HRController.php';

$controller = new HRController();
$controller->monthlyReport();
