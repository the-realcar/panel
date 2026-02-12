<?php

class AdminLinesController extends Controller {
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
        $form_data = ['active' => 'on'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/admin/lines/create.php');
            }

            $form_data = $_POST;

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
                    Line::create([
                        'line_number' => $form_data['line_number'],
                        'name' => $form_data['name'],
                        'route_description' => !empty($form_data['route_description']) ? $form_data['route_description'] : null,
                        'line_type' => $form_data['line_type'],
                        'active' => isset($form_data['active']) ? 'true' : 'false'
                    ]);

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
            setFlashMessage('success', 'Linia zostala usunieta pomyslnie.');
        } catch (Exception $e) {
            error_log('Error deleting line: ' . $e->getMessage());
            setFlashMessage('error', 'Nie mozna usunac linii. Moze byc uzywana w innych miejscach systemu.');
        }

        $this->redirectTo('/admin/lines/index.php');
    }
}
