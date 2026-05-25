<?php
require_once __DIR__ . '/app/bootstrap.php';
$page_title = 'Polityka Prywatności';
$last_updated = '4 kwietnia 2026';
?>
<!DOCTYPE html>
<html lang="pl" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="description" content="Polityka Prywatności Panelu Pracowniczego Firma KOT">
    <title><?php echo e($page_title); ?> – <?php echo e(APP_NAME); ?></title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/mobile.css">
    <link rel="stylesheet" href="/assets/css/dark-mode.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚌</text></svg>">
    <style>
        body { background: var(--bg-secondary, #f5f5f5); }
        .legal-container { max-width: 860px; margin: 2rem auto; padding: 0 1rem 4rem; }
        .legal-card { background: var(--bg, #fff); border-radius: 12px; padding: 2.5rem 3rem; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
        .legal-card h1 { font-size: 1.75rem; margin-bottom: .25rem; }
        .legal-card .meta { color: var(--text-muted, #888); font-size: .875rem; margin-bottom: 2rem; }
        .legal-card h2 { font-size: 1.15rem; margin: 2rem 0 .6rem; border-bottom: 1px solid var(--border, #e0e0e0); padding-bottom: .4rem; }
        .legal-card p, .legal-card li { line-height: 1.75; color: var(--text, #333); }
        .legal-card ul { padding-left: 1.4rem; margin: .5rem 0; }
        .legal-card li { margin-bottom: .3rem; }
        .legal-back { display: inline-block; margin-bottom: 1.25rem; font-size: .9rem; color: var(--link, #0066cc); text-decoration: none; }
        .legal-back:hover { text-decoration: underline; }
        @media (max-width: 600px) { .legal-card { padding: 1.5rem 1.25rem; } }
    </style>
</head>
<body>
<div class="legal-container">
    <a href="/login.php" class="legal-back">← Powrót do logowania</a>
    <div class="legal-card">
        <h1>🔒 Polityka Prywatności</h1>
        <p class="meta">Ostatnia aktualizacja: <?php echo $last_updated; ?></p>
        <p>Niniejsza Polityka Prywatności opisuje, w jaki sposób <strong>Firma KOT</strong> gromadzi, przechowuje i przetwarza dane osobowe użytkowników Panelu Pracowniczego (dalej: „Panel").</p>
        <h2>1. Administrator danych</h2>
        <p>Administratorem danych osobowych jest <strong>Firma KOT</strong>, będąca wirtualną organizacją działającą w środowisku platformy Roblox. Kontakt: przez serwer Discord Firmy KOT lub wiadomość do administracji w Panelu.</p>
        <h2>2. Jakie dane gromadzimy</h2>
        <ul>
            <li><strong>Dane konta:</strong> nazwa użytkownika, adres e-mail, imię i nazwisko.</li>
            <li><strong>Identyfikatory zewnętrzne:</strong> Roblox User ID oraz Discord User ID – wyłącznie jeśli użytkownik połączy konto przez OAuth.</li>
            <li><strong>Dane zatrudnienia:</strong> przypisane stanowisko, brygada, harmonogram pracy.</li>
            <li><strong>Logi aktywności:</strong> daty logowań, adres IP, wykonane akcje – wyłącznie w celach bezpieczeństwa.</li>
            <li><strong>Dane zgłoszeń:</strong> treść raportów o incydentach i podań złożonych przez użytkownika.</li>
        </ul>
        <h2>3. Cel przetwarzania danych</h2>
        <ul>
            <li>Umożliwienie dostępu do Panelu i zarządzania kontem pracowniczym.</li>
            <li>Wyświetlanie harmonogramów, tras i przypisań służbowych.</li>
            <li>Komunikacja e-mailowa (np. reset hasła).</li>
            <li>Zapewnienie bezpieczeństwa systemu i prowadzenie rejestru audytu.</li>
            <li>Rozpatrywanie podań i zgłoszeń incydentów.</li>
        </ul>
        <h2>4. Podstawa prawna</h2>
        <p>Dane przetwarzane są na podstawie zgody użytkownika wyrażonej podczas rejestracji lub połączenia konta zewnętrznego, a także na podstawie prawnie uzasadnionego interesu administratora w zakresie bezpieczeństwa systemu.</p>
        <h2>5. Udostępnianie danych</h2>
        <p>Dane <strong>nie są sprzedawane ani przekazywane</strong> podmiotom trzecim w celach marketingowych. Mogą być udostępniane wyłącznie Administratorom Panelu oraz operatorowi hostingu w zakresie niezbędnym do utrzymania usługi.</p>
        <h2>6. Połączenie z Roblox i Discord (OAuth)</h2>
        <p>Jeśli użytkownik połączy konto przez OAuth platformy Roblox lub Discord, Panel pobiera wyłącznie unikalny identyfikator użytkownika (User ID) oraz nazwę użytkownika. Panel nie pobiera haseł ani adresów e-mail z tych platform. Połączenie można odwołać w ustawieniach konta.</p>
        <h2>7. Czas przechowywania danych</h2>
        <p>Dane konta przechowywane są przez czas aktywności konta. Po archiwizacji mogą być przechowywane do 12 miesięcy w celach audytowych, a następnie trwale usuwane lub anonimizowane.</p>
        <h2>8. Prawa użytkownika</h2>
        <ul>
            <li>Prawo dostępu, sprostowania i usunięcia danych.</li>
            <li>Prawo do ograniczenia przetwarzania i przenoszenia danych.</li>
            <li>Prawo do cofnięcia zgody w dowolnym momencie.</li>
        </ul>
        <h2>9. Bezpieczeństwo danych</h2>
        <p>Stosujemy szyfrowanie haseł (bcrypt), połączenia HTTPS, ochronę przed CSRF oraz rejestrowanie prób nieautoryzowanego dostępu.</p>
        <h2>10. Pliki cookie i sesje</h2>
        <p>Panel używa wyłącznie sesyjnego ciasteczka PHP do utrzymania zalogowanego stanu. Nie używamy ciasteczek śledzących ani reklamowych.</p>
        <h2>11. Zmiany polityki</h2>
        <p>O istotnych zmianach użytkownicy zostaną powiadomieni poprzez komunikat w Panelu lub wiadomość e-mail.</p>
    </div>
</div>
<script src="/assets/js/dark-mode.js"></script>
</body>
</html>