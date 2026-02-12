<?php

class AdminBrigadesController extends Controller {
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
        $brigades = Brigade::listAll($per_page, $offset);

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
        $form_data = ['active' => 'on'];
        $lines = Line::listActive();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/admin/brigades/create.php');
            }

            $form_data = $_POST;

            $validator = new Validator($form_data);
            $validator->required('line_id', 'Linia jest wymagana.')
                      ->required('brigade_number', 'Numer brygady jest wymagany.');

            if ($validator->fails()) {
                $errors = $validator->getErrors();
            }

            if (empty($errors['line_id']) && empty($errors['brigade_number'])) {
                if (Brigade::exists($form_data['line_id'], $form_data['brigade_number'])) {
                    $errors['brigade_number'] = 'Brygada o tym numerze juz istnieje dla tej linii.';
                }
            }

            if (empty($errors)) {
                try {
                    Brigade::create([
                        'line_id' => $form_data['line_id'],
                        'brigade_number' => $form_data['brigade_number'],
                        'default_vehicle_type' => !empty($form_data['default_vehicle_type']) ? $form_data['default_vehicle_type'] : null,
                        'description' => !empty($form_data['description']) ? $form_data['description'] : null,
                        'active' => isset($form_data['active']) ? 'true' : 'false'
                    ]);

                    setFlashMessage('success', 'Brygada zostala dodana pomyslnie.');
                    $this->redirectTo('/admin/brigades/index.php');
                } catch (Exception $e) {
                    error_log('Error creating brigade: ' . $e->getMessage());
                    $errors['general'] = 'Wystapil blad podczas dodawania brygady.';
                }
            }
        }

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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/admin/brigades/edit.php?id=' . $brigade_id);
            }

            $form_data = array_merge($brigade, $_POST);

            $validator = new Validator($form_data);
            $validator->required('line_id', 'Linia jest wymagana.')
                      ->required('brigade_number', 'Numer brygady jest wymagany.');

            if ($validator->fails()) {
                $errors = $validator->getErrors();
            }

            if (empty($errors['line_id']) && empty($errors['brigade_number'])) {
                if ($form_data['line_id'] != $brigade['line_id'] || $form_data['brigade_number'] != $brigade['brigade_number']) {
                    if (Brigade::exists($form_data['line_id'], $form_data['brigade_number'], $brigade_id)) {
                        $errors['brigade_number'] = 'Brygada o tym numerze juz istnieje dla tej linii.';
                    }
                }
            }

            if (empty($errors)) {
                try {
                    Brigade::update($brigade_id, [
                        'line_id' => $form_data['line_id'],
                        'brigade_number' => $form_data['brigade_number'],
                        'default_vehicle_type' => !empty($form_data['default_vehicle_type']) ? $form_data['default_vehicle_type'] : null,
                        'description' => !empty($form_data['description']) ? $form_data['description'] : null,
                        'active' => isset($form_data['active']) ? 'true' : 'false'
                    ]);

                    setFlashMessage('success', 'Brygada zostala zaktualizowana pomyslnie.');
                    $this->redirectTo('/admin/brigades/index.php');
                } catch (Exception $e) {
                    error_log('Error updating brigade: ' . $e->getMessage());
                    $errors['general'] = 'Wystapil blad podczas aktualizacji brygady.';
                }
            }
        }

        $this->render('admin/brigades/edit', [
            'page_title' => 'Edytuj brygade',
            'errors' => $errors,
            'form_data' => $form_data,
            'brigade' => $brigade,
            'lines' => $lines
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
            setFlashMessage('success', 'Brygada zostala usunieta pomyslnie.');
        } catch (Exception $e) {
            error_log('Error deleting brigade: ' . $e->getMessage());
            setFlashMessage('error', 'Wystapil blad podczas usuwania brygady.');
        }

        $this->redirectTo('/admin/brigades/index.php');
    }
}
