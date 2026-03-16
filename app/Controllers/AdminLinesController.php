<?php

class AdminLinesController extends Controller {
    private function stripCodeFence($value) {
        $value = trim((string)$value);
        $value = preg_replace('/^```[a-zA-Z0-9_-]*\s*/', '', $value);
        $value = preg_replace('/\s*```$/', '', $value);
        return trim((string)$value);
    }

    private function parseBulkLineImport($raw_input, array &$errors) {
        $payload = $this->stripCodeFence($raw_input);
        if ($payload === '') {
            $errors['bulk_json'] = 'Wklej dane JSON do importu.';
            return [];
        }

        $decoded_entries = null;
        if (strpos($payload, '[') === 0) {
            $decoded_entries = json_decode($payload, true);
            if (!is_array($decoded_entries)) {
                $errors['bulk_json'] = 'Nie udalo sie odczytac tablicy JSON.';
                return [];
            }
        } else {
            $chunks = preg_split('/;\s*(?=\{)/', rtrim($payload, ';'), -1, PREG_SPLIT_NO_EMPTY);
            $decoded_entries = [];

            foreach ($chunks as $index => $chunk) {
                $row = json_decode(trim($chunk), true);
                if (!is_array($row)) {
                    $errors['bulk_json'] = 'Nieprawidlowy JSON w rekordzie #' . ($index + 1) . '.';
                    return [];
                }
                $decoded_entries[] = $row;
            }
        }

        if (empty($decoded_entries)) {
            $errors['bulk_json'] = 'Nie znaleziono zadnych rekordow do importu.';
            return [];
        }

        $entries = [];
        foreach ($decoded_entries as $index => $entry) {
            $row_no = $index + 1;
            $line_number = trim((string)($entry['Line'] ?? ''));
            $destination = trim((string)($entry['Destination'] ?? ''));
            $stops = $entry['Stops'] ?? null;

            if ($line_number === '' || $destination === '' || !is_array($stops)) {
                $errors['bulk_json'] = 'Rekord #' . $row_no . ' musi zawierac pola Line, Destination i Stops.';
                return [];
            }

            $normalized_stops = [];
            foreach ($stops as $stop_name) {
                $normalized_name = trim((string)$stop_name);
                if ($normalized_name === '') {
                    $errors['bulk_json'] = 'Rekord #' . $row_no . ' zawiera pusty przystanek.';
                    return [];
                }
                $normalized_stops[] = $normalized_name;
            }

            if (count($normalized_stops) < 2) {
                $errors['bulk_json'] = 'Rekord #' . $row_no . ' musi zawierac co najmniej dwa przystanki.';
                return [];
            }

            if (count(array_unique($normalized_stops)) !== count($normalized_stops)) {
                $errors['bulk_json'] = 'Rekord #' . $row_no . ' zawiera powtorzony przystanek, czego obecna struktura tras nie obsluguje.';
                return [];
            }

            $entries[] = [
                'line_number' => $line_number,
                'destination' => $destination,
                'stops' => $normalized_stops
            ];
        }

        return $entries;
    }

    private function buildImportedLineName(array $entries) {
        $first_stops = $entries[0]['stops'] ?? [];
        if (count($first_stops) >= 2) {
            return $first_stops[0] . ' - ' . $first_stops[count($first_stops) - 1];
        }

        return $entries[0]['destination'] ?? 'Nowa linia';
    }

    private function buildImportedRouteDescription(array $entries) {
        $descriptions = [];
        foreach ($entries as $entry) {
            $first_stop = $entry['stops'][0] ?? null;
            $last_stop = $entry['stops'][count($entry['stops']) - 1] ?? null;
            if ($first_stop !== null && $last_stop !== null) {
                $descriptions[] = $first_stop . ' -> ' . $last_stop;
            }
        }

        $descriptions = array_values(array_unique($descriptions));
        return implode(' | ', $descriptions);
    }

