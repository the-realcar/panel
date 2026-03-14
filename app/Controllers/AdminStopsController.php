<?php

class AdminStopsController extends Controller {
    public function index() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('stops', 'read');

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $per_page = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $per_page;

        // Handle city AJAX actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['city_action'])) {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                $this->jsonError('Nieprawidlowy token CSRF.');
            }
            $this->handleCityAction($_POST['city_action']);
            return;
        }

        $total_items = Stop::countAll();
        $total_pages = (int)ceil($total_items / $per_page);
        $stops = Stop::listAll($per_page, $offset);
        $cities = City::listAll();

        $this->render('admin/stops/index', [
            'page_title'  => 'Zarządzanie przystankami',
            'stops'       => $stops,
            'cities'      => $cities,
            'page'        => $page,
            'total_pages' => $total_pages,
            'rbac'        => $rbac
        ]);
    }

    private function handleCityAction($action) {
        $id = (int)($_POST['id'] ?? 0);

        if ($action === 'create') {
            $name = trim($_POST['name'] ?? '');
            if (empty($name)) { $this->jsonError('Nazwa miasta jest wymagana.'); }
            if (City::existsByName($name)) { $this->jsonError('Miasto o tej nazwie już istnieje.'); }
            $new_id = City::create(['name' => $name, 'active' => 'on']);
            AuditLog::log('city.create', 'cities', $new_id, null, ['name' => $name]);
            $this->jsonSuccess(['id' => $new_id, 'name' => e($name)]);
        }

        if ($action === 'update') {
            if (!$id) { $this->jsonError('Nieprawidlowy ID.'); }
            $name = trim($_POST['name'] ?? '');
            if (empty($name)) { $this->jsonError('Nazwa miasta jest wymagana.'); }
            if (City::existsByName($name, $id)) { $this->jsonError('Miasto o tej nazwie już istnieje.'); }
            $city = City::find($id);
            if (!$city) { $this->jsonError('Miasto nie istnieje.'); }
            City::update($id, ['name' => $name, 'active' => 'on']);
            AuditLog::log('city.update', 'cities', $id, ['name' => $city['name']], ['name' => $name]);
            $this->jsonSuccess(['id' => $id, 'name' => e($name)]);
        }

        if ($action === 'delete') {
            if (!$id) { $this->jsonError('Nieprawidlowy ID.'); }
            if (City::getStopsCount($id) > 0) { $this->jsonError('Nie można usunąć miasta z przypisanymi przystankami.'); }
            City::delete($id);
            AuditLog::log('city.delete', 'cities', $id, null, null);
            $this->jsonSuccess([]);
        }

        $this->jsonError('Nieznana akcja.');
    }

    private function jsonSuccess($data) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    private function jsonError($msg) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $msg]);
        exit;
    }

    public function create() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('stops', 'create');

        $errors = [];
        $form_data = ['active' => 'on'];
        $cities = City::listActive();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/admin/stops/create.php');
            }

            $form_data = $_POST;

            $validator = new Validator($form_data);
            $validator->required('name', 'Nazwa przystanku jest wymagana.');

            if ($validator->fails()) {
                $errors = $validator->getErrors();
            }

            if (empty($errors)) {
                try {
                    $new_stop_id = Stop::create([
                        'city_id'   => !empty($form_data['city_id']) ? $form_data['city_id'] : null,
                        'name'      => $form_data['name'],
                        'opis'      => !empty($form_data['opis']) ? $form_data['opis'] : null,
                        'status_nz' => isset($form_data['status_nz']) ? 'true' : 'false',
                        'active'    => isset($form_data['active']) ? 'true' : 'false'
                    ]);
                    AuditLog::log('stop.create', 'stops', $new_stop_id, null, ['name' => $form_data['name']]);

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
            'errors'     => $errors,
            'form_data'  => $form_data,
            'cities'     => $cities,
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
        $cities = City::listActive();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/admin/stops/edit.php?id=' . $stop_id);
            }

            $form_data = array_merge($stop, $_POST);

            $validator = new Validator($form_data);
            $validator->required('name', 'Nazwa przystanku jest wymagana.');

            if ($validator->fails()) {
                $errors = $validator->getErrors();
            }

            if (empty($errors)) {
                try {
                    Stop::update($stop_id, [
                        'city_id'   => !empty($form_data['city_id']) ? $form_data['city_id'] : null,
                        'name'      => $form_data['name'],
                        'opis'      => !empty($form_data['opis']) ? $form_data['opis'] : null,
                        'status_nz' => isset($form_data['status_nz']) ? 'true' : 'false',
                        'active'    => isset($form_data['active']) ? 'true' : 'false'
                    ]);
                    AuditLog::log('stop.update', 'stops', $stop_id, ['name' => $stop['name']], ['name' => $form_data['name']]);

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
            'errors'     => $errors,
            'form_data'  => $form_data,
            'stop'       => $stop,
            'cities'     => $cities,
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

        $platforms_count = Stop::getPlatformsCount($stop_id);
        if ($platforms_count > 0) {
            setFlashMessage('error', 'Nie mozna usunac przystanku, ktory ma przypisane stanowiska.');
            $this->redirectTo('/admin/stops/index.php');
        }

        try {
            Stop::delete($stop_id);
            AuditLog::log('stop.delete', 'stops', $stop_id, ['name' => $stop['name']], null);
            setFlashMessage('success', 'Przystanek zostal usuniety pomyslnie.');
        } catch (Exception $e) {
            error_log('Error deleting stop: ' . $e->getMessage());
            setFlashMessage('error', 'Wystapil blad podczas usuwania przystanku.');
        }

        $this->redirectTo('/admin/stops/index.php');
    }
}
