<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="pl" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="description" content="404 - Nie znaleziono strony">
    <title>404 - Nie znaleziono strony</title>
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="/assets/css/mobile.css">
    <link rel="stylesheet" href="/assets/css/dark-mode.css">
</head>
<body>
    <div class="wrapper">
        <main class="main-content">
            <div class="container">
                <section class="error-page">
                    <div class="card error-card">
                        <p class="error-code text-primary">404</p>
                        <h1 class="error-title">Nie znaleziono strony</h1>
                        <p class="error-description">
                            Adres, ktory probujesz otworzyc, nie istnieje lub zostal przeniesiony.
                        </p>
                        <div class="error-actions">
                            <a href="/" class="btn btn-primary">Powrót do strony gloweja</a>
                        </div>
                        <p class="error-meta mb-0">Kod bledu HTTP: 404</p>
                    </div>
                </section>
            </div>
        </main>
    </div>
    <script src="/assets/js/dark-mode.js"></script>
</body>
</html>