    private function importBulkLines(array $entries, $line_type, $active) {
        $grouped_entries = [];
        foreach ($entries as $entry) {
            $grouped_entries[$entry['line_number']][] = $entry;
        }

        $summary = [
            'lines_created' => 0,
            'variants_created' => 0,
            'variants_updated' => 0,
            'stops_created' => 0,
            'platforms_created' => 0
        ];

        foreach ($grouped_entries as $line_number => $line_entries) {
            $line = Line::findByNumber($line_number);
            if (!$line) {
                $line_id = Line::create([
                    'line_number' => $line_number,
                    'name' => $this->buildImportedLineName($line_entries),
                    'route_description' => $this->buildImportedRouteDescription($line_entries),
                    'line_type' => $line_type,
                    'active' => $active ? 'true' : 'false'
                ]);
                $line = Line::find($line_id);
                $summary['lines_created']++;

                AuditLog::log('line.create', 'lines', $line_id, null, [
                    'line_number' => $line_number,
                    'source' => 'bulk_json'
                ]);
            }

            foreach ($line_entries as $entry) {
                $variant = RouteVariant::findByLineAndDirection($line['id'], $entry['destination']);
                if ($variant) {
                    RouteVariant::update($variant['id'], [
                        'line_id' => $line['id'],
                        'variant_name' => 'Kierunek: ' . $entry['destination'],
                        'variant_type' => 'normal',
                        'direction' => $entry['destination'],
                        'is_active' => true
                    ]);
                    RouteStop::deleteByVariant($variant['id']);
                    $variant_id = $variant['id'];
                    $summary['variants_updated']++;
                } else {
                    $variant_id = RouteVariant::create([
                        'line_id' => $line['id'],
                        'variant_name' => 'Kierunek: ' . $entry['destination'],
                        'variant_type' => 'normal',
                        'direction' => $entry['destination'],
                        'is_active' => true
                    ]);
                    $summary['variants_created']++;
                }

                foreach ($entry['stops'] as $index => $stop_name) {
                    $stop = Stop::findByName($stop_name);
                    if (!$stop) {
                        $stop_id = Stop::create([
                            'city_id' => null,
                            'name' => $stop_name,
                            'opis' => null,
                            'status_nz' => false,
                            'active' => true
                        ]);
                        $stop = Stop::find($stop_id);
                        $summary['stops_created']++;
                    }

                    $platform = Platform::findByStopAndNumber($stop['id'], '01');
                    if (!$platform) {
                        $platform_id = Platform::create([
                            'stop_id' => $stop['id'],
                            'platform_number' => '01',
                            'platform_type' => 'regular',
                            'description' => 'Utworzone automatycznie podczas importu linii',
                            'active' => true
                        ]);
                        $platform = Platform::find($platform_id);
                        $summary['platforms_created']++;
                    }

                    RouteStop::create([
                        'route_variant_id' => $variant_id,
                        'platform_id' => $platform['id'],
                        'stop_sequence' => $index + 1,
                        'travel_time_minutes' => null,
                        'is_timing_point' => false
                    ]);
                }

                AuditLog::log('line.bulk_variant_import', 'route_variants', $variant_id, null, [
                    'line_id' => $line['id'],
                    'line_number' => $line_number,
                    'direction' => $entry['destination']
                ]);
            }
        }

        return $summary;
    }

    public function index() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('lines', 'read');

        $type_filter = $_GET['type'] ?? '';
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $per_page = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $per_page;

        $total_items = Line::countByType($type_filter);
        $total_pages = (int)ceil($total_items / $per_page);
        $lines = Line::listByType($type_filter, $per_page, $offset);

