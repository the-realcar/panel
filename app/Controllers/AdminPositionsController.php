<?php

class AdminPositionsController extends Controller {
    public function index() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('positions', 'read');

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $per_page = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $per_page;

        $total_items = Position::countAll();
        $total_pages = (int)ceil($total_items / $per_page);
        $positions = Position::listWithCounts($per_page, $offset);

        $this->render('admin/positions/index', [
            'page_title' => 'Zarzadzanie stanowiskami',
            'positions' => $positions,
            'page' => $page,
            'total_pages' => $total_pages,
            'rbac' => $rbac
        ]);
    }

    public function create() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('positions', 'create');

        $errors = [];
        $form_data = ['active' => 'on'];
        $departments = Department::listActive();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/admin/positions/create.php');
            }

            $form_data = $_POST;

            $validator = new Validator($form_data);
            $validator->required('name', 'Nazwa stanowiska jest wymagana.');

            if (!empty($form_data['max_count'])) {
                $validator->integer('max_count', 'Limit musi byc liczba calkowita.')
                          ->min('max_count', 1, 'Limit musi byc wiekszy niz 0.');
            }

            if ($validator->fails()) {
                $errors = $validator->getErrors();
            }

            if (empty($errors['name']) && Position::existsByName($form_data['name'])) {
                $errors['name'] = 'Stanowisko o tej nazwie juz istnieje.';
            }

            if (empty($errors)) {
                try {
                    $new_pos_id = Position::create([
                        'name' => $form_data['name'],
                        'department_id' => !empty($form_data['department_id']) ? (int)$form_data['department_id'] : null,
                        'max_count' => !empty($form_data['max_count']) ? (int)$form_data['max_count'] : null,
                        'description' => !empty($form_data['description']) ? $form_data['description'] : null,
                        'active' => isset($form_data['active']) ? 'true' : 'false'
                    ]);
                    AuditLog::log('position.create', 'positions', $new_pos_id, null, ['name' => $form_data['name']]);

                    setFlashMessage('success', 'Stanowisko zostalo dodane pomyslnie.');
                    $this->redirectTo('/admin/positions/index.php');
                } catch (Exception $e) {
                    error_log('Error creating position: ' . $e->getMessage());
                    $errors['general'] = 'Wystapil blad podczas dodawania stanowiska.';
                }
            }
        }

        $this->render('admin/positions/create', [
            'page_title' => 'Dodaj stanowisko',
            'errors' => $errors,
            'form_data' => $form_data,
            'departments' => $departments
        ]);
    }

    public function edit() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('positions', 'update');

        $position_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$position_id) {
            setFlashMessage('error', 'Nieprawidlowy ID stanowiska.');
            $this->redirectTo('/admin/positions/index.php');
        }

        $position = Position::find($position_id);
        if (!$position) {
            setFlashMessage('error', 'Stanowisko nie zostalo znalezione.');
            $this->redirectTo('/admin/positions/index.php');
        }

        $errors = [];
        $form_data = $position;
        $departments = Department::listActive();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/admin/positions/edit.php?id=' . $position_id);
            }

            $form_data = array_merge($position, $_POST);

            $validator = new Validator($form_data);
            $validator->required('name', 'Nazwa stanowiska jest wymagana.');

            if (!empty($form_data['max_count'])) {
                $validator->integer('max_count', 'Limit musi byc liczba calkowita.')
                          ->min('max_count', 1, 'Limit musi byc wiekszy niz 0.');
            }

            if ($validator->fails()) {
                $errors = $validator->getErrors();
            }

            if (empty($errors['name']) && $form_data['name'] !== $position['name']) {
                if (Position::existsByName($form_data['name'], $position_id)) {
                    $errors['name'] = 'Stanowisko o tej nazwie juz istnieje.';
                }
            }

            if (empty($errors)) {
                try {
                    Position::update($position_id, [
                        'name' => $form_data['name'],
                        'department_id' => !empty($form_data['department_id']) ? (int)$form_data['department_id'] : null,
                        'max_count' => !empty($form_data['max_count']) ? (int)$form_data['max_count'] : null,
                        'description' => !empty($form_data['description']) ? $form_data['description'] : null,
                        'active' => isset($form_data['active']) ? 'true' : 'false'
                    ]);
                    AuditLog::log('position.update', 'positions', $position_id, ['name' => $position['name']], ['name' => $form_data['name']]);

                    setFlashMessage('success', 'Stanowisko zostalo zaktualizowane pomyslnie.');
                    $this->redirectTo('/admin/positions/index.php');
                } catch (Exception $e) {
                    error_log('Error updating position: ' . $e->getMessage());
                    $errors['general'] = 'Wystapil blad podczas aktualizacji stanowiska.';
                }
            }
        }

        $this->render('admin/positions/edit', [
            'page_title' => 'Edytuj stanowisko',
            'errors' => $errors,
            'form_data' => $form_data,
            'position_id' => $position_id,
            'departments' => $departments
        ]);
    }

    public function structure() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('positions', 'read');

        $structure = $this->buildStructureData();

        $this->render('admin/positions/structure', [
            'page_title' => 'Struktura organizacyjna',
            'structure' => $structure,
            'rbac' => $rbac
        ]);
    }

    public function exportStructure() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('positions', 'read');

        $structure = $this->buildStructureData();
        $lines = [
            'Struktura organizacyjna',
            'Wygenerowano: ' . date('Y-m-d H:i:s'),
            ''
        ];

        if (empty($structure)) {
            $lines[] = 'Brak danych struktury.';
        } else {
            foreach ($structure as $department => $positions) {
                $lines[] = '=== ' . $department . ' ===';
                foreach ($positions as $position) {
                    $current = (int)$position['current_count'];
                    $max = $position['max_count'] !== null ? (int)$position['max_count'] : null;
                    $limit_text = $max !== null && $max > 0 ? ($current . '/' . $max) : ($current . '/bez limitu');

                    $lines[] = '- ' . $position['name'] . ' | oblozenie: ' . $limit_text;

                    foreach ($position['assigned_users'] as $user) {
                        $full_name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                        $display = $full_name !== '' ? $full_name : $user['username'];
                        $status = !empty($user['active']) ? 'aktywny' : 'nieaktywny';
                        $lines[] = '  * ' . $display . ' (' . $user['username'] . ', ' . $status . ')';
                    }
                }

                $lines[] = '';
            }
        }

        $pdf_content = $this->buildSimplePdf($lines);
        $filename = 'struktura-organizacyjna-' . date('Ymd-His') . '.pdf';

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $pdf_content;
        exit;
    }

    private function buildStructureData(): array {
        $positions = Position::listStructureByDepartment();
        $structure = [];

        foreach ($positions as $position) {
            $department_name = $position['department_name'] ?? 'Bez przypisanego dzialu';

            if (!isset($structure[$department_name])) {
                $structure[$department_name] = [];
            }

            $users = Position::listAssignedUsers((int)$position['id']);
            $position['assigned_users'] = $users;
            $structure[$department_name][] = $position;
        }

        return $structure;
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
