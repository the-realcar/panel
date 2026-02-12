<?php
?>
<!DOCTYPE html>
<html lang="pl" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="description" content="Panel Pracowniczy Firma KOT - Logowanie">
    <title>Logowanie - <?php echo e(APP_NAME); ?></title>

    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/mobile.css">
    <link rel="stylesheet" href="/assets/css/dark-mode.css">

    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚌</text></svg>">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    🚌 Firma KOT
                </div>
                <div class="login-subtitle">
                    Panel Pracowniczy
                </div>
            </div>

            <?php
            $flash = getFlashMessage();
            $flash_class = 'alert-info';
            if (!empty($flash['type'])) {
                if ($flash['type'] === 'success') $flash_class = 'alert-success';
                if ($flash['type'] === 'error') $flash_class = 'alert-error';
                if ($flash['type'] === 'warning') $flash_class = 'alert-warning';
                if ($flash['type'] === 'info') $flash_class = 'alert-info';
            }
            ?>
            <?php if (!empty($flash['message'])): ?>
                <div class="alert <?php echo e($flash_class); ?>">
                    <?php echo e($flash['message']); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors['login'])): ?>
                <div class="alert alert-error">
                    <?php echo e($errors['login']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <?php echo csrfField(); ?>
                <div class="form-group">
                    <label for="username" class="form-label">Nazwa użytkownika</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-control" 
                        value="<?php echo e($username); ?>"
                        autocomplete="username"
                        autofocus
                        required
                    >
                    <?php if (!empty($errors['username'])): ?>
                        <div class="form-error"><?php echo e($errors['username']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Hasło</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control"
                        autocomplete="current-password"
                        required
                    >
                    <?php if (!empty($errors['password'])): ?>
                        <div class="form-error"><?php echo e($errors['password']); ?></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Zaloguj się
                </button>
            </form>

            <?php $oauth_enabled = !empty($oauth['discord']) || !empty($oauth['roblox']); ?>
            <?php if ($oauth_enabled): ?>
                <div style="margin-top: 1.5rem; text-align: center;">
                    <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.75rem;">
                        Lub zaloguj sie przez
                    </p>
                    <div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap;">
                        <?php if (!empty($oauth['discord'])): ?>
                            <a class="btn btn-outline" href="/oauth/discord.php">Discord</a>
                        <?php endif; ?>
                        <?php if (!empty($oauth['roblox'])): ?>
                            <a class="btn btn-outline" href="/oauth/roblox.php">Roblox</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div style="margin-top: 1.5rem; text-align: center;">
                <a href="/reset-password.php" style="color: var(--text-muted); font-size: 0.875rem;">
                    Zapomniałeś hasła?
                </a>
            </div>

            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
                <p style="font-size: 0.75rem; color: var(--text-muted); text-align: center; margin: 0;">
                    <strong>Dane testowe:</strong><br>
                    admin / password123<br>
                    kierowca1 / password123<br>
                    dyspozytor1 / password123
                </p>
            </div>
        </div>
    </div>

    <script src="/assets/js/dark-mode.js"></script>
</body>
</html>
