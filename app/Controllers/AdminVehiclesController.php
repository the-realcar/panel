<?php

class AdminVehiclesController extends Controller {
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
        $form_data = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/admin/vehicles/create.php');
            }

            $form_data = $_POST;

            $validator = new Validator($form_data);
            $validator->required('vehicle_number', 'Numer pojazdu jest wymagany.')
                      ->required('vehicle_type', 'Typ pojazdu jest wymagany.')
                      ->required('status', 'Status jest wymagany.');

            if (!empty($form_data['manufacture_year'])) {
                $validator->integer('manufacture_year', 'Rok produkcji musi byc liczba calkowita.')
                          ->min('manufacture_year', 1900, 'Rok produkcji musi byc wiekszy niz 1900.')
                          ->max('manufacture_year', date('Y') + 1, 'Rok produkcji nie moze byc w przyszlosci.');
            }

            if (!empty($form_data['capacity'])) {
                $validator->integer('capacity', 'Pojemnosc musi byc liczba calkowita.')
                          ->min('capacity', 1, 'Pojemnosc musi byc wieksza niz 0.');
            }

            if (!empty($form_data['last_inspection'])) {
                $validator->date('last_inspection', 'Y-m-d', 'Data ostatniego przegladu jest nieprawidlowa.');
            }

            if ($validator->fails()) {
                $errors = $validator->getErrors();
            }

            if (empty($errors['vehicle_number']) && Vehicle::existsByNumber($form_data['vehicle_number'])) {
                $errors['vehicle_number'] = 'Pojazd o tym numerze juz istnieje.';
            }

            if (!empty($form_data['registration_plate']) && empty($errors['registration_plate'])) {
                if (Vehicle::existsByPlate($form_data['registration_plate'])) {
                    $errors['registration_plate'] = 'Pojazd o tej rejestracji juz istnieje.';
                }
            }

            if (empty($errors)) {
                try {
                    Vehicle::create([
                        'vehicle_number' => $form_data['vehicle_number'],
                        'registration_plate' => !empty($form_data['registration_plate']) ? $form_data['registration_plate'] : null,
                        'vehicle_type' => $form_data['vehicle_type'],
                        'model' => !empty($form_data['model']) ? $form_data['model'] : null,
                        'manufacture_year' => !empty($form_data['manufacture_year']) ? (int)$form_data['manufacture_year'] : null,
                        'capacity' => !empty($form_data['capacity']) ? (int)$form_data['capacity'] : null,
                        'status' => $form_data['status'],
                        'last_inspection' => !empty($form_data['last_inspection']) ? $form_data['last_inspection'] : null
                    ]);

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
            $validator->required('vehicle_number', 'Numer pojazdu jest wymagany.')
                      ->required('vehicle_type', 'Typ pojazdu jest wymagany.')
                      ->required('status', 'Status jest wymagany.');

            if (!empty($form_data['manufacture_year'])) {
                $validator->integer('manufacture_year', 'Rok produkcji musi byc liczba calkowita.')
                          ->min('manufacture_year', 1900, 'Rok produkcji musi byc wiekszy niz 1900.')
                          ->max('manufacture_year', date('Y') + 1, 'Rok produkcji nie moze byc w przyszlosci.');
            }

            if (!empty($form_data['capacity'])) {
                $validator->integer('capacity', 'Pojemnosc musi byc liczba calkowita.')
                          ->min('capacity', 1, 'Pojemnosc musi byc wieksza niz 0.');
            }

            if (!empty($form_data['last_inspection'])) {
                $validator->date('last_inspection', 'Y-m-d', 'Data ostatniego przegladu jest nieprawidlowa.');
            }

            if ($validator->fails()) {
                $errors = $validator->getErrors();
            }

            if (empty($errors['vehicle_number']) && $form_data['vehicle_number'] !== $vehicle['vehicle_number']) {
                if (Vehicle::existsByNumber($form_data['vehicle_number'], $vehicle_id)) {
                    $errors['vehicle_number'] = 'Pojazd o tym numerze juz istnieje.';
                }
            }

            if (!empty($form_data['registration_plate']) && empty($errors['registration_plate'])) {
                if ($form_data['registration_plate'] !== $vehicle['registration_plate']) {
                    if (Vehicle::existsByPlate($form_data['registration_plate'], $vehicle_id)) {
                        $errors['registration_plate'] = 'Pojazd o tej rejestracji juz istnieje.';
                    }
                }
            }

            if (empty($errors)) {
                try {
                    Vehicle::update($vehicle_id, [
                        'vehicle_number' => $form_data['vehicle_number'],
                        'registration_plate' => !empty($form_data['registration_plate']) ? $form_data['registration_plate'] : null,
                        'vehicle_type' => $form_data['vehicle_type'],
                        'model' => !empty($form_data['model']) ? $form_data['model'] : null,
                        'manufacture_year' => !empty($form_data['manufacture_year']) ? (int)$form_data['manufacture_year'] : null,
                        'capacity' => !empty($form_data['capacity']) ? (int)$form_data['capacity'] : null,
                        'status' => $form_data['status'],
                        'last_inspection' => !empty($form_data['last_inspection']) ? $form_data['last_inspection'] : null
                    ]);

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

        if (!verifyCsrfToken($_GET['csrf_token'] ?? '')) {
            setFlashMessage('error', 'Nieprawidlowy token CSRF.');
            $this->redirectTo('/admin/vehicles/index.php');
        }

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

        try {
            Vehicle::delete($vehicle_id);
            setFlashMessage('success', 'Pojazd zostal usuniety pomyslnie.');
        } catch (Exception $e) {
            error_log('Error deleting vehicle: ' . $e->getMessage());
            setFlashMessage('error', 'Nie mozna usunac pojazdu. Moze byc uzywany w innych miejscach systemu.');
        }

        $this->redirectTo('/admin/vehicles/index.php');
    }
}
