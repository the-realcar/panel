<?php
?>
<!DOCTYPE html>
<html lang="pl" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="description" content="Panel Pracowniczy Firma KOT - Reset hasla">
    <title>Reset hasla - <?php echo e(APP_NAME); ?></title>

    <link rel="stylesheet" href="/public/assets/css/main.css">
    <link rel="stylesheet" href="/public/assets/css/mobile.css">
    <link rel="stylesheet" href="/public/assets/css/dark-mode.css">

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
                    Reset hasla
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php
                    $flash = getFlashMessage();
                    if ($flash) {
                        echo e($flash['message']);
                    }
                    ?>
                </div>

                <div style="text-align: center; margin-top: 1.5rem;">
                    <a href="/public/login.php" class="btn btn-primary">
                        Powrot do logowania
                    </a>
                </div>
            <?php else: ?>
                <p style="color: var(--text-muted); margin-bottom: 1.5rem; text-align: center;">
                    Podaj adres email przypisany do Twojego konta. Wyslemy Ci link do resetowania hasla.
                </p>

                <?php if (!empty($errors['email'])): ?>
                    <div class="alert alert-error">
                        <?php echo e($errors['email']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <input type="hidden" name="action" value="request">

                    <div class="form-group">
                        <label for="email" class="form-label">Adres email</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-control" 
                            autocomplete="email"
                            autofocus
                            required
                        >
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        Wyslij link resetujacy
                    </button>
                </form>

                <div style="margin-top: 1.5rem; text-align: center;">
                    <a href="/public/login.php" style="color: var(--text-muted); font-size: 0.875rem;">
                        <- Powrot do logowania
                    </a>
                </div>
            <?php endif; ?>

            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
                <p style="font-size: 0.75rem; color: var(--text-muted); text-align: center; margin: 0;">
                    W przypadku problemow skontaktuj sie z administratorem systemu.
                </p>
            </div>
        </div>
    </div>

    <script src="/public/assets/js/dark-mode.js"></script>
</body>
</html>
