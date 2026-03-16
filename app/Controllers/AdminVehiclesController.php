<?php

class AdminVehiclesController extends Controller {
    private function stripCodeFence($value) {
        $value = trim((string)$value);
        $value = preg_replace('/^```[a-zA-Z0-9_-]*\s*/', '', $value);
        $value = preg_replace('/\s*```$/', '', $value);
        return trim((string)$value);
    }

    private function parseBooleanCsvValue($value, &$is_valid) {
        $normalized = mb_strtolower(trim((string)$value));
        $is_valid = true;

        if ($normalized === '' || in_array($normalized, ['0', 'false', 'nie', 'no', 'n'], true)) {
            return false;
        }

        if (in_array($normalized, ['1', 'true', 'tak', 'yes', 'y'], true)) {
            return true;
        }

        $is_valid = false;
        return false;
    }

    private function parseBulkVehicleCsv($raw_input, array &$errors) {
        $payload = $this->stripCodeFence($raw_input);
        if ($payload === '') {
            $errors['bulk_csv'] = 'Wklej dane CSV do importu.';
            return [];
        }

        $lines = preg_split('/\r\n|\n|\r/', $payload);
        $lines = array_values(array_filter(array_map('trim', $lines), static function ($line) {
            return $line !== '';
        }));

        if (count($lines) < 2) {
            $errors['bulk_csv'] = 'CSV musi zawierac naglowek i co najmniej jeden wiersz danych.';
            return [];
        }

        $header = array_map(static function ($value) {
            return trim(mb_strtolower((string)$value));
        }, str_getcsv($lines[0], ';'));

        $required_headers = ['nr_poj', 'vehicle_type', 'status'];
        foreach ($required_headers as $required_header) {
            if (!in_array($required_header, $header, true)) {
                $errors['bulk_csv'] = 'Brakuje wymaganej kolumny CSV: ' . $required_header . '.';
                return [];
            }
        }

        $allowed_vehicle_types = ['bus', 'tram', 'metro', 'tbus'];
        $allowed_capacity = ['MINI', 'MIDI', 'MAXI', 'MAXI+', 'MEGA', 'MEGA+', 'GIGA'];
        $allowed_statuses = ['sprawny', 'w naprawie', 'odstawiony', 'zawieszony'];
        $allowed_drive_types = ['Diesel', 'CNG', 'Hybrydowy', 'Elektryczny', 'Wodorowy'];
        $allowed_depots = ['KM', 'KW', 'MC'];
        $allowed_carriers = ['Ostrans', 'KujaTrans', 'Ostromunikacja'];

        $rows = [];
        $seen_vehicle_numbers = [];
        $seen_plates = [];

        for ($i = 1; $i < count($lines); $i++) {
            $values = str_getcsv($lines[$i], ';');
            if (count($values) < count($header)) {
                $values = array_pad($values, count($header), '');
            }

            $row = [];
            foreach ($header as $index => $column) {
                $row[$column] = trim((string)($values[$index] ?? ''));
            }

            $row_number = $i + 1;
            $nr_poj = $row['nr_poj'] ?? '';
            $reg_plate = $row['reg_plate'] ?? '';
            $vehicle_type = $row['vehicle_type'] ?? '';
            $status = $row['status'] ?? '';
            $rok_prod = $row['rok_prod'] ?? '';
            $pojemnosc = $row['pojemnosc'] ?? '';
            $typ_napedu = $row['typ_napedu'] ?? '';
            $zajezdnia = $row['zajezdnia'] ?? '';
            $przewoznik = $row['przewoznik'] ?? '';

            if ($nr_poj === '' && $reg_plate === '' && $vehicle_type === '' && $status === '') {
                continue;
            }

            if ($nr_poj === '' || $vehicle_type === '' || $status === '') {
                $errors['bulk_csv'] = 'Wiersz #' . $row_number . ' musi zawierac nr_poj, vehicle_type i status.';
                return [];
            }

            if (!in_array($vehicle_type, $allowed_vehicle_types, true)) {
                $errors['bulk_csv'] = 'Wiersz #' . $row_number . ' ma nieprawidlowy vehicle_type.';
                return [];
            }

            if (!in_array($status, $allowed_statuses, true)) {
                $errors['bulk_csv'] = 'Wiersz #' . $row_number . ' ma nieprawidlowy status.';
                return [];
            }

            if ($pojemnosc !== '' && !in_array($pojemnosc, $allowed_capacity, true)) {
                $errors['bulk_csv'] = 'Wiersz #' . $row_number . ' ma nieprawidlowa pojemnosc.';
                return [];
            }

            if ($typ_napedu !== '' && !in_array($typ_napedu, $allowed_drive_types, true)) {
                $errors['bulk_csv'] = 'Wiersz #' . $row_number . ' ma nieprawidlowy typ_napedu.';
                return [];
            }

            if ($zajezdnia !== '' && !in_array($zajezdnia, $allowed_depots, true)) {
                $errors['bulk_csv'] = 'Wiersz #' . $row_number . ' ma nieprawidlowa zajezdnie.';
                return [];
            }

            if ($przewoznik !== '' && !in_array($przewoznik, $allowed_carriers, true)) {
                $errors['bulk_csv'] = 'Wiersz #' . $row_number . ' ma nieprawidlowego przewoznika.';
                return [];
            }

            if ($rok_prod !== '') {
                if (!filter_var($rok_prod, FILTER_VALIDATE_INT)) {
                    $errors['bulk_csv'] = 'Wiersz #' . $row_number . ' ma nieprawidlowy rok produkcji.';
                    return [];
                }

                $rok_prod_int = (int)$rok_prod;
                if ($rok_prod_int < 1900 || $rok_prod_int > ((int)date('Y') + 1)) {
                    $errors['bulk_csv'] = 'Wiersz #' . $row_number . ' ma rok produkcji poza dozwolonym zakresem.';
                    return [];
                }
            } else {
                $rok_prod_int = null;
            }

            $bool_valid = true;
            $klimatyzacja = $this->parseBooleanCsvValue($row['klimatyzacja'] ?? '', $bool_valid);
            if (!$bool_valid) {
                $errors['bulk_csv'] = 'Wiersz #' . $row_number . ' ma nieprawidlowa wartosc klimatyzacja. Uzyj np. tak/nie lub 1/0.';
                return [];
            }

            if (isset($seen_vehicle_numbers[$nr_poj])) {
                $errors['bulk_csv'] = 'W pliku powtorzono numer pojazdu: ' . $nr_poj . '.';
                return [];
            }
            $seen_vehicle_numbers[$nr_poj] = true;

            if ($reg_plate !== '') {
                $normalized_plate = mb_strtolower($reg_plate);
                if (isset($seen_plates[$normalized_plate])) {
                    $errors['bulk_csv'] = 'W pliku powtorzono rejestracje: ' . $reg_plate . '.';
                    return [];
                }
                $seen_plates[$normalized_plate] = true;
            }

            if (Vehicle::existsByNumber($nr_poj)) {
                $errors['bulk_csv'] = 'Pojazd o numerze ' . $nr_poj . ' juz istnieje w bazie.';
                return [];
            }

            if ($reg_plate !== '' && Vehicle::existsByPlate($reg_plate)) {
                $errors['bulk_csv'] = 'Pojazd o rejestracji ' . $reg_plate . ' juz istnieje w bazie.';
                return [];
            }

            $rows[] = [
                'nr_poj' => $nr_poj,
                'reg_plate' => $reg_plate !== '' ? $reg_plate : null,
                'vehicle_type' => $vehicle_type,
                'model' => ($row['model'] ?? '') !== '' ? $row['model'] : null,
                'rok_prod' => $rok_prod_int,
                'pojemnosc' => $pojemnosc !== '' ? $pojemnosc : null,
                'status' => $status,
                'marka' => ($row['marka'] ?? '') !== '' ? $row['marka'] : null,
                'pulpit' => ($row['pulpit'] ?? '') !== '' ? $row['pulpit'] : null,
                'engine' => ($row['engine'] ?? '') !== '' ? $row['engine'] : null,
                'gearbox' => ($row['gearbox'] ?? '') !== '' ? $row['gearbox'] : null,
                'typ_napedu' => $typ_napedu !== '' ? $typ_napedu : null,
                'norma_spalania' => ($row['norma_spalania'] ?? '') !== '' ? $row['norma_spalania'] : null,
                'klimatyzacja' => $klimatyzacja,
                'zajezdnia' => $zajezdnia !== '' ? $zajezdnia : null,
                'przewoznik' => $przewoznik !== '' ? $przewoznik : null,
                'opiekun_1' => ($row['opiekun_1'] ?? '') !== '' ? $row['opiekun_1'] : null,
                'opiekun_2' => ($row['opiekun_2'] ?? '') !== '' ? $row['opiekun_2'] : null,
                'dodatkowe_informacje' => ($row['dodatkowe_informacje'] ?? '') !== '' ? $row['dodatkowe_informacje'] : null
            ];
        }

        if (empty($rows)) {
            $errors['bulk_csv'] = 'Nie znaleziono zadnych poprawnych wierszy danych do importu.';
            return [];
        }

        return $rows;
    }

