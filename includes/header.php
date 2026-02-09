<?php
/**
 * Common Header
 * Panel Pracowniczy Firma KOT
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    require_once __DIR__ . '/../config/config.php';
}

$page_title = $page_title ?? 'Panel Pracowniczy';
$current_user = getCurrentUsername();
$current_user_id = getCurrentUserId();
?>
<!DOCTYPE html>
<html lang="pl" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="description" content="Panel Pracowniczy Firma KOT - System zarządzania">
    <title><?php echo e($page_title); ?> - <?php echo e(APP_NAME); ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="/public/assets/css/main.css">
    <link rel="stylesheet" href="/public/assets/css/mobile.css">
    <link rel="stylesheet" href="/public/assets/css/dark-mode.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚌</text></svg>">
</head>
<body>
    <div class="wrapper">
        <!-- Header -->
        <header class="header">
            <div class="container">
                <div class="header-inner">
                    <div class="header-logo">
                        <a href="/" style="color: var(--text); text-decoration: none;">
                            🚌 <?php echo e(APP_NAME); ?>
                        </a>
                    </div>
                    
                    <?php if (isLoggedIn()): ?>
                    <div class="header-actions">
                        <div class="header-user">
                            <span class="hide-mobile">👤</span>
                            <span><?php echo e($current_user); ?></span>
                        </div>
                        <a href="/public/logout.php" class="btn btn-sm btn-outline">Wyloguj</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </header>
        
        <?php if (isLoggedIn()): ?>
            <?php include __DIR__ . '/navigation.php'; ?>
        <?php endif; ?>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="container">
                <?php
                // Display flash messages
                $flash = getFlashMessage();
                if ($flash):
                    $alert_type = $flash['type'];
                    if ($alert_type === 'success') {
                        $alert_class = 'alert-success';
                    } elseif ($alert_type === 'error') {
                        $alert_class = 'alert-error';
                    } elseif ($alert_type === 'warning') {
                        $alert_class = 'alert-warning';
                    } else {
                        $alert_class = 'alert-info';
                    }
                ?>
                <div class="alert <?php echo $alert_class; ?>" data-auto-hide>
                    <?php echo e($flash['message']); ?>
                </div>
                <?php endif; ?>
