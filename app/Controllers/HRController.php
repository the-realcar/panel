<?php

class HRController extends Controller {
    private function ensureAccess(): void {
        requireLogin();

        $rbac = new RBAC();
        if (!$rbac->hasRole('Kadry') && !$rbac->isAdmin()) {
            setFlashMessage('error', 'Brak dostepu do panelu kadr.');
            $this->redirectTo('/index.php');
        }
    }

    public function dashboard() {
        $this->ensureAccess();

        $month = $_GET['month'] ?? date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }

        $summary = WorkHour::listMonthlySummary($month);
        $total_hours = 0.0;
        foreach ($summary as $row) {
            $total_hours += (float)$row['total_hours'];
        }

        $this->render('hr/dashboard', [
            'page_title' => 'Panel Kadr',
            'month' => $month,
            'summary' => $summary,
            'total_hours' => $total_hours
        ]);
    }

    public function workHours() {
        $this->ensureAccess();

        $month = $_GET['month'] ?? date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }

        $drivers = WorkHour::listDriverUsers();
        $selected_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

        if ($selected_user_id <= 0 && !empty($drivers)) {
            $selected_user_id = (int)$drivers[0]['id'];
        }

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/hr/work-hours.php?month=' . urlencode($month) . '&user_id=' . $selected_user_id);
            }

            $action = $_POST['action'] ?? 'save';

            if ($action === 'delete') {
                $entry_id = (int)($_POST['entry_id'] ?? 0);
                $old = WorkHour::findById($entry_id);

                if (!$old) {
                    setFlashMessage('error', 'Nie znaleziono wpisu ECP do usuniecia.');
                } else {
                    WorkHour::deleteById($entry_id);
                    AuditLog::log('work_hours.delete', 'work_hours', $entry_id, [
                        'user_id' => $old['user_id'],
                        'work_date' => $old['work_date'],
                        'hours_worked' => $old['hours_worked']
                    ], null);
                    setFlashMessage('success', 'Wpis ECP zostal usuniety.');
                }

                $this->redirectTo('/hr/work-hours.php?month=' . urlencode($month) . '&user_id=' . $selected_user_id);
            }

            $user_id = (int)($_POST['user_id'] ?? 0);
            $work_date = trim($_POST['work_date'] ?? '');
            $hours_raw = str_replace(',', '.', trim($_POST['hours_worked'] ?? ''));
            $notes = trim($_POST['notes'] ?? '');

            if ($user_id <= 0) {
                $errors['user_id'] = 'Wybierz kierowce.';
            }

            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $work_date)) {
                $errors['work_date'] = 'Podaj poprawna date pracy.';
            }

            if (!is_numeric($hours_raw)) {
                $errors['hours_worked'] = 'Liczba godzin musi byc liczba.';
            } else {
                $hours_worked = (float)$hours_raw;
                if ($hours_worked < 0 || $hours_worked > 24) {
                    $errors['hours_worked'] = 'Liczba godzin musi byc z zakresu 0-24.';
                }
            }

            if (empty($errors)) {
                $existing_id = null;
                foreach (WorkHour::listEntriesForUserMonth($user_id, substr($work_date, 0, 7)) as $entry) {
                    if ((string)$entry['work_date'] === $work_date) {
                        $existing_id = (int)$entry['id'];
                        break;
                    }
                }

                $record_id = WorkHour::upsertEntry([
                    'user_id' => $user_id,
                    'work_date' => $work_date,
                    'hours_worked' => $hours_worked,
                    'notes' => $notes,
                    'source' => 'manual',
                    'updated_by' => getCurrentUserId()
                ]);

                AuditLog::log(
                    $existing_id ? 'work_hours.update' : 'work_hours.create',
                    'work_hours',
                    $record_id,
                    null,
                    [
                        'user_id' => $user_id,
                        'work_date' => $work_date,
                        'hours_worked' => $hours_worked
                    ]
                );

                setFlashMessage('success', $existing_id ? 'Wpis ECP zostal zaktualizowany.' : 'Wpis ECP zostal dodany.');
                $this->redirectTo('/hr/work-hours.php?month=' . urlencode(substr($work_date, 0, 7)) . '&user_id=' . $user_id);
            }

            $selected_user_id = $user_id > 0 ? $user_id : $selected_user_id;
        }

        $entries = $selected_user_id > 0 ? WorkHour::listEntriesForUserMonth($selected_user_id, $month) : [];
        $monthly_total = $selected_user_id > 0 ? WorkHour::getMonthlyTotalForUser($selected_user_id, $month) : 0;

        $this->render('hr/work-hours', [
            'page_title' => 'Ewidencja czasu pracy',
            'drivers' => $drivers,
            'selected_user_id' => $selected_user_id,
            'month' => $month,
            'entries' => $entries,
            'monthly_total' => $monthly_total,
            'errors' => $errors
        ]);
    }

    public function monthlyReport() {
        $this->ensureAccess();

        $month = $_GET['month'] ?? date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }

        $user_id = (int)($_GET['user_id'] ?? 0);
        if ($user_id <= 0) {
            setFlashMessage('error', 'Wybierz pracownika do raportu miesiecznego.');
            $this->redirectTo('/hr/work-hours.php?month=' . urlencode($month));
        }

        $report = $this->buildMonthlyReportData($user_id, $month);
        if ($report === null) {
            setFlashMessage('error', 'Nie znaleziono pracownika dla raportu.');
            $this->redirectTo('/hr/work-hours.php?month=' . urlencode($month));
        }

        $this->render('hr/monthly-report', [
            'page_title' => 'Raport miesieczny ECP',
            'month' => $month,
            'user' => $report['user'],
            'work_hours' => $report['work_hours'],
            'schedule_stats' => $report['schedule_stats'],
            'route_stats' => $report['route_stats'],
            'incident_stats' => $report['incident_stats'],
            'entries' => $report['entries'],
            'generated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function exportMonthlyReport() {
        $this->ensureAccess();

        $month = $_GET['month'] ?? date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }

        $user_id = (int)($_GET['user_id'] ?? 0);
        $format = strtolower(trim((string)($_GET['format'] ?? 'csv')));
        if (!in_array($format, ['csv', 'pdf'], true)) {
            $format = 'csv';
        }

        $report = $this->buildMonthlyReportData($user_id, $month);
        if ($report === null) {
            setFlashMessage('error', 'Nie mozna wygenerowac eksportu dla wybranego pracownika.');
            $this->redirectTo('/hr/work-hours.php?month=' . urlencode($month));
        }

        if ($format === 'csv') {
            $filename = 'raport-ecp-' . $report['user']['username'] . '-' . $month . '.csv';
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            echo "user_id,username,miesiac,work_date,hours_worked,notes\n";
            foreach ($report['entries'] as $entry) {
                $row = [
                    (string)$report['user']['id'],
                    (string)$report['user']['username'],
                    $month,
                    (string)$entry['work_date'],
                    number_format((float)$entry['hours_worked'], 2, '.', ''),
                    str_replace(["\r", "\n", '"'], [' ', ' ', '""'], (string)($entry['notes'] ?? ''))
                ];

                echo '"' . implode('","', $row) . "\"\n";
            }
            exit;
        }

        $filename = 'raport-ecp-' . $report['user']['username'] . '-' . $month . '.pdf';
        $pdf_lines = [
            'Raport miesieczny ECP',
            'Pracownik: ' . trim(($report['user']['first_name'] ?? '') . ' ' . ($report['user']['last_name'] ?? '')) . ' (' . $report['user']['username'] . ')',
            'Miesiac: ' . $month,
            'Suma godzin: ' . number_format((float)($report['work_hours']['total_hours'] ?? 0), 2, '.', ''),
            'Liczba dni: ' . (int)($report['work_hours']['days_count'] ?? 0),
            'Liczba sluzb: ' . (int)($report['schedule_stats']['shifts_count'] ?? 0),
            'Wykonane sluzby: ' . (int)($report['schedule_stats']['completed_shifts'] ?? 0),
            'Karty drogowe: ' . (int)($report['route_stats']['route_cards_count'] ?? 0),
            'Pasazerowie: ' . (int)($report['route_stats']['passengers_total'] ?? 0),
            'Incydenty: ' . (int)($report['incident_stats']['incidents_count'] ?? 0),
            '--- Szczegoly ECP ---'
        ];

        foreach ($report['entries'] as $entry) {
            $pdf_lines[] = (string)$entry['work_date'] . ' | ' . number_format((float)$entry['hours_worked'], 2, '.', '') . 'h | ' . trim((string)($entry['notes'] ?? ''));
        }

        $pdf_content = $this->buildSimplePdf($pdf_lines);
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $pdf_content;
        exit;
    }

    private function buildMonthlyReportData(int $user_id, string $month): ?array {
        if ($user_id <= 0) {
            return null;
        }

        $user = User::find($user_id);
        if (!$user) {
            return null;
        }

        $db = new Database();
        $work_hours = $db->queryOne(
            "
            SELECT COALESCE(SUM(hours_worked), 0) AS total_hours,
                   COUNT(*) AS days_count
            FROM work_hours
            WHERE user_id = :user_id
              AND TO_CHAR(work_date, 'YYYY-MM') = :month
            ",
            [':user_id' => $user_id, ':month' => $month]
        );

        $schedule_stats = $db->queryOne(
            "
            SELECT COUNT(*) AS shifts_count,
                   COUNT(*) FILTER (WHERE status = 'completed') AS completed_shifts
            FROM schedules
            WHERE user_id = :user_id
              AND TO_CHAR(schedule_date, 'YYYY-MM') = :month
            ",
            [':user_id' => $user_id, ':month' => $month]
        );

        $route_stats = $db->queryOne(
            "
            SELECT COUNT(*) AS route_cards_count,
                   COALESCE(SUM(passengers_count), 0) AS passengers_total
            FROM route_cards
            WHERE user_id = :user_id
              AND TO_CHAR(route_date, 'YYYY-MM') = :month
            ",
            [':user_id' => $user_id, ':month' => $month]
        );

        $incident_stats = $db->queryOne(
            "
            SELECT COUNT(*) AS incidents_count
            FROM incidents
            WHERE reported_by = :user_id
              AND TO_CHAR(incident_date, 'YYYY-MM') = :month
            ",
            [':user_id' => $user_id, ':month' => $month]
        );

        $entries = WorkHour::listEntriesForUserMonth($user_id, $month);

        return [
            'user' => $user,
            'work_hours' => $work_hours,
            'schedule_stats' => $schedule_stats,
            'route_stats' => $route_stats,
            'incident_stats' => $incident_stats,
            'entries' => $entries
        ];
    }

    private function buildSimplePdf(array $lines): string {
        $safe_lines = [];
        foreach ($lines as $line) {
            $text = (string)$line;
            $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
            if ($ascii === false) {
                $ascii = $text;
            }

            $ascii = preg_replace('/[^\x20-\x7E]/', ' ', $ascii);
            $ascii = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $ascii);
            $safe_lines[] = $ascii;
        }

        $stream_lines = [
            'BT',
            '/F1 11 Tf',
            '14 TL',
            '50 800 Td'
        ];

        foreach ($safe_lines as $index => $line) {
            if ($index > 0) {
                $stream_lines[] = 'T*';
            }
            $stream_lines[] = '(' . $line . ') Tj';
        }
        $stream_lines[] = 'ET';

        $stream = implode("\n", $stream_lines) . "\n";

        $objects = [];
        $objects[] = '1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n';
        $objects[] = '2 0 obj\n<< /Type /Pages /Count 1 /Kids [3 0 R] >>\nendobj\n';
        $objects[] = '3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n';
        $objects[] = '4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n';
        $objects[] = '5 0 obj\n<< /Length ' . strlen($stream) . ' >>\nstream\n' . $stream . 'endstream\nendobj\n';

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }

        $xref_position = strlen($pdf);
        $total_objects = count($objects) + 1;

        $pdf .= "xref\n0 {$total_objects}\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i < $total_objects; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer\n<< /Size {$total_objects} /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xref_position}\n%%EOF";

        return $pdf;
    }
}
