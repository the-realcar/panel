<?php

class AdminPlatformsController extends Controller {
    public function index() {
        requireLogin();

        $rbac = new RBAC();
        if (!$rbac->can('platforms', 'read')) {
            setFlashMessage('error', 'Brak uprawnien do przegladania platform.');
            $this->redirectTo('/index.php');
        }

        $stop_id = $_GET['stop_id'] ?? null;

        if (!$stop_id) {
            setFlashMessage('error', 'Nie wybrano przystanku.');
            $this->redirectTo('/admin/stops/index.php');
        }

        $stop = Stop::findByStopId($stop_id);
        if (!$stop) {
            setFlashMessage('error', 'Przystanek nie istnieje.');
            $this->redirectTo('/admin/stops/index.php');
        }

        $platforms = Platform::listByStop($stop['id']);

        $this->render('admin/platforms/index', [
            'page_title' => 'Platformy - ' . $stop['name'],
            'stop' => $stop,
            'platforms' => $platforms
        ]);
    }

    public function create() {
        requireLogin();

        $rbac = new RBAC();
        if (!$rbac->can('platforms', 'create')) {
            setFlashMessage('error', 'Brak uprawnien do tworzenia platform.');
            $this->redirectTo('/index.php');
        }

        $stop_id = $_GET['stop_id'] ?? $_POST['stop_id'] ?? null;

        if (!$stop_id) {
            setFlashMessage('error', 'Nie wybrano przystanku.');
            $this->redirectTo('/admin/stops/index.php');
        }

        $stop = Stop::findByStopId($stop_id);
        if (!$stop) {
            setFlashMessage('error', 'Przystanek nie istnieje.');
            $this->redirectTo('/admin/stops/index.php');
        }

        $errors = [];
        $form_data = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/admin/platforms/create.php?stop_id=' . urlencode($stop_id));
            }

            $form_data = $_POST;

            $validator = new Validator($form_data);
            $validator->required('platform_number', 'Numer platformy jest wymagany.');

            if ($validator->fails()) {
                $errors = $validator->getErrors();
            }

            if (empty($errors)) {
                if (Platform::exists($stop['id'], $form_data['platform_number'])) {
                    $errors['platform_number'] = 'Platforma o tym numerze juz istnieje na tym przystanku.';
                }
            }

