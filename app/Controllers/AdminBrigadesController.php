<?php

class AdminBrigadesController extends Controller {
    private function normalizeDepartureFormData(array &$form_data) {
        $times = $form_data['departure_time'] ?? [];
        $directions = $form_data['departure_direction'] ?? [];

        if (!is_array($times)) {
            $times = [];
        }
        if (!is_array($directions)) {
            $directions = [];
        }

        $row_count = max(count($times), count($directions), 1);
        $normalized_times = [];
        $normalized_directions = [];

        for ($i = 0; $i < $row_count; $i++) {
            $normalized_times[] = trim((string)($times[$i] ?? ''));
            $normalized_directions[] = trim((string)($directions[$i] ?? ''));
        }

        $form_data['departure_time'] = $normalized_times;
        $form_data['departure_direction'] = $normalized_directions;
    }

    private function parseDepartures(array $form_data, array &$errors) {
        $times = $form_data['departure_time'] ?? [];
        $directions = $form_data['departure_direction'] ?? [];

        if (!is_array($times)) {
            $times = [];
        }
        if (!is_array($directions)) {
            $directions = [];
        }

        $row_count = max(count($times), count($directions));
        $departures = [];
        $seen = [];

        for ($i = 0; $i < $row_count; $i++) {
            $time = trim((string)($times[$i] ?? ''));
            $direction = trim((string)($directions[$i] ?? ''));

            if ($time === '' && $direction === '') {
                continue;
            }

            if ($time === '' || $direction === '') {
                $errors['departures'] = 'Kazdy odjazd musi miec godzine oraz kierunek.';
                continue;
            }

            if (!preg_match('/^(2[0-3]|[01][0-9]):[0-5][0-9]$/', $time)) {
                $errors['departures'] = 'Godziny odjazdu musza byc w formacie HH:MM.';
                continue;
            }

            if (mb_strlen($direction) > 120) {
                $errors['departures'] = 'Kierunek nie moze przekraczac 120 znakow.';
                continue;
            }

            $dedupe_key = $time . '|' . mb_strtolower($direction);
            if (isset($seen[$dedupe_key])) {
                $errors['departures'] = 'Wykryto powtorzone odjazdy z tym samym kierunkiem.';
                continue;
            }

            $seen[$dedupe_key] = true;
            $departures[] = [
                'departure_time' => $time,
                'direction' => $direction
            ];
        }

        return $departures;
    }

    public function index() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('brigades', 'read');

        $line_filter = isset($_GET['line']) ? (int)$_GET['line'] : null;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $per_page = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $per_page;

        $total_items = Brigade::countByLine($line_filter);
        $total_pages = (int)ceil($total_items / $per_page);
        $brigades = Brigade::listAll($per_page, $offset, false, $line_filter);

        $lines = Line::listActive();