    private function importBulkVehicles(array $rows) {
        $created = 0;

        foreach ($rows as $row) {
            $vehicle_id = Vehicle::create($row);
            $created++;

            AuditLog::log('vehicle.create', 'vehicles', $vehicle_id, null, [
                'nr_poj' => $row['nr_poj'],
                'vehicle_type' => $row['vehicle_type'],
                'source' => 'bulk_csv'
            ]);
        }

        return $created;
    }

    public function index() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('vehicles', 'read');

        $status_filter = $_GET['status'] ?? '';
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $per_page = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $per_page;

        $total_items = Vehicle::countByStatus($status_filter);
        $total_pages = (int)ceil($total_items / $per_page);
        $vehicles = Vehicle::listByStatus($status_filter, $per_page, $offset);

        $this->render('admin/vehicles/index', [
            'page_title' => 'Zarzadzanie pojazdami',
            'vehicles' => $vehicles,
            'status_filter' => $status_filter,
            'page' => $page,
            'total_pages' => $total_pages,
            'rbac' => $rbac
        ]);
    }

    public function create() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('vehicles', 'create');

        $errors = [];
        $form_data = ['bulk_csv' => ''];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/admin/vehicles/create.php');
            }

            $form_data = $_POST;
            $import_mode = $_POST['import_mode'] ?? 'single';

            if ($import_mode === 'bulk_csv') {
                $rows = $this->parseBulkVehicleCsv($form_data['bulk_csv'] ?? '', $errors);

                if (empty($errors)) {
                    try {
                        $created = $this->importBulkVehicles($rows);
                        setFlashMessage('success', 'Import zakonczony: dodano pojazdow ' . $created . '.');
                        $this->redirectTo('/admin/vehicles/index.php');
                    } catch (Exception $e) {
                        error_log('Error bulk importing vehicles: ' . $e->getMessage());
                        $errors['bulk_csv'] = 'Wystapil blad podczas importu pojazdow.';
                    }
                }

                $this->render('admin/vehicles/create', [
                    'page_title' => 'Dodaj pojazd',
                    'errors' => $errors,
                    'form_data' => $form_data
                ]);
                return;
            }

            $validator = new Validator($form_data);
            $validator->required('nr_poj', 'Numer pojazdu jest wymagany.')
                      ->required('vehicle_type', 'Typ pojazdu jest wymagany.')
                      ->required('status', 'Status jest wymagany.');

            if (!empty($form_data['rok_prod'])) {
                $validator->integer('rok_prod', 'Rok produkcji musi byc liczba calkowita.')
                          ->min('rok_prod', 1900, 'Rok produkcji musi byc wiekszy niz 1900.')
                          ->max('rok_prod', date('Y') + 1, 'Rok produkcji nie moze byc w przyszlosci.');
            }

            if ($validator->fails()) {
                $errors = $validator->getErrors();
            }

            if (empty($errors['nr_poj']) && Vehicle::existsByNumber($form_data['nr_poj'])) {
                $errors['nr_poj'] = 'Pojazd o tym numerze juz istnieje.';
            }

            if (!empty($form_data['reg_plate']) && empty($errors['reg_plate'])) {
                if (Vehicle::existsByPlate($form_data['reg_plate'])) {
                    $errors['reg_plate'] = 'Pojazd o tej rejestracji juz istnieje.';
                }
            }

            if (empty($errors)) {
                try {
                    $new_vehicle_id = Vehicle::create([
                        'nr_poj' => $form_data['nr_poj'],
                        'reg_plate' => !empty($form_data['reg_plate']) ? $form_data['reg_plate'] : null,
                        'vehicle_type' => $form_data['vehicle_type'],
                        'model' => !empty($form_data['model']) ? $form_data['model'] : null,
                        'rok_prod' => !empty($form_data['rok_prod']) ? (int)$form_data['rok_prod'] : null,
                        'pojemnosc' => !empty($form_data['pojemnosc']) ? $form_data['pojemnosc'] : null,
                        'status' => $form_data['status'],
                        'marka' => !empty($form_data['marka']) ? $form_data['marka'] : null,
                        'pulpit' => !empty($form_data['pulpit']) ? $form_data['pulpit'] : null,
                        'engine' => !empty($form_data['engine']) ? $form_data['engine'] : null,
                        'gearbox' => !empty($form_data['gearbox']) ? $form_data['gearbox'] : null,
                        'typ_napedu' => !empty($form_data['typ_napedu']) ? $form_data['typ_napedu'] : null,
                        'norma_spalania' => !empty($form_data['norma_spalania']) ? $form_data['norma_spalania'] : null,
                        'klimatyzacja' => isset($form_data['klimatyzacja']) ? true : false,
                        'zajezdnia' => !empty($form_data['zajezdnia']) ? $form_data['zajezdnia'] : null,
                        'przewoznik' => !empty($form_data['przewoznik']) ? $form_data['przewoznik'] : null,
                        'opiekun_1' => !empty($form_data['opiekun_1']) ? $form_data['opiekun_1'] : null,
                        'opiekun_2' => !empty($form_data['opiekun_2']) ? $form_data['opiekun_2'] : null,
                        'dodatkowe_informacje' => !empty($form_data['dodatkowe_informacije']) ? $form_data['dodatkowe_informacje'] : null
                    ]);
                    AuditLog::log('vehicle.create', 'vehicles', $new_vehicle_id, null, ['nr_poj' => $form_data['nr_poj'], 'vehicle_type' => $form_data['vehicle_type']]);

                    setFlashMessage('success', 'Pojazd zostal dodany pomyslnie.');
                    $this->redirectTo('/admin/vehicles/index.php');
                } catch (Exception $e) {
                    error_log('Error creating vehicle: ' . $e->getMessage());
                    $errors['general'] = 'Wystapil blad podczas dodawania pojazdu.';
                }
            }
        }

        $this->render('admin/vehicles/create', [
            'page_title' => 'Dodaj pojazd',
            'errors' => $errors,
            'form_data' => $form_data
        ]);
    }

    public function edit() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('vehicles', 'update');

        $vehicle_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$vehicle_id) {
            setFlashMessage('error', 'Nieprawidlowy ID pojazdu.');
            $this->redirectTo('/admin/vehicles/index.php');
        }

        $vehicle = Vehicle::find($vehicle_id);
        if (!$vehicle) {
            setFlashMessage('error', 'Pojazd nie zostal znaleziony.');
            $this->redirectTo('/admin/vehicles/index.php');
        }

        $errors = [];
        $form_data = $vehicle;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/admin/vehicles/edit.php?id=' . $vehicle_id);
            }

            $form_data = array_merge($vehicle, $_POST);

            $validator = new Validator($form_data);
            $validator->required('nr_poj', 'Numer pojazdu jest wymagany.')
                      ->required('vehicle_type', 'Typ pojazdu jest wymagany.')
                      ->required('status', 'Status jest wymagany.');

            if (!empty($form_data['rok_prod'])) {
                $validator->integer('rok_prod', 'Rok produkcji musi byc liczba calkowita.')
                          ->min('rok_prod', 1900, 'Rok produkcji musi byc wiekszy niz 1900.')
                          ->max('rok_prod', date('Y') + 1, 'Rok produkcji nie moze byc w przyszlosci.');
            }

            if ($validator->fails()) {
                $errors = $validator->getErrors();
            }

            if (empty($errors['nr_poj']) && $form_data['nr_poj'] !== $vehicle['nr_poj']) {
                if (Vehicle::existsByNumber($form_data['nr_poj'], $vehicle_id)) {
                    $errors['nr_poj'] = 'Pojazd o tym numerze juz istnieje.';
                }
            }

            if (!empty($form_data['reg_plate']) && empty($errors['reg_plate'])) {
                if ($form_data['reg_plate'] !== $vehicle['reg_plate']) {
                    if (Vehicle::existsByPlate($form_data['reg_plate'], $vehicle_id)) {
                        $errors['reg_plate'] = 'Pojazd o tej rejestracji juz istnieje.';
                    }
                }
            }

            if (empty($errors)) {
                try {
                    Vehicle::update($vehicle_id, [
                        'nr_poj' => $form_data['nr_poj'],
                        'reg_plate' => !empty($form_data['reg_plate']) ? $form_data['reg_plate'] : null,
                        'vehicle_type' => $form_data['vehicle_type'],
                        'model' => !empty($form_data['model']) ? $form_data['model'] : null,
                        'rok_prod' => !empty($form_data['rok_prod']) ? (int)$form_data['rok_prod'] : null,
                        'pojemnosc' => !empty($form_data['pojemnosc']) ? $form_data['pojemnosc'] : null,
                        'status' => $form_data['status'],
                        'marka' => !empty($form_data['marka']) ? $form_data['marka'] : null,
                        'pulpit' => !empty($form_data['pulpit']) ? $form_data['pulpit'] : null,
                        'engine' => !empty($form_data['engine']) ? $form_data['engine'] : null,
                        'gearbox' => !empty($form_data['gearbox']) ? $form_data['gearbox'] : null,
                        'typ_napedu' => !empty($form_data['typ_napedu']) ? $form_data['typ_napedu'] : null,
                        'norma_spalania' => !empty($form_data['norma_spalania']) ? $form_data['norma_spalania'] : null,
                        'klimatyzacja' => isset($form_data['klimatyzacja']) ? true : false,
                        'zajezdnia' => !empty($form_data['zajezdnia']) ? $form_data['zajezdnia'] : null,
                        'przewoznik' => !empty($form_data['przewoznik']) ? $form_data['przewoznik'] : null,
                        'opiekun_1' => !empty($form_data['opiekun_1']) ? $form_data['opiekun_1'] : null,
                        'opiekun_2' => !empty($form_data['opiekun_2']) ? $form_data['opiekun_2'] : null,
                        'dodatkowe_informacje' => !empty($form_data['dodatkowe_informacje']) ? $form_data['dodatkowe_informacje'] : null
                    ]);
                    AuditLog::log('vehicle.update', 'vehicles', $vehicle_id, ['nr_poj' => $vehicle['nr_poj'], 'status' => $vehicle['status']], ['nr_poj' => $form_data['nr_poj'], 'status' => $form_data['status']]);

                    setFlashMessage('success', 'Pojazd zostal zaktualizowany pomyslnie.');
                    $this->redirectTo('/admin/vehicles/index.php');
                } catch (Exception $e) {
                    error_log('Error updating vehicle: ' . $e->getMessage());
                    $errors['general'] = 'Wystapil blad podczas aktualizacji pojazdu.';
                }
            }
        }

        $this->render('admin/vehicles/edit', [
            'page_title' => 'Edytuj pojazd',
            'errors' => $errors,
            'form_data' => $form_data,
            'vehicle_id' => $vehicle_id
        ]);
    }

    public function delete() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('vehicles', 'delete');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            setFlashMessage('error', 'Nieprawidlowe zadanie.');
            $this->redirectTo('/admin/vehicles/index.php');
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlashMessage('error', 'Nieprawidlowy token CSRF.');
            $this->redirectTo('/admin/vehicles/index.php');
        }

        $vehicle_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if (!$vehicle_id) {
            setFlashMessage('error', 'Nieprawidlowy ID pojazdu.');
            $this->redirectTo('/admin/vehicles/index.php');
        }

        $vehicle = Vehicle::find($vehicle_id);
        if (!$vehicle) {
            setFlashMessage('error', 'Pojazd nie zostal znaleziony.');
            $this->redirectTo('/admin/vehicles/index.php');
        }

        try {
            Vehicle::delete($vehicle_id);
            AuditLog::log('vehicle.delete', 'vehicles', $vehicle_id, ['nr_poj' => $vehicle['nr_poj'], 'vehicle_type' => $vehicle['vehicle_type']], null);
            setFlashMessage('success', 'Pojazd zostal usuniety pomyslnie.');
        } catch (Exception $e) {
            error_log('Error deleting vehicle: ' . $e->getMessage());
            setFlashMessage('error', 'Nie mozna usunac pojazdu. Moze byc uzywany w innych miejscach systemu.');
        }

        $this->redirectTo('/admin/vehicles/index.php');
    }
}
