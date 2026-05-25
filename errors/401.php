<?php
http_response_code(401);
?>
<!DOCTYPE html>
<html lang="pl" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="description" content="401 - Brak autoryzacji">
    <title>401 - Brak autoryzacji</title>
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
                        <p class="error-code text-warning">401</p>
                        <h1 class="error-title">Brak autoryzacji</h1>
                        <p class="error-description">
                            Aby zobaczyc ten zasob, musisz byc zalogowany i posiadac wazna sesje.
                        </p>
                        <div class="error-actions">
                            <a href="/" class="btn btn-outline">Powrót do strony glownej</a>
                        </div>
                        <p class="error-meta mb-0">Kod bledu HTTP: 401</p>
                    </div>
                </section>
            </div>
        </main>
    </div>
    <script src="/assets/js/dark-mode.js"></script>
</body>
</html>
