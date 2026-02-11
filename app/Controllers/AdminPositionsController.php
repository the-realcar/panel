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
                    Position::create([
                        'name' => $form_data['name'],
                        'department_id' => !empty($form_data['department_id']) ? (int)$form_data['department_id'] : null,
                        'max_count' => !empty($form_data['max_count']) ? (int)$form_data['max_count'] : null,
                        'description' => !empty($form_data['description']) ? $form_data['description'] : null,
                        'active' => isset($form_data['active']) ? 'true' : 'false'
                    ]);

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
}
