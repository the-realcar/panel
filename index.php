<?php
/**
 * =============================================================================
 * PANEL PRACOWNICZY PPUT OSTRANS - STRONA GŁÓWNA (LOGOWANIE)
 * =============================================================================
 * Wersja: 1.0
 * Data: 2026-01-23
 * Wymagania: 3.1 - Moduł Uwierzytelniania
 * =============================================================================
 */

// Rozpoczęcie sesji
session_start();

// Sprawdzenie czy użytkownik jest już zalogowany
if (isset($_SESSION['user_id'])) {
    // Przekierowanie do odpowiedniego panelu w zależności od roli
    header('Location: dashboard.php');
    exit;
}

// Obsługa błędów logowania
$error_message = '';
if (isset($_SESSION['login_error'])) {
    $error_message = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

// Obsługa komunikatów sukcesu (np. po resecie hasła)
$success_message = '';
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Panel Pracowniczy PPUT Ostrans - System zarządzania wirtualnym przedsiębiorstwem transportowym">
    <meta name="theme-color" content="#FF6B6B">
    
    <!-- Apple Web App Meta Tags -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <title>Logowanie - Panel Ostrans</title>
    
    <!-- Główny arkusz stylów -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Favicon (do dodania w przyszłości) -->
    <!-- <link rel="icon" type="image/png" href="assets/images/favicon.png"> -->
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container fade-in">
            <!-- Nagłówek z logo -->
            <div class="login-header">
                <div class="logo">OSTRANS</div>
                <div class="subtitle">Panel Pracowniczy</div>
            </div>

            <!-- Komunikaty błędów -->
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Komunikaty sukcesu -->
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <!-- Formularz logowania -->
            <form action="auth/login.php" method="POST" class="login-form">
                <!-- Pole loginu -->
                <div class="form-group">
                    <label for="login" class="form-label">Login</label>
                    <input 
                        type="text" 
                        id="login" 
                        name="login" 
                        class="form-input" 
                        placeholder="Wprowadź login" 
                        required 
                        autofocus
                        autocomplete="username"
                    >
                </div>

                <!-- Pole hasła -->
                <div class="form-group">
                    <label for="password" class="form-label">Hasło</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        placeholder="Wprowadź hasło" 
                        required
                        autocomplete="current-password"
                    >
                </div>

                <!-- Checkbox "Zapamiętaj mnie" -->
                <div class="form-group">
                    <label style="display: flex; align-items: center;">
                        <input 
                            type="checkbox" 
                            name="remember_me" 
                            class="form-checkbox"
                            value="1"
                        >
                        <span style="font-size: var(--font-size-sm);">Zapamiętaj mnie</span>
                    </label>
                </div>

                <!-- Token CSRF (zabezpieczenie) -->
                <?php
                    // Generowanie tokena CSRF
                    if (empty($_SESSION['csrf_token'])) {
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    }
                ?>
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <!-- Przycisk logowania -->
                <button type="submit" class="btn btn-block">
                    Zaloguj się
                </button>
            </form>

            <!-- Linki pomocnicze -->
            <div class="helper-links">
                <a href="auth/forgot-password.php" class="helper-link">
                    Zapomniałeś hasła?
                </a>
            </div>

            <!-- Informacja o systemie -->
            <div style="margin-top: var(--spacing-xl); text-align: center; font-size: var(--font-size-sm); color: var(--text-secondary);">
                <p>
                    System zarządzania wirtualnym przedsiębiorstwem transportowym
                </p>
                <p style="margin-top: var(--spacing-sm);">
                    <strong>PPUT Ostrans</strong> &copy; <?php echo date('Y'); ?>
                </p>
            </div>
        </div>
    </div>

    <!-- 
    =============================================================================
    NOTATKI DEWELOPERSKIE:
    =============================================================================
    
    Struktura do dalszego rozwoju:
    
    1. Plik auth/login.php - Obsługa procesu logowania:
       - Walidacja CSRF tokena
       - Weryfikacja loginu i hasła z bazą danych
       - Tworzenie sesji użytkownika
       - Logowanie próby logowania (tabela logi_systemowe)
    
    2. Plik dashboard.php - Panel główny po zalogowaniu:
       - Różne widoki w zależności od roli (Kierowca, Dyspozytor, etc.)
       - Redirect do odpowiednich modułów
    
    3. Plik auth/forgot-password.php - Reset hasła:
       - Formularz z polem email
       - Generowanie tokena resetującego
       - Wysyłka emaila z linkiem (3.1)
    
    4. Moduły zgodnie z wymaganiami:
       - panels/driver/ - Panel Kierowcy (3.2)
       - panels/dispatcher/ - Panel Dyspozytora (3.3)
       - panels/hr/ - Panel Kadrowo-Administracyjny (3.4)
       - panels/management/ - Panel Zarządu (3.5)
    
    5. Funkcje bezpieczeństwa (3.1):
       - Timeout sesji (automatyczne wylogowanie)
       - RBAC - kontrola dostępu na podstawie ról
       - Hashowanie haseł (bcrypt/Argon2)
    
    =============================================================================
    -->

    <script>
        // Opcjonalny JavaScript dla przyszłych funkcjonalności
        
        // Detekcja preferowanego trybu (ciemny/jasny)
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            console.log('Dark mode detected');
            // Możliwość zapisania preferencji użytkownika
        }
        
        // Walidacja formularza (po stronie klienta - dodatkowa)
        document.querySelector('.login-form')?.addEventListener('submit', function(e) {
            const login = document.getElementById('login').value.trim();
            const password = document.getElementById('password').value;
            
            if (login.length < 3) {
                e.preventDefault();
                alert('Login musi mieć co najmniej 3 znaki.');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Hasło musi mieć co najmniej 6 znaków.');
                return false;
            }
        });
    </script>
</body>
</html>
