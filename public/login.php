<?php
/**
 * Login Page
 * Panel Pracowniczy Firma KOT
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: /public/index.php');
    exit;
}

$errors = [];
$username = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validate input
    $validator = new Validator($_POST);
    $validator->required('username', 'Nazwa użytkownika jest wymagana')
              ->required('password', 'Hasło jest wymagane');
    
    if ($validator->passes()) {
        $auth = new Auth();
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        if ($auth->login($username, $password, $ip_address, $user_agent)) {
            // Login successful
            $redirect = $_GET['redirect'] ?? '/public/index.php';
            header('Location: ' . $redirect);
            exit;
        } else {
            $errors['login'] = 'Nieprawidłowa nazwa użytkownika lub hasło.';
        }
    } else {
        $errors = $validator->getErrors();
    }
}
?>
<!DOCTYPE html>
<html lang="pl" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="description" content="Panel Pracowniczy Firma KOT - Logowanie">
    <title>Logowanie - <?php echo e(APP_NAME); ?></title>
    
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
                    Panel Pracowniczy
                </div>
            </div>
            
            <?php if (!empty($errors['login'])): ?>
                <div class="alert alert-error">
                    <?php echo e($errors['login']); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
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
            
            <div style="margin-top: 1.5rem; text-align: center;">
                <a href="/public/reset-password.php" style="color: var(--text-muted); font-size: 0.875rem;">
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
    
    <script src="/public/assets/js/dark-mode.js"></script>
</body>
</html>
