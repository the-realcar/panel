<?php
http_response_code(500);
?>
<!DOCTYPE html>
<html lang="pl" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="description" content="500 - Blad serwera">
    <title>500 - Blad serwera</title>
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
                        <p class="error-code text-danger">500</p>
                        <h1 class="error-title">Wewnetrzny blad serwera</h1>
                        <p class="error-description">
                            Wystapil nieoczekiwany problem po stronie serwera. Sprobuj ponownie za chwile.
                        </p>
                        <div class="error-actions">
                            <a href="/" class="btn btn-primary">Strona glowna</a>
                            <a href="javascript:location.reload()" class="btn btn-outline">Odswiez strone</a>
                        </div>
                        <p class="error-meta mb-0">Kod bledu HTTP: 500</p>
                    </div>
                </section>
            </div>
        </main>
    </div>
    <script src="/assets/js/dark-mode.js"></script>
</body>
</html>
