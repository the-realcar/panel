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
                    Vehicle::create([
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
            setFlashMessage('success', 'Pojazd zostal usuniety pomyslnie.');
        } catch (Exception $e) {
            error_log('Error deleting vehicle: ' . $e->getMessage());
            setFlashMessage('error', 'Nie mozna usunac pojazdu. Moze byc uzywany w innych miejscach systemu.');
        }

        $this->redirectTo('/admin/vehicles/index.php');
    }
}