        $this->render('admin/brigades/index', [
            'page_title' => 'Zarzadzanie brygadami',
            'brigades' => $brigades,
            'lines' => $lines,
            'line_filter' => $line_filter,
            'page' => $page,
            'total_pages' => $total_pages,
            'rbac' => $rbac
        ]);
    }

    public function create() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('brigades', 'create');

        $errors = [];
        $form_data = [
            'active' => 'on',
            'is_peak' => '',
            'peak_type' => '',
            'departure_time' => [''],
            'departure_direction' => ['']
        ];
        $lines = Line::listActive();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/admin/brigades/create.php');
            }

            $form_data = $_POST;
            $this->normalizeDepartureFormData($form_data);

            $validator = new Validator($form_data);
            $validator->required('line_id', 'Linia jest wymagana.')
                      ->required('brigade_number', 'Numer brygady jest wymagany.');

            if ($validator->fails()) {
                $errors = $validator->getErrors();
            }

            $form_data['brigade_number'] = Brigade::normalizeBrigadeNumber($form_data['brigade_number'] ?? '');

            if (empty($errors['line_id']) && empty($errors['brigade_number'])) {
                if (Brigade::exists($form_data['line_id'], $form_data['brigade_number'])) {
                    $errors['brigade_number'] = 'Brygada o tym numerze juz istnieje dla tej linii.';
                }
            }

            if (!empty($form_data['is_peak'])) {
                $allowed_peak_types = ['peak', 'single_shift'];
                if (empty($form_data['peak_type'])) {
                    $errors['peak_type'] = 'Wybierz typ brygady szczytowej.';
                } elseif (!in_array($form_data['peak_type'], $allowed_peak_types, true)) {
                    $errors['peak_type'] = 'Wybrano nieprawidlowy typ brygady szczytowej.';
                }
            }

            $departures = $this->parseDepartures($form_data, $errors);

            $allowed_przewoznicy = ['Ostrans', 'KujaTrans', 'Ostromunikacja'];
            if (!empty($form_data['przewoznik']) && !in_array($form_data['przewoznik'], $allowed_przewoznicy, true)) {
                $errors['przewoznik'] = 'Wybrano nieprawidlowa spolke.';
            }

            if (empty($errors)) {
                try {
                    $new_brigade_id = Brigade::create([
                        'line_id' => $form_data['line_id'],
                        'brigade_number' => $form_data['brigade_number'],
                        'is_peak' => !empty($form_data['is_peak']) ? 'true' : 'false',
                        'peak_type' => !empty($form_data['is_peak']) ? ($form_data['peak_type'] ?? null) : null,
                        'shift_a_start' => !empty($form_data['shift_a_start']) ? $form_data['shift_a_start'] : null,
                        'shift_a_end'   => !empty($form_data['shift_a_end'])   ? $form_data['shift_a_end']   : null,
                        'shift_b_start' => !empty($form_data['shift_b_start']) ? $form_data['shift_b_start'] : null,
                        'shift_b_end'   => !empty($form_data['shift_b_end'])   ? $form_data['shift_b_end']   : null,
                        'shift_a_first_stop' => !empty($form_data['shift_a_first_stop']) ? $form_data['shift_a_first_stop'] : null,
                        'shift_a_last_stop'  => !empty($form_data['shift_a_last_stop'])  ? $form_data['shift_a_last_stop']  : null,
                        'shift_a_capacity'   => !empty($form_data['shift_a_capacity'])   ? $form_data['shift_a_capacity']   : null,
                        'shift_b_first_stop' => !empty($form_data['shift_b_first_stop']) ? $form_data['shift_b_first_stop'] : null,
                        'shift_b_last_stop'  => !empty($form_data['shift_b_last_stop'])  ? $form_data['shift_b_last_stop']  : null,
                        'shift_b_capacity'   => !empty($form_data['shift_b_capacity'])   ? $form_data['shift_b_capacity']   : null,
                        'default_vehicle_type' => !empty($form_data['default_vehicle_type']) ? $form_data['default_vehicle_type'] : null,
                        'przewoznik' => !empty($form_data['przewoznik']) ? $form_data['przewoznik'] : null,
                        'description' => !empty($form_data['description']) ? $form_data['description'] : null,
                        'active' => isset($form_data['active']) ? 'true' : 'false'
                    ]);
                    Brigade::replaceDepartures($new_brigade_id, $departures);
                    AuditLog::log('brigade.create', 'brigades', $new_brigade_id, null, ['line_id' => $form_data['line_id'], 'brigade_number' => $form_data['brigade_number']]);

                    setFlashMessage('success', 'Brygada zostala dodana pomyslnie.');
                    $this->redirectTo('/admin/brigades/index.php');
                } catch (Exception $e) {
                    error_log('Error creating brigade: ' . $e->getMessage());
                    $errors['general'] = 'Wystapil blad podczas dodawania brygady.';
                }
            }
        }

        $this->normalizeDepartureFormData($form_data);

        $this->render('admin/brigades/create', [
            'page_title' => 'Dodaj brygade',
            'errors' => $errors,
            'form_data' => $form_data,
            'lines' => $lines
        ]);
    }

    public function edit() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('brigades', 'update');

        $brigade_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$brigade_id) {
            setFlashMessage('error', 'Nieprawidlowy ID brygady.');
            $this->redirectTo('/admin/brigades/index.php');
        }

        $brigade = Brigade::find($brigade_id);
        if (!$brigade) {
            setFlashMessage('error', 'Brygada nie zostala znaleziona.');
            $this->redirectTo('/admin/brigades/index.php');
        }

        $errors = [];
        $form_data = $brigade;
        $lines = Line::listActive();

        $existing_departures = Brigade::listDepartures($brigade_id);
        $form_data['departure_time'] = [];
        $form_data['departure_direction'] = [];
        foreach ($existing_departures as $departure) {
            $form_data['departure_time'][] = substr((string)$departure['departure_time'], 0, 5);
            $form_data['departure_direction'][] = (string)$departure['direction'];
        }
        $this->normalizeDepartureFormData($form_data);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/admin/brigades/edit.php?id=' . $brigade_id);
            }

            $form_data = array_merge($brigade, $_POST);
            $this->normalizeDepartureFormData($form_data);

            $validator = new Validator($form_data);
            $validator->required('line_id', 'Linia jest wymagana.')
                      ->required('brigade_number', 'Numer brygady jest wymagany.');

            if ($validator->fails()) {
                $errors = $validator->getErrors();
            }

            $form_data['brigade_number'] = Brigade::normalizeBrigadeNumber($form_data['brigade_number'] ?? '');

            if (empty($errors['line_id']) && empty($errors['brigade_number'])) {
                if ($form_data['line_id'] != $brigade['line_id'] || $form_data['brigade_number'] != $brigade['brigade_number']) {
                    if (Brigade::exists($form_data['line_id'], $form_data['brigade_number'], $brigade_id)) {
                        $errors['brigade_number'] = 'Brygada o tym numerze juz istnieje dla tej linii.';
                    }
                }
            }

            if (!empty($form_data['is_peak'])) {
                $allowed_peak_types = ['peak', 'single_shift'];
                if (empty($form_data['peak_type'])) {
                    $errors['peak_type'] = 'Wybierz typ brygady szczytowej.';
                } elseif (!in_array($form_data['peak_type'], $allowed_peak_types, true)) {
                    $errors['peak_type'] = 'Wybrano nieprawidlowy typ brygady szczytowej.';
                }
            }

            $departures = $this->parseDepartures($form_data, $errors);

            $allowed_przewoznicy = ['Ostrans', 'KujaTrans', 'Ostromunikacja'];
            if (!empty($form_data['przewoznik']) && !in_array($form_data['przewoznik'], $allowed_przewoznicy, true)) {
                $errors['przewoznik'] = 'Wybrano nieprawidlowa spolke.';
            }

            if (empty($errors)) {
                try {
                    Brigade::update($brigade_id, [
                        'line_id' => $form_data['line_id'],
                        'brigade_number' => $form_data['brigade_number'],
                        'is_peak' => !empty($form_data['is_peak']) ? 'true' : 'false',
                        'peak_type' => !empty($form_data['is_peak']) ? ($form_data['peak_type'] ?? null) : null,
                        'shift_a_start' => !empty($form_data['shift_a_start']) ? $form_data['shift_a_start'] : null,
                        'shift_a_end'   => !empty($form_data['shift_a_end'])   ? $form_data['shift_a_end']   : null,
                        'shift_b_start' => !empty($form_data['shift_b_start']) ? $form_data['shift_b_start'] : null,
                        'shift_b_end'   => !empty($form_data['shift_b_end'])   ? $form_data['shift_b_end']   : null,
                        'shift_a_first_stop' => !empty($form_data['shift_a_first_stop']) ? $form_data['shift_a_first_stop'] : null,
                        'shift_a_last_stop'  => !empty($form_data['shift_a_last_stop'])  ? $form_data['shift_a_last_stop']  : null,
                        'shift_a_capacity'   => !empty($form_data['shift_a_capacity'])   ? $form_data['shift_a_capacity']   : null,
                        'shift_b_first_stop' => !empty($form_data['shift_b_first_stop']) ? $form_data['shift_b_first_stop'] : null,
                        'shift_b_last_stop'  => !empty($form_data['shift_b_last_stop'])  ? $form_data['shift_b_last_stop']  : null,
                        'shift_b_capacity'   => !empty($form_data['shift_b_capacity'])   ? $form_data['shift_b_capacity']   : null,
                        'default_vehicle_type' => !empty($form_data['default_vehicle_type']) ? $form_data['default_vehicle_type'] : null,
                        'przewoznik' => !empty($form_data['przewoznik']) ? $form_data['przewoznik'] : null,
                        'description' => !empty($form_data['description']) ? $form_data['description'] : null,
                        'active' => isset($form_data['active']) ? 'true' : 'false'
                    ]);
                    Brigade::replaceDepartures($brigade_id, $departures);
                    AuditLog::log('brigade.update', 'brigades', $brigade_id, ['brigade_number' => $brigade['brigade_number'], 'line_id' => $brigade['line_id']], ['brigade_number' => $form_data['brigade_number'], 'line_id' => $form_data['line_id']]);

                    setFlashMessage('success', 'Brygada zostala zaktualizowana pomyslnie.');
                    $this->redirectTo('/admin/brigades/index.php');
                } catch (Exception $e) {
                    error_log('Error updating brigade: ' . $e->getMessage());
                    $errors['general'] = 'Wystapil blad podczas aktualizacji brygady.';
                }
            }
        }

        $this->normalizeDepartureFormData($form_data);

        $this->render('admin/brigades/edit', [
            'page_title' => 'Edytuj brygade',
            'errors' => $errors,
            'form_data' => $form_data,
            'brigade' => $brigade,
            'lines' => $lines
        ]);
    }

    public function schedule() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('brigades', 'read');

        $line_filter = isset($_GET['line']) ? (int)$_GET['line'] : null;
        $lines = Line::listActive();
        $brigades = Brigade::listAll(9999, 0, true, $line_filter);

        $this->render('admin/brigades/schedule', [
            'page_title' => 'Plan zmian brygad',
            'brigades' => $brigades,
            'lines' => $lines,
            'line_filter' => $line_filter
        ]);
    }

    public function delete() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('brigades', 'delete');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            setFlashMessage('error', 'Nieprawidlowe zadanie.');
            $this->redirectTo('/admin/brigades/index.php');
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlashMessage('error', 'Nieprawidlowy token CSRF.');
            $this->redirectTo('/admin/brigades/index.php');
        }

        $brigade_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if (!$brigade_id) {
            setFlashMessage('error', 'Nieprawidlowy ID brygady.');
            $this->redirectTo('/admin/brigades/index.php');
        }

        $brigade = Brigade::find($brigade_id);
        if (!$brigade) {
            setFlashMessage('error', 'Brygada nie zostala znaleziona.');
            $this->redirectTo('/admin/brigades/index.php');
        }

        // Check if brigade is used in schedules
        if (Brigade::isUsedInSchedules($brigade_id)) {
            setFlashMessage('error', 'Nie mozna usunac brygady, ktora jest uzywana w grafikach.');
            $this->redirectTo('/admin/brigades/index.php');
        }

        try {
            Brigade::delete($brigade_id);
            AuditLog::log('brigade.delete', 'brigades', $brigade_id, ['brigade_number' => $brigade['brigade_number'], 'line_id' => $brigade['line_id']], null);
            setFlashMessage('success', 'Brygada zostala usunieta pomyslnie.');
        } catch (Exception $e) {
            error_log('Error deleting brigade: ' . $e->getMessage());
            setFlashMessage('error', 'Wystapil blad podczas usuwania brygady.');
        }

        $this->redirectTo('/admin/brigades/index.php');
    }

    public function generateSchedule() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('brigades', 'read');

        $lines = Line::listActive();
        $line_filter = isset($_GET['line']) ? (int)$_GET['line'] : null;
        $brigades = $line_filter ? Brigade::listByLine($line_filter, true) : [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Brigade::supportsDepartures()) {
                setFlashMessage('error', 'Tabela odjazdów brygad nie jest dostępna w tej bazie danych.');
                $this->redirectTo('/admin/brigades/generate-schedule.php' . ($line_filter ? '?line=' . $line_filter : ''));
            }

            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/admin/brigades/generate-schedule.php');
            }

            $brigade_ids = isset($_POST['brigade_ids']) && is_array($_POST['brigade_ids'])
                ? array_map('intval', $_POST['brigade_ids'])
                : [];

            if (empty($brigade_ids)) {
                setFlashMessage('error', 'Nie wybrano zadnej brygady.');
                $this->redirectTo('/admin/brigades/generate-schedule.php' . ($line_filter ? '?line=' . $line_filter : ''));
            }

            $tmp_file = tempnam(sys_get_temp_dir(), 'rozklad_');
            $zip = new ZipArchive();
            if ($zip->open($tmp_file, ZipArchive::OVERWRITE) !== true) {
                setFlashMessage('error', 'Nie mozna utworzyc archiwum ZIP.');
                $this->redirectTo('/admin/brigades/generate-schedule.php');
            }

            foreach ($brigade_ids as $brigade_id) {
                $brigade = Brigade::find($brigade_id);
                if (!$brigade) {
                    continue;
                }
                $departures = Brigade::listDepartures($brigade_id);
                $line_num = preg_replace('/[^A-Za-z0-9]/', '_', (string)($brigade['line_number'] ?? 'L'));
                $brig_num = preg_replace('/[^A-Za-z0-9]/', '_', (string)($brigade['brigade_number'] ?? '0'));
                $prefix = $line_num . '_' . $brig_num;

                $zip->addFromString($prefix . '.html', $this->buildScheduleHtml($brigade, $departures, 'full'));
                $zip->addFromString($prefix . 'A.html', $this->buildScheduleHtml($brigade, $departures, 'A'));

                $has_b = !empty($brigade['shift_b_start']) && !empty($brigade['shift_b_end']);
                if ($has_b) {
                    $zip->addFromString($prefix . 'B.html', $this->buildScheduleHtml($brigade, $departures, 'B'));
                }
            }

            $zip->close();

            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="rozklady_jazdy.zip"');
            header('Content-Length: ' . filesize($tmp_file));
            header('Cache-Control: no-cache, no-store, must-revalidate');
            readfile($tmp_file);
            unlink($tmp_file);
            exit;
        }

        $this->render('admin/brigades/generate-schedule', [
            'page_title' => 'Generator rozkladow jazdy',
            'lines' => $lines,
            'brigades' => $brigades,
            'line_filter' => $line_filter,
            'departures_available' => Brigade::supportsDepartures()
        ]);
    }

    private function buildScheduleHtml(array $brigade, array $departures, string $shift_type): string {
        $hhmm = function ($t) {
            return $t ? substr((string)$t, 0, 5) : '';
        };

        $line_num    = htmlspecialchars((string)($brigade['line_number']   ?? ''), ENT_QUOTES, 'UTF-8');
        $brig_num    = htmlspecialchars((string)($brigade['brigade_number'] ?? ''), ENT_QUOTES, 'UTF-8');
        $operator    = htmlspecialchars((string)($brigade['przewoznik']     ?? ''), ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars((string)($brigade['description']   ?? ''), ENT_QUOTES, 'UTF-8');

        if ($shift_type === 'A') {
            $shift_label  = 'A';
            $shift_start  = $hhmm($brigade['shift_a_start'] ?? '');
            $shift_end    = $hhmm($brigade['shift_a_end']   ?? '');
            $first_stop   = htmlspecialchars((string)($brigade['shift_a_first_stop'] ?? ''), ENT_QUOTES, 'UTF-8');
            $last_stop    = htmlspecialchars((string)($brigade['shift_a_last_stop']  ?? ''), ENT_QUOTES, 'UTF-8');
            $capacity     = htmlspecialchars((string)($brigade['shift_a_capacity']   ?? ''), ENT_QUOTES, 'UTF-8');
            $filtered = array_values(array_filter($departures, function ($d) use ($shift_start, $shift_end) {
                $t = substr((string)$d['departure_time'], 0, 5);
                return $shift_start && $shift_end && $t >= $shift_start && $t <= $shift_end;
            }));
        } elseif ($shift_type === 'B') {
            $shift_label  = 'B';
            $shift_start  = $hhmm($brigade['shift_b_start'] ?? '');
            $shift_end    = $hhmm($brigade['shift_b_end']   ?? '');
            $first_stop   = htmlspecialchars((string)($brigade['shift_b_first_stop'] ?? ''), ENT_QUOTES, 'UTF-8');
            $last_stop    = htmlspecialchars((string)($brigade['shift_b_last_stop']  ?? ''), ENT_QUOTES, 'UTF-8');
            $capacity     = htmlspecialchars((string)($brigade['shift_b_capacity']   ?? ''), ENT_QUOTES, 'UTF-8');
            $filtered = array_values(array_filter($departures, function ($d) use ($shift_start, $shift_end) {
                $t = substr((string)$d['departure_time'], 0, 5);
                return $shift_start && $shift_end && $t >= $shift_start && $t <= $shift_end;
            }));
        } else {
            $shift_label  = '';
            $shift_start  = $hhmm($brigade['shift_a_start'] ?? '');
            $end_b        = $hhmm($brigade['shift_b_end']   ?? '');
            $end_a        = $hhmm($brigade['shift_a_end']   ?? '');
            $shift_end    = $end_b ?: $end_a;
            $first_stop   = htmlspecialchars((string)($brigade['shift_a_first_stop'] ?? ''), ENT_QUOTES, 'UTF-8');
            $last_stop_b  = $brigade['shift_b_last_stop'] ?? '';
            $last_stop    = htmlspecialchars((string)($last_stop_b ?: ($brigade['shift_a_last_stop'] ?? '')), ENT_QUOTES, 'UTF-8');
            $capacity     = htmlspecialchars((string)($brigade['shift_a_capacity']   ?? ''), ENT_QUOTES, 'UTF-8');
            $filtered     = array_values($departures);
        }

        $title_suffix = $shift_label !== '' ? $shift_label : '';
        $doc_title = $line_num . '/' . $brig_num . $title_suffix;

        // Group departures by direction, then by hour
        $by_direction = [];
        foreach ($filtered as $dep) {
            $dir  = $dep['direction'];
            $time = substr((string)$dep['departure_time'], 0, 5);
            $hour = intval(explode(':', $time)[0]);
            $min  = substr($time, 3, 2);
            if (!isset($by_direction[$dir])) {
                $by_direction[$dir] = [];
            }
            if (!isset($by_direction[$dir][$hour])) {
                $by_direction[$dir][$hour] = [];
            }
            $by_direction[$dir][$hour][] = $min;
        }

        // Prepare sub-title
        $sub_title_parts = [];
        if ($shift_label !== '') {
            $sub_title_parts[] = 'Zmiana ' . $shift_label;
        } else {
            $sub_title_parts[] = 'Cały dzień';
        }
        $sub_title = implode(' | ', $sub_title_parts);

        // Shift info line
        $info_parts = [];
        if ($shift_start) {
            $info_parts[] = '1. odjazd: <strong>' . $shift_start . '</strong>'
                . ($first_stop ? ' (' . $first_stop . ')' : '');
        }
        if ($shift_end) {
            $info_parts[] = 'Ostatni przyjazd: <strong>' . $shift_end . '</strong>'
                . ($last_stop ? ' (' . $last_stop . ')' : '');
        }
        if ($capacity) {
            $info_parts[] = 'Pojemność: <strong>' . $capacity . '</strong>';
        }

        // For full day with two shifts: show podmiana info
        $podmiana_html = '';
        if ($shift_type === 'full' && !empty($brigade['shift_a_end']) && !empty($brigade['shift_b_start'])) {
            $a_end   = $hhmm($brigade['shift_a_end']);
            $b_start = $hhmm($brigade['shift_b_start']);
            $podmiana_html = '<tr><td class="info-label">Podmiana</td><td class="info-value">'
                . htmlspecialchars($a_end . ' – ' . $b_start, ENT_QUOTES, 'UTF-8') . '</td></tr>';
        }

        // Build table rows per direction
        $tables_html = '';
        foreach ($by_direction as $dir => $hours_map) {
            ksort($hours_map);
            $min_hour = min(array_keys($hours_map));
            $max_hour = max(array_keys($hours_map));
            $rows = '';
            for ($h = $min_hour; $h <= $max_hour; $h++) {
                $mins = $hours_map[$h] ?? [];
                sort($mins);
                $rows .= '<tr>'
                    . '<td class="hour-cell">' . str_pad((string)$h, 2, '0', STR_PAD_LEFT) . '</td>'
                    . '<td class="mins-cell">' . implode('&nbsp;&nbsp;', $mins) . '</td>'
                    . '</tr>';
            }
            $dir_esc = htmlspecialchars($dir, ENT_QUOTES, 'UTF-8');
            $tables_html .= '<div class="direction-block">'
                . '<div class="direction-header">' . $dir_esc . '</div>'
                . '<table class="schedule-table"><tbody>' . $rows . '</tbody></table>'
                . '</div>';
        }

        if ($tables_html === '') {
            $tables_html = '<p class="no-dep">Brak odjazdów dla tej zmiany.</p>';
        }

        $notes_row = $description !== ''
            ? '<tr><td class="info-label">Uwagi</td><td class="info-value">' . nl2br($description) . '</td></tr>'
            : '';

        $operator_row = $operator !== ''
            ? '<tr><td class="info-label">Operator</td><td class="info-value">' . $operator . '</td></tr>'
            : '<tr><td class="info-label">Operator</td><td class="info-value">—</td></tr>';

        $info_rows = '';
        foreach ($info_parts as $part) {
            $info_rows .= '<tr><td colspan="2" class="info-single">' . $part . '</td></tr>';
        }

        $html = <<<HTML
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<title>Rozkład jazdy {$doc_title}</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,Helvetica,sans-serif;font-size:11pt;background:#fff;color:#000;padding:8mm}
.schedule-wrapper{border:2px solid #000;width:100%;max-width:190mm;margin:0 auto}
.schedule-header{border-bottom:2px solid #000;display:flex;align-items:stretch}
.header-line{border-right:2px solid #000;padding:4px 8px;font-size:13pt;font-weight:bold;min-width:60px;display:flex;align-items:center;justify-content:center}
.header-brigade{border-right:2px solid #000;padding:4px 8px;font-size:22pt;font-weight:bold;min-width:90px;display:flex;align-items:center;justify-content:center}
.header-sub{padding:4px 8px;flex:1;display:flex;flex-direction:column;justify-content:center}
.header-sub .sub-label{font-size:8pt;color:#555}
.header-sub .sub-value{font-size:12pt;font-weight:bold}
.schedule-body{padding:0}
.direction-block{border-bottom:1px solid #000}
.direction-block:last-child{border-bottom:none}
.direction-header{background:#000;color:#fff;padding:3px 8px;font-weight:bold;font-size:10pt;border-bottom:1px solid #000}
.schedule-table{width:100%;border-collapse:collapse}
.schedule-table td{border:1px solid #000;padding:2px 6px}
.hour-cell{width:40px;text-align:center;font-weight:bold;border-right:2px solid #000;background:#f5f5f5}
.mins-cell{letter-spacing:2px}
.no-dep{padding:6px 8px;font-style:italic;color:#555}
.schedule-footer{border-top:2px solid #000}
.info-table{width:100%;border-collapse:collapse}
.info-table td{border:1px solid #000;padding:3px 8px;font-size:10pt}
.info-label{width:120px;font-weight:bold;background:#f5f5f5;white-space:nowrap}
.info-value{word-break:break-word}
.info-single{padding:3px 8px;font-size:10pt}
@media print{body{padding:0}@page{margin:6mm}html,body{width:210mm}}
</style>
</head>
<body>
<div class="schedule-wrapper">
  <div class="schedule-header">
    <div class="header-line"><span>Linia<br>{$line_num}</span></div>
    <div class="header-brigade"><span>{$line_num}/{$brig_num}{$title_suffix}</span></div>
    <div class="header-sub">
      <span class="sub-label">Rozkład jazdy brygadowego</span>
      <span class="sub-value">{$sub_title}</span>
    </div>
  </div>
  <div class="schedule-body">
    {$tables_html}
  </div>
  <div class="schedule-footer">
    <table class="info-table">
      {$info_rows}
      {$podmiana_html}
      {$notes_row}
      {$operator_row}
    </table>
  </div>
</div>
</body>
</html>
HTML;

        return $html;
    }
}