            if (empty($errors)) {
                try {
                    Platform::create([
                        'stop_id' => $stop['id'],
                        'platform_number' => $form_data['platform_number'],
                        'description' => !empty($form_data['description']) ? $form_data['description'] : null
                    ]);

                    setFlashMessage('success', 'Platforma zostala dodana pomyslnie.');
                    $this->redirectTo('/admin/platforms/index.php?stop_id=' . urlencode($stop_id));
                } catch (Exception $e) {
                    error_log('Error creating platform: ' . $e->getMessage());
                    $errors['general'] = 'Wystapil blad podczas tworzenia platformy.';
                }
            }
        }

        $this->render('admin/platforms/create', [
            'page_title' => 'Dodaj platformę - ' . $stop['name'],
            'stop' => $stop,
            'errors' => $errors,
            'form_data' => $form_data
        ]);
    }

    public function edit() {
        requireLogin();

        $rbac = new RBAC();
        if (!$rbac->can('platforms', 'update')) {
            setFlashMessage('error', 'Brak uprawnien do edycji platform.');
            $this->redirectTo('/index.php');
        }

        $id = $_GET['id'] ?? $_POST['id'] ?? null;

        if (!$id) {
            setFlashMessage('error', 'Nie podano ID platformy.');
            $this->redirectTo('/admin/stops/index.php');
        }

        $platform = Platform::find($id);
        if (!$platform) {
            setFlashMessage('error', 'Platforma nie istnieje.');
            $this->redirectTo('/admin/stops/index.php');
        }

        $stop = Stop::find($platform['stop_id']);
        if (!$stop) {
            setFlashMessage('error', 'Przystanek nie istnieje.');
            $this->redirectTo('/admin/stops/index.php');
        }

        $errors = [];
        $form_data = $platform;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/admin/platforms/edit.php?id=' . (int)$id);
            }

            $form_data = $_POST;
            $form_data['id'] = $id;

            $validator = new Validator($form_data);
            $validator->required('platform_number', 'Numer platformy jest wymagany.');

            if ($validator->fails()) {
                $errors = $validator->getErrors();
            }

            if (empty($errors)) {
                if ($form_data['platform_number'] !== $platform['platform_number']) {
                    if (Platform::exists($stop['id'], $form_data['platform_number'])) {
                        $errors['platform_number'] = 'Platforma o tym numerze juz istnieje na tym przystanku.';
                    }
                }
            }

            if (empty($errors)) {
                $changes = false;
                if ($form_data['platform_number'] !== $platform['platform_number'] ||
                    ($form_data['description'] ?? '') !== ($platform['description'] ?? '')) {
                    $changes = true;
                }

                if (!$changes) {
                    setFlashMessage('info', 'Nie wprowadzono zadnych zmian.');
                    $this->redirectTo('/admin/platforms/index.php?stop_id=' . urlencode($stop['stop_id']));
                }

                try {
                    Platform::update($id, [
                        'platform_number' => $form_data['platform_number'],
                        'description' => !empty($form_data['description']) ? $form_data['description'] : null
                    ]);

                    setFlashMessage('success', 'Platforma zostala zaktualizowana pomyslnie.');
                    $this->redirectTo('/admin/platforms/index.php?stop_id=' . urlencode($stop['stop_id']));
                } catch (Exception $e) {
                    error_log('Error updating platform: ' . $e->getMessage());
                    $errors['general'] = 'Wystapil blad podczas aktualizacji platformy.';
                }
            }
        }

        $this->render('admin/platforms/edit', [
            'page_title' => 'Edytuj platformę - ' . $stop['name'],
            'stop' => $stop,
            'platform' => $platform,
            'errors' => $errors,
            'form_data' => $form_data
        ]);
    }

    public function delete() {
        requireLogin();

        $rbac = new RBAC();
        if (!$rbac->can('platforms', 'delete')) {
            setFlashMessage('error', 'Brak uprawnien do usuwania platform.');
            $this->redirectTo('/index.php');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            setFlashMessage('error', 'Nieprawidlowe zadanie.');
            $this->redirectTo('/admin/stops/index.php');
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlashMessage('error', 'Nieprawidlowy token CSRF.');
            $this->redirectTo('/admin/stops/index.php');
        }

        $id = $_POST['id'] ?? null;

        if (!$id) {
            setFlashMessage('error', 'Nie podano ID platformy.');
            $this->redirectTo('/admin/stops/index.php');
        }

        $platform = Platform::find($id);
        if (!$platform) {
            setFlashMessage('error', 'Platforma nie istnieje.');
            $this->redirectTo('/admin/stops/index.php');
        }

        $stop = Stop::find($platform['stop_id']);

        if (Platform::isUsedInRoutes($id)) {
            setFlashMessage('error', 'Nie mozna usunac platformy, poniewaz jest uzywana w trasach.');
            if ($stop) {
                $this->redirectTo('/admin/platforms/index.php?stop_id=' . urlencode($stop['stop_id']));
            } else {
                $this->redirectTo('/admin/stops/index.php');
            }
        }

        try {
            Platform::delete($id);
            setFlashMessage('success', 'Platforma zostala usunieta pomyslnie.');
        } catch (Exception $e) {
            error_log('Error deleting platform: ' . $e->getMessage());
            setFlashMessage('error', 'Wystapil blad podczas usuwania platformy.');
        }

        if ($stop) {
            $this->redirectTo('/admin/platforms/index.php?stop_id=' . urlencode($stop['stop_id']));
        } else {
            $this->redirectTo('/admin/stops/index.php');
        }
    }
}
