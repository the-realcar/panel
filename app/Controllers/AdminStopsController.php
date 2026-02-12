<?php

class AdminStopsController extends Controller {
    public function index() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('stops', 'read');

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $per_page = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $per_page;

        $total_items = Stop::countAll();
        $total_pages = (int)ceil($total_items / $per_page);
        $stops = Stop::listAll($per_page, $offset);

        $this->render('admin/stops/index', [
            'page_title' => 'Zarzadzanie przystankami',
            'stops' => $stops,
            'page' => $page,
            'total_pages' => $total_pages,
            'rbac' => $rbac
        ]);
    }

    public function create() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('stops', 'create');

        $errors = [];
        $form_data = ['active' => 'on'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/admin/stops/create.php');
            }

            $form_data = $_POST;

            $validator = new Validator($form_data);
            $validator->required('stop_id', 'Identyfikator przystanku jest wymagany.')
                      ->required('name', 'Nazwa przystanku jest wymagana.');

            if (!empty($form_data['latitude'])) {
                $validator->numeric('latitude', 'Szerokosc geograficzna musi byc liczba.');
            }

            if (!empty($form_data['longitude'])) {
                $validator->numeric('longitude', 'Dlugosc geograficzna musi byc liczba.');
            }

            if ($validator->fails()) {
                $errors = $validator->getErrors();
            }

            if (empty($errors['stop_id']) && Stop::existsByStopId($form_data['stop_id'])) {
                $errors['stop_id'] = 'Przystanek o tym identyfikatorze juz istnieje.';
            }

            if (empty($errors)) {
                try {
                    Stop::create([
                        'stop_id' => $form_data['stop_id'],
                        'name' => $form_data['name'],
                        'location_description' => !empty($form_data['location_description']) ? $form_data['location_description'] : null,
                        'latitude' => !empty($form_data['latitude']) ? $form_data['latitude'] : null,
                        'longitude' => !empty($form_data['longitude']) ? $form_data['longitude'] : null,
                        'active' => isset($form_data['active']) ? 'true' : 'false'
                    ]);

                    setFlashMessage('success', 'Przystanek zostal dodany pomyslnie.');
                    $this->redirectTo('/admin/stops/index.php');
                } catch (Exception $e) {
                    error_log('Error creating stop: ' . $e->getMessage());
                    $errors['general'] = 'Wystapil blad podczas dodawania przystanku.';
                }
            }
        }

        $this->render('admin/stops/create', [
            'page_title' => 'Dodaj przystanek',
            'errors' => $errors,
            'form_data' => $form_data
        ]);
    }

    public function edit() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('stops', 'update');

        $stop_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$stop_id) {
            setFlashMessage('error', 'Nieprawidlowy ID przystanku.');
            $this->redirectTo('/admin/stops/index.php');
        }

        $stop = Stop::find($stop_id);
        if (!$stop) {
            setFlashMessage('error', 'Przystanek nie zostal znaleziony.');
            $this->redirectTo('/admin/stops/index.php');
        }

        $errors = [];
        $form_data = $stop;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/admin/stops/edit.php?id=' . $stop_id);
            }

            $form_data = array_merge($stop, $_POST);

            $validator = new Validator($form_data);
            $validator->required('stop_id', 'Identyfikator przystanku jest wymagany.')
                      ->required('name', 'Nazwa przystanku jest wymagana.');

            if (!empty($form_data['latitude'])) {
                $validator->numeric('latitude', 'Szerokosc geograficzna musi byc liczba.');
            }

            if (!empty($form_data['longitude'])) {
                $validator->numeric('longitude', 'Dlugosc geograficzna musi byc liczba.');
            }

            if ($validator->fails()) {
                $errors = $validator->getErrors();
            }

            if (empty($errors['stop_id']) && $form_data['stop_id'] !== $stop['stop_id']) {
                if (Stop::existsByStopId($form_data['stop_id'], $stop_id)) {
                    $errors['stop_id'] = 'Przystanek o tym identyfikatorze juz istnieje.';
                }
            }

            if (empty($errors)) {
                try {
                    Stop::update($stop_id, [
                        'stop_id' => $form_data['stop_id'],
                        'name' => $form_data['name'],
                        'location_description' => !empty($form_data['location_description']) ? $form_data['location_description'] : null,
                        'latitude' => !empty($form_data['latitude']) ? $form_data['latitude'] : null,
                        'longitude' => !empty($form_data['longitude']) ? $form_data['longitude'] : null,
                        'active' => isset($form_data['active']) ? 'true' : 'false'
                    ]);

                    setFlashMessage('success', 'Przystanek zostal zaktualizowany pomyslnie.');
                    $this->redirectTo('/admin/stops/index.php');
                } catch (Exception $e) {
                    error_log('Error updating stop: ' . $e->getMessage());
                    $errors['general'] = 'Wystapil blad podczas aktualizacji przystanku.';
                }
            }
        }

        $this->render('admin/stops/edit', [
            'page_title' => 'Edytuj przystanek',
            'errors' => $errors,
            'form_data' => $form_data,
            'stop' => $stop
        ]);
    }

    public function delete() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('stops', 'delete');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            setFlashMessage('error', 'Nieprawidlowe zadanie.');
            $this->redirectTo('/admin/stops/index.php');
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlashMessage('error', 'Nieprawidlowy token CSRF.');
            $this->redirectTo('/admin/stops/index.php');
        }

        $stop_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if (!$stop_id) {
            setFlashMessage('error', 'Nieprawidlowy ID przystanku.');
            $this->redirectTo('/admin/stops/index.php');
        }

        $stop = Stop::find($stop_id);
        if (!$stop) {
            setFlashMessage('error', 'Przystanek nie zostal znaleziony.');
            $this->redirectTo('/admin/stops/index.php');
        }

        // Check if stop has platforms
        $platforms_count = Stop::getPlatformsCount($stop_id);
        if ($platforms_count > 0) {
            setFlashMessage('error', 'Nie mozna usunac przystanku, ktory ma przypisane stanowiska. Usun najpierw stanowiska.');
            $this->redirectTo('/admin/stops/index.php');
        }

        try {
            Stop::delete($stop_id);
            setFlashMessage('success', 'Przystanek zostal usuniety pomyslnie.');
        } catch (Exception $e) {
            error_log('Error deleting stop: ' . $e->getMessage());
            setFlashMessage('error', 'Wystapil blad podczas usuwania przystanku.');
        }

        $this->redirectTo('/admin/stops/index.php');
    }
}
