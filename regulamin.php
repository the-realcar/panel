<?php
require_once __DIR__ . '/app/bootstrap.php';
$page_title = 'Regulamin';
$last_updated = '4 kwietnia 2026';
?>
<!DOCTYPE html>
<html lang="pl" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="description" content="Regulamin Panelu Pracowniczego Firma KOT">
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
        .legal-card ul, .legal-card ol { padding-left: 1.4rem; margin: .5rem 0; }
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
        <h1>📋 Regulamin</h1>
        <p class="meta">Ostatnia aktualizacja: <?php echo $last_updated; ?></p>
        <p>Niniejszy Regulamin określa zasady korzystania z Panelu Pracowniczego <strong>Firmy KOT</strong>. Korzystanie z Panelu oznacza akceptację poniższych zasad.</p>
        <h2>1. Definicje</h2>
        <ul>
            <li><strong>Firma KOT</strong> – wirtualna organizacja transportowa działająca na platformie Roblox.</li>
            <li><strong>Panel</strong> – system informatyczny służący do zarządzania pracownikami, harmonogramami i flotą.</li>
            <li><strong>Użytkownik</strong> – osoba posiadająca aktywne konto w Panelu.</li>
            <li><strong>Administrator</strong> – osoba uprawniona do zarządzania Panelem i jego użytkownikami.</li>
        </ul>
        <h2>2. Warunki korzystania</h2>
        <ol>
            <li>Z Panelu mogą korzystać wyłącznie osoby, które otrzymały konto od Administratora.</li>
            <li>Użytkownik zobowiązany jest do podania prawdziwych danych.</li>
            <li>Konto jest przypisane do konkretnej osoby i nie może być udostępniane innym.</li>
            <li>Użytkownik zobowiązany jest do zachowania poufności danych logowania.</li>
        </ol>
        <h2>3. Obowiązki użytkownika</h2>
        <ul>
            <li>Przestrzeganie zasad i regulaminów Firmy KOT.</li>
            <li>Rzetelne wypełnianie kart drogowych, harmonogramów i raportów.</li>
            <li>Zgłaszanie incydentów zgodnie z procedurami.</li>
            <li>Niezwłoczne informowanie Administratora o podejrzeniu naruszenia bezpieczeństwa konta.</li>
            <li>Używanie Panelu wyłącznie w celach służbowych.</li>
        </ul>
        <h2>4. Działania zabronione</h2>
        <ul>
            <li>Udostępnianie danych logowania osobom trzecim.</li>
            <li>Próby nieautoryzowanego dostępu do kont innych użytkowników.</li>
            <li>Wprowadzanie nieprawdziwych danych w formularzach.</li>
            <li>Działania zakłócające pracę systemu (ataki DoS, skanowanie).</li>
            <li>Pobieranie danych innych użytkowników bez zgody Administratora.</li>
            <li>Używanie automatycznych skryptów lub botów.</li>
        </ul>
        <h2>5. Połączenie kont zewnętrznych (Roblox, Discord)</h2>
        <ol>
            <li>Użytkownik może opcjonalnie połączyć konto Roblox lub Discord z Panelem.</li>
            <li>Połączenie jest dobrowolne i służy wyłącznie weryfikacji tożsamości.</li>
            <li>Użytkownik może w każdej chwili odłączyć powiązane konto.</li>
            <li>Firma KOT nie ponosi odpowiedzialności za działania platform Roblox ani Discord.</li>
        </ol>
        <h2>6. Konta i dostęp</h2>
        <ol>
            <li>Administrator może dezaktywować konto w przypadku naruszenia Regulaminu.</li>
            <li>Konto nieaktywne przez dłuższy czas może zostać zarchiwizowane.</li>
            <li>Użytkownik może wnioskować o usunięcie konta, kontaktując się z Administratorem.</li>
        </ol>
        <h2>7. Dostępność systemu</h2>
        <p>Panel jest udostępniany w trybie ciągłym, jednak Firma KOT nie gwarantuje nieprzerwanego działania. Planowane przerwy ogłaszane są z wyprzedzeniem na serwerze Discord.</p>
        <h2>8. Ograniczenie odpowiedzialności</h2>
        <p>Firma KOT nie ponosi odpowiedzialności za szkody wynikłe z przerw w dostępie do Panelu, utratę danych spowodowaną siłą wyższą ani treści wprowadzone przez użytkowników.</p>
        <h2>9. Zgłaszanie naruszeń</h2>
        <p>Naruszenia Regulaminu należy zgłaszać Administratorowi przez serwer Discord lub wiadomość w Panelu.</p>
        <h2>10. Zmiany Regulaminu</h2>
        <p>O zmianach użytkownicy zostaną poinformowani co najmniej 7 dni przed ich wejściem w życie. Dalsze korzystanie z Panelu oznacza akceptację zmian.</p>
        <h2>11. Prawo właściwe</h2>
        <p>Regulamin podlega prawu polskiemu. Spory rozstrzygane są polubownie lub przez właściwy sąd polski.</p>
    </div>
</div>
<script src="/assets/js/dark-mode.js"></script>
</body>
</html>