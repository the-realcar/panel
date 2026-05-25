<?php
http_response_code(403);
$default_message = 'Nie masz uprawnien do wykonania tej operacji.';
$message = isset($error_message) && is_string($error_message) && trim($error_message) !== ''
    ? $error_message
    : $default_message;
?>
<!DOCTYPE html>
<html lang="pl" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="description" content="403 - Brak dostepu">
    <title>403 - Brak dostepu</title>
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
                        <p class="error-code text-danger">403</p>
                        <h1 class="error-title">Brak dostepu</h1>
                        <p class="error-description"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
                        <div class="error-actions">
                            <a href="javascript:history.back()" class="btn btn-outline">Wroc</a>
                        </div>
                        <p class="error-meta mb-0">Kod bledu HTTP: 403</p>
                    </div>
                </section>
            </div>
        </main>
    </div>
    <script src="/assets/js/dark-mode.js"></script>
</body>
</html>
