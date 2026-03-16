<?php

class AdminStats {
    public static function getStats() {
        $db = new Database();
        $query = "
            SELECT 
                (SELECT COUNT(*) FROM users WHERE active = TRUE) as total_users,
                (SELECT COUNT(*) FROM vehicles) as total_vehicles,
                (SELECT COUNT(*) FROM vehicles WHERE status = 'available') as available_vehicles,
                (SELECT COUNT(*) FROM vehicles WHERE status = 'in_use') as in_use_vehicles,
                (SELECT COUNT(*) FROM vehicles WHERE status = 'maintenance') as maintenance_vehicles,
                (SELECT COUNT(*) FROM vehicles WHERE status = 'broken') as broken_vehicles,
                (SELECT COUNT(*) FROM lines WHERE active = TRUE) as total_lines,
                (SELECT COUNT(*) FROM incidents) as total_incidents,
                (SELECT COUNT(*) FROM incidents WHERE status = 'open') as open_incidents,
                (SELECT COUNT(*) FROM incidents WHERE status = 'in_progress') as in_progress_incidents,
                (SELECT COUNT(*) FROM incidents WHERE status = 'resolved') as resolved_incidents
        ";

        return $db->queryOne($query);
    }

    public static function getSlaChecks(): array {
        $checks = [];

        $db_ms = null;
        $db_ok = true;
        $db_error = null;
        $db = new Database();

        $start = microtime(true);
        try {
            $db->queryOne('SELECT 1 AS ok');
            $db_ms = (microtime(true) - $start) * 1000;
        } catch (Throwable $e) {
            $db_ok = false;
            $db_error = $e->getMessage();
            $db_ms = (microtime(true) - $start) * 1000;
        }

        $checks[] = [
            'key' => 'response_db',
            'label' => 'Czas odpowiedzi DB',
            'target' => '< ' . (int)SLA_MAX_RESPONSE_MS . ' ms',
            'value' => $db_ms !== null ? number_format($db_ms, 2, '.', '') . ' ms' : 'brak danych',
            'status' => $db_ok && $db_ms < SLA_MAX_RESPONSE_MS ? 'ok' : 'fail',
            'details' => $db_ok ? 'Pomiar zapytania kontrolnego SELECT 1.' : ('Blad DB: ' . $db_error)
        ];

        $https_enabled = false;
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $https_enabled = true;
        } elseif (defined('BASE_URL') && parse_url(BASE_URL, PHP_URL_SCHEME) === 'https') {
            $https_enabled = true;
        }

        $checks[] = [
            'key' => 'ssl_https',
            'label' => 'SSL / HTTPS',
            'target' => 'Wlaczone HTTPS',
            'value' => $https_enabled ? 'HTTPS aktywne' : 'HTTPS niewykryte',
            'status' => $https_enabled ? 'ok' : 'fail',
            'details' => 'Wymaganie US-028: bezpieczny dostep szyfrowany.'
        ];

        $checks[] = [
            'key' => 'uptime_target',
            'label' => 'Cel dostepnosci',
            'target' => '>= ' . number_format((float)SLA_UPTIME_TARGET_PERCENT, 1, '.', '') . '%',
            'value' => number_format((float)SLA_UPTIME_TARGET_PERCENT, 1, '.', '') . '% (cel)',
            'status' => 'warn',
            'details' => 'Weryfikuj z zewnetrznym monitoringiem (np. UptimeRobot) przez endpoint /health.php.'
        ];

        $checks[] = [
            'key' => 'monitoring_endpoint',
            'label' => 'Endpoint monitoringu',
            'target' => 'Dostepny /health.php',
            'value' => is_file(BASE_PATH . '/health.php') ? 'Skonfigurowany' : 'Brak',
            'status' => is_file(BASE_PATH . '/health.php') ? 'ok' : 'fail',
            'details' => 'Endpoint przeznaczony pod monitoring 24/7.'
        ];

        return $checks;
    }
}
