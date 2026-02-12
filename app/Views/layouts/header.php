<?php
$page_title = $page_title ?? 'Panel Pracowniczy';
$current_user = getCurrentUsername();
?>
<!DOCTYPE html>
<html lang="pl" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="description" content="Panel Pracowniczy Firma KOT - System zarzadzania">
    <title><?php echo e($page_title); ?> - <?php echo e(APP_NAME); ?></title>

    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/mobile.css">
    <link rel="stylesheet" href="/assets/css/dark-mode.css">

    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>K</text></svg>">
</head>
<body>
    <div class="wrapper">
        <header class="header">
            <div class="container">
                <div class="header-inner">
                    <div class="header-logo">
                        <a href="/" style="color: var(--text); text-decoration: none;">
                            <?php echo e(APP_NAME); ?>
                        </a>
                    </div>

                    <?php if (isLoggedIn()): ?>
                    <div class="header-actions">
                        <div class="header-user">
                            <span class="hide-mobile">User</span>
                            <span><?php echo e($current_user); ?></span>
                        </div>
                        <a href="/logout.php" class="btn btn-sm btn-outline">Wyloguj</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <?php if (isLoggedIn()): ?>
            <?php View::partial('layouts/navigation'); ?>
        <?php endif; ?>

        <main class="main-content">
            <div class="container">
                <?php
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