        $this->render('admin/lines/index', [
            'page_title' => 'Zarzadzanie liniami',
            'lines' => $lines,
            'type_filter' => $type_filter,
            'page' => $page,
            'total_pages' => $total_pages,
            'rbac' => $rbac
        ]);
    }

    public function create() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('lines', 'create');

        $errors = [];
        $form_data = ['active' => 'on', 'bulk_active' => 'on', 'bulk_line_type' => 'bus', 'bulk_json' => ''];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/admin/lines/create.php');
            }

            $form_data = $_POST;
            $import_mode = $_POST['import_mode'] ?? 'single';

            if ($import_mode === 'bulk_json') {
                $bulk_line_type = trim((string)($form_data['bulk_line_type'] ?? ''));
                $allowed_types = ['bus', 'tram', 'metro'];
                if (!in_array($bulk_line_type, $allowed_types, true)) {
                    $errors['bulk_line_type'] = 'Wybierz poprawny typ linii dla importu.';
                }

                $entries = $this->parseBulkLineImport($form_data['bulk_json'] ?? '', $errors);

                if (empty($errors)) {
                    try {
                        $summary = $this->importBulkLines($entries, $bulk_line_type, isset($form_data['bulk_active']));
                        setFlashMessage(
                            'success',
                            'Import zakonczony: dodano linii ' . $summary['lines_created'] . ', utworzono wariantow ' . $summary['variants_created'] . ', zaktualizowano wariantow ' . $summary['variants_updated'] . '.'
                        );
                        $this->redirectTo('/admin/lines/index.php');
                    } catch (Exception $e) {
                        error_log('Error bulk importing lines: ' . $e->getMessage());
                        $errors['bulk_json'] = 'Wystapil blad podczas importu linii.';
                    }
                }

                $this->render('admin/lines/create', [
                    'page_title' => 'Dodaj linie',
                    'errors' => $errors,
                    'form_data' => $form_data
                ]);
                return;
            }

            $validator = new Validator($form_data);
            $validator->required('line_number', 'Numer linii jest wymagany.')
                      ->required('name', 'Nazwa jest wymagana.')
                      ->required('line_type', 'Typ linii jest wymagany.');

            if ($validator->fails()) {
                $errors = $validator->getErrors();
            }

            if (empty($errors['line_number']) && Line::existsByNumber($form_data['line_number'])) {
                $errors['line_number'] = 'Linia o tym numerze juz istnieje.';
            }

            if (empty($errors)) {
                try {
                    $new_line_id = Line::create([
                        'line_number' => $form_data['line_number'],
                        'name' => $form_data['name'],
                        'route_description' => !empty($form_data['route_description']) ? $form_data['route_description'] : null,
                        'line_type' => $form_data['line_type'],
                        'active' => isset($form_data['active']) ? 'true' : 'false'
                    ]);
                    AuditLog::log('line.create', 'lines', $new_line_id, null, ['line_number' => $form_data['line_number'], 'name' => $form_data['name'], 'line_type' => $form_data['line_type']]);

                    setFlashMessage('success', 'Linia zostala dodana pomyslnie.');
                    $this->redirectTo('/admin/lines/index.php');
                } catch (Exception $e) {
                    error_log('Error creating line: ' . $e->getMessage());
                    $errors['general'] = 'Wystapil blad podczas dodawania linii.';
                }
            }
        }

        $this->render('admin/lines/create', [
            'page_title' => 'Dodaj linie',
            'errors' => $errors,
            'form_data' => $form_data
        ]);
    }

    public function edit() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('lines', 'update');

        $line_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$line_id) {
            setFlashMessage('error', 'Nieprawidlowy ID linii.');
            $this->redirectTo('/admin/lines/index.php');
        }

        $line = Line::find($line_id);
        if (!$line) {
            setFlashMessage('error', 'Linia nie zostala znaleziona.');
            $this->redirectTo('/admin/lines/index.php');
        }

        $errors = [];
        $form_data = $line;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/admin/lines/edit.php?id=' . $line_id);
            }

            $form_data = array_merge($line, $_POST);

            $validator = new Validator($form_data);
            $validator->required('line_number', 'Numer linii jest wymagany.')
                      ->required('name', 'Nazwa jest wymagana.')
                      ->required('line_type', 'Typ linii jest wymagany.');

            if ($validator->fails()) {
                $errors = $validator->getErrors();
            }

            if (empty($errors['line_number']) && $form_data['line_number'] !== $line['line_number']) {
                if (Line::existsByNumber($form_data['line_number'], $line_id)) {
                    $errors['line_number'] = 'Linia o tym numerze juz istnieje.';
                }
            }

            if (empty($errors)) {
                try {
                    Line::update($line_id, [
                        'line_number' => $form_data['line_number'],
                        'name' => $form_data['name'],
                        'route_description' => !empty($form_data['route_description']) ? $form_data['route_description'] : null,
                        'line_type' => $form_data['line_type'],
                        'active' => isset($form_data['active']) ? 'true' : 'false'
                    ]);
                    AuditLog::log('line.update', 'lines', $line_id, ['line_number' => $line['line_number'], 'name' => $line['name']], ['line_number' => $form_data['line_number'], 'name' => $form_data['name']]);

                    setFlashMessage('success', 'Linia zostala zaktualizowana pomyslnie.');
                    $this->redirectTo('/admin/lines/index.php');
                } catch (Exception $e) {
                    error_log('Error updating line: ' . $e->getMessage());
                    $errors['general'] = 'Wystapil blad podczas aktualizacji linii.';
                }
            }
        }

        $this->render('admin/lines/edit', [
            'page_title' => 'Edytuj linie',
            'errors' => $errors,
            'form_data' => $form_data,
            'line_id' => $line_id
        ]);
    }

    public function delete() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('lines', 'delete');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            setFlashMessage('error', 'Nieprawidlowe zadanie.');
            $this->redirectTo('/admin/lines/index.php');
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlashMessage('error', 'Nieprawidlowy token CSRF.');
            $this->redirectTo('/admin/lines/index.php');
        }

        $line_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if (!$line_id) {
            setFlashMessage('error', 'Nieprawidlowy ID linii.');
            $this->redirectTo('/admin/lines/index.php');
        }

        $line = Line::find($line_id);
        if (!$line) {
            setFlashMessage('error', 'Linia nie zostala znaleziona.');
            $this->redirectTo('/admin/lines/index.php');
        }

        try {
            Line::delete($line_id);
            AuditLog::log('line.delete', 'lines', $line_id, ['line_number' => $line['line_number'], 'name' => $line['name']], null);
            setFlashMessage('success', 'Linia zostala usunieta pomyslnie.');
        } catch (Exception $e) {
            error_log('Error deleting line: ' . $e->getMessage());
            setFlashMessage('error', 'Nie mozna usunac linii. Moze byc uzywana w innych miejscach systemu.');
        }

        $this->redirectTo('/admin/lines/index.php');
    }
}
