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

        $report = $this->buildMonthlyPositionsReportData($month);

        $this->render('hr/monthly-report', [
            'page_title' => 'Raport miesieczny stanowisk i spolek',
            'month' => $month,
            'rows' => $report['rows'],
            'totals' => $report['totals'],
            'generated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function exportMonthlyReport() {
        $this->ensureAccess();

        $month = $_GET['month'] ?? date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }

        $format = strtolower(trim((string)($_GET['format'] ?? 'csv')));
        if (!in_array($format, ['csv', 'pdf'], true)) {
            $format = 'csv';
        }

        $report = $this->buildMonthlyPositionsReportData($month);

        if ($format === 'csv') {
            $filename = 'raport-stanowiska-spolki-' . $month . '.csv';
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            echo "miesiac,spolka,stanowisko,pracownicy,sluzby_wykonane,karty_drogowe,kursy_wykonane,obsluzone_przystanki,pasazerowie,incydenty\n";
            foreach ($report['rows'] as $row) {
                $row = [
                    $month,
                    (string)$row['company_name'],
                    (string)$row['position_name'],
                    (string)(int)$row['active_people'],
                    (string)(int)$row['completed_shifts'],
                    (string)(int)$row['route_cards_count'],
                    (string)(int)$row['executed_courses'],
                    (string)(int)$row['served_stops'],
                    (string)(int)$row['passengers_total'],
                    (string)(int)$row['incidents_count']
                ];

                echo '"' . implode('","', $row) . "\"\n";
            }
            exit;
        }

        $filename = 'raport-stanowiska-spolki-' . $month . '.pdf';
        $pdf_lines = [
            'Raport miesieczny stanowisk i spolek',
            'Miesiac: ' . $month,
            'Suma sluzb wykonanych: ' . (int)($report['totals']['completed_shifts'] ?? 0),
            'Suma kart drogowych: ' . (int)($report['totals']['route_cards_count'] ?? 0),
            'Suma kursow: ' . (int)($report['totals']['executed_courses'] ?? 0),
            'Suma obsluzonych przystankow: ' . (int)($report['totals']['served_stops'] ?? 0),
            'Suma pasazerow: ' . (int)($report['totals']['passengers_total'] ?? 0),
            'Suma incydentow: ' . (int)($report['totals']['incidents_count'] ?? 0),
            '--- Szczegoly ---'
        ];

        foreach ($report['rows'] as $row) {
            $pdf_lines[] =
                $row['company_name'] . ' | ' .
                $row['position_name'] . ' | prac.: ' . (int)$row['active_people'] .
                ' | sluzby: ' . (int)$row['completed_shifts'] .
                ' | karty: ' . (int)$row['route_cards_count'] .
                ' | kursy: ' . (int)$row['executed_courses'] .
                ' | przystanki: ' . (int)$row['served_stops'] .
                ' | pasazerowie: ' . (int)$row['passengers_total'] .
                ' | incydenty: ' . (int)$row['incidents_count'];
        }

        $pdf_content = $this->buildSimplePdf($pdf_lines);
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $pdf_content;
        exit;
    }

    private function buildMonthlyPositionsReportData(string $month): array {
        $db = new Database();

        $rows = $db->query(
            "
            WITH position_users AS (
                SELECT
                    up.user_id,
                    p.id AS position_id,
                    p.name AS position_name,
                    CASE
                        WHEN p.name ILIKE '%(Spółka)%' OR p.name ILIKE '%Spolki%' OR p.name ILIKE '%Spółki%' THEN 'Spolki'
                        ELSE 'Firma KOT'
                    END AS position_group
                FROM user_positions up
                INNER JOIN positions p ON p.id = up.position_id
                INNER JOIN users u ON u.id = up.user_id
                WHERE up.active = TRUE
                  AND p.active = TRUE
                  AND u.active = TRUE
            ),
            variant_stop_counts AS (
                SELECT route_variant_id, COUNT(*)::int AS stop_count
                FROM route_stops
                GROUP BY route_variant_id
            ),
            activity_rows AS (
                SELECT
                    s.user_id,
                    COALESCE(v.przewoznik, 'Nieprzypisano') AS company_name,
                    COUNT(*) FILTER (WHERE s.status = 'completed')::int AS completed_shifts,
                    0::int AS route_cards_count,
                    0::int AS executed_courses,
                    0::int AS served_stops,
                    0::int AS passengers_total,
                    0::int AS incidents_count
                FROM schedules s
                LEFT JOIN vehicles v ON v.id = s.vehicle_id
                WHERE TO_CHAR(s.schedule_date, 'YYYY-MM') = :month
                GROUP BY s.user_id, COALESCE(v.przewoznik, 'Nieprzypisano')

                UNION ALL

                SELECT
                    rc.user_id,
                    COALESCE(v.przewoznik, 'Nieprzypisano') AS company_name,
                    0::int AS completed_shifts,
                    COUNT(*)::int AS route_cards_count,
                    0::int AS executed_courses,
                    0::int AS served_stops,
                    COALESCE(SUM(rc.passengers_count), 0)::int AS passengers_total,
                    0::int AS incidents_count
                FROM route_cards rc
                LEFT JOIN vehicles v ON v.id = rc.vehicle_id
                WHERE TO_CHAR(rc.route_date, 'YYYY-MM') = :month
                GROUP BY rc.user_id, COALESCE(v.przewoznik, 'Nieprzypisano')

                UNION ALL

                SELECT
                    rc.user_id,
                    COALESCE(v.przewoznik, 'Nieprzypisano') AS company_name,
                    0::int AS completed_shifts,
                    0::int AS route_cards_count,
                    COALESCE(SUM(rct.trips_count), 0)::int AS executed_courses,
                    COALESCE(SUM(rct.trips_count * COALESCE(vsc.stop_count, 0)), 0)::int AS served_stops,
                    0::int AS passengers_total,
                    0::int AS incidents_count
                FROM route_card_trips rct
                INNER JOIN route_cards rc ON rc.id = rct.route_card_id
                LEFT JOIN vehicles v ON v.id = rc.vehicle_id
                LEFT JOIN variant_stop_counts vsc ON vsc.route_variant_id = rct.route_variant_id
                WHERE TO_CHAR(rc.route_date, 'YYYY-MM') = :month
                GROUP BY rc.user_id, COALESCE(v.przewoznik, 'Nieprzypisano')

                UNION ALL

                SELECT
                    i.reported_by AS user_id,
                    COALESCE(v.przewoznik, 'Nieprzypisano') AS company_name,
                    0::int AS completed_shifts,
                    0::int AS route_cards_count,
                    0::int AS executed_courses,
                    0::int AS served_stops,
                    0::int AS passengers_total,
                    COUNT(*)::int AS incidents_count
                FROM incidents i
                LEFT JOIN vehicles v ON v.id = i.vehicle_id
                WHERE TO_CHAR(i.incident_date, 'YYYY-MM') = :month
                  AND i.reported_by IS NOT NULL
                GROUP BY i.reported_by, COALESCE(v.przewoznik, 'Nieprzypisano')
            ),
            user_company_activity AS (
                SELECT
                    user_id,
                    company_name,
                    SUM(completed_shifts)::int AS completed_shifts,
                    SUM(route_cards_count)::int AS route_cards_count,
                    SUM(executed_courses)::int AS executed_courses,
                    SUM(served_stops)::int AS served_stops,
                    SUM(passengers_total)::int AS passengers_total,
                    SUM(incidents_count)::int AS incidents_count
                FROM activity_rows
                GROUP BY user_id, company_name
            )
            SELECT
                CASE
                    WHEN pu.position_group = 'Firma KOT' THEN 'Firma KOT'
                    ELSE uca.company_name
                END AS company_name,
                pu.position_name,
                COUNT(DISTINCT pu.user_id)::int AS assigned_people,
                COUNT(DISTINCT uca.user_id)::int AS active_people,
                COALESCE(SUM(uca.completed_shifts), 0)::int AS completed_shifts,
                COALESCE(SUM(uca.route_cards_count), 0)::int AS route_cards_count,
                COALESCE(SUM(uca.executed_courses), 0)::int AS executed_courses,
                COALESCE(SUM(uca.served_stops), 0)::int AS served_stops,
                COALESCE(SUM(uca.passengers_total), 0)::int AS passengers_total,
                COALESCE(SUM(uca.incidents_count), 0)::int AS incidents_count
            FROM position_users pu
            LEFT JOIN user_company_activity uca ON uca.user_id = pu.user_id
            GROUP BY
                CASE
                    WHEN pu.position_group = 'Firma KOT' THEN 'Firma KOT'
                    ELSE uca.company_name
                END,
                pu.position_name
            ORDER BY 1 ASC NULLS LAST, 2 ASC
            ",
            [':month' => $month]
        );

        $clean_rows = array_values(array_filter($rows, function ($row) {
            return !empty($row['company_name']);
        }));

        $totals = [
            'active_people' => 0,
            'completed_shifts' => 0,
            'route_cards_count' => 0,
            'executed_courses' => 0,
            'served_stops' => 0,
            'passengers_total' => 0,
            'incidents_count' => 0
        ];

        foreach ($clean_rows as $row) {
            $totals['active_people'] += (int)$row['active_people'];
            $totals['completed_shifts'] += (int)$row['completed_shifts'];
            $totals['route_cards_count'] += (int)$row['route_cards_count'];
            $totals['executed_courses'] += (int)$row['executed_courses'];
            $totals['served_stops'] += (int)$row['served_stops'];
            $totals['passengers_total'] += (int)$row['passengers_total'];
            $totals['incidents_count'] += (int)$row['incidents_count'];
        }

        return [
            'rows' => $clean_rows,
            'totals' => $totals
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
