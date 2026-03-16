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
}
