<?php

class AdminDistrictsController extends Controller {
    public function index(): void {
        requireLogin();

        $rbac = new RBAC();
        if (!$rbac->isAdmin()) {
            setFlashMessage('error', 'Brak uprawnien do zarzadzania dzielnicami.');
            $this->redirectTo('/index.php');
        }

        $errors = [];
        $edit_district = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/admin/districts/index.php');
            }

            if (!District::isAvailable()) {
                setFlashMessage('error', 'Tabela districts nie jest jeszcze dostepna. Uruchom migracje SQL.');
                $this->redirectTo('/admin/districts/index.php');
            }

            $action = $_POST['district_action'] ?? 'create';
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $city_id = isset($_POST['city_id']) ? (int)$_POST['city_id'] : 0;
            $name = trim((string)($_POST['name'] ?? ''));
            $active = isset($_POST['active']);

            if (in_array($action, ['create', 'update'], true)) {
                if ($name === '') {
                    $errors['name'] = 'Nazwa dzielnicy jest wymagana.';
                }

                if ($city_id <= 0 || City::find($city_id) === null) {
                    $errors['city_id'] = 'Wybierz poprawne miasto.';
                }

                if (empty($errors) && District::existsByNameAndCity($name, $city_id, $action === 'update' ? $id : null)) {
                    $errors['name'] = 'Taka dzielnica juz istnieje w wybranym miescie.';
                }
            }

            if (empty($errors)) {
                try {
                    if ($action === 'create') {
                        $new_id = District::create([
                            'name' => $name,
                            'city_id' => $city_id,
                            'active' => $active
                        ]);
                        AuditLog::log('district.create', 'districts', (int)$new_id, null, ['name' => $name, 'city_id' => $city_id]);
                        setFlashMessage('success', 'Dzielnica zostala dodana.');
                    } elseif ($action === 'update' && $id > 0) {
                        $existing = District::find($id);
                        if (!$existing) {
                            setFlashMessage('error', 'Wybrana dzielnica nie istnieje.');
                            $this->redirectTo('/admin/districts/index.php');
                        }

                        District::update($id, [
                            'name' => $name,
                            'city_id' => $city_id,
                            'active' => $active
                        ]);
                        AuditLog::log('district.update', 'districts', $id, $existing, ['name' => $name, 'city_id' => $city_id]);
                        setFlashMessage('success', 'Dzielnica zostala zaktualizowana.');
                    } elseif ($action === 'delete' && $id > 0) {
                        $existing = District::find($id);
                        if (!$existing) {
                            setFlashMessage('error', 'Wybrana dzielnica nie istnieje.');
                            $this->redirectTo('/admin/districts/index.php');
                        }

                        District::delete($id);
                        AuditLog::log('district.delete', 'districts', $id, $existing, null);
                        setFlashMessage('success', 'Dzielnica zostala usunieta.');
                    }

                    $this->redirectTo('/admin/districts/index.php');
                } catch (Exception $e) {
                    error_log('Error saving district: ' . $e->getMessage());
                    $errors['general'] = 'Wystapil blad podczas zapisu dzielnicy.';
                }
            }

            if ($action === 'update' && $id > 0) {
                $edit_district = [
                    'id' => $id,
                    'name' => $name,
                    'city_id' => $city_id,
                    'active' => $active
                ];
            }
        }

        if ($edit_district === null && isset($_GET['edit'])) {
            $edit_district = District::find((int)$_GET['edit']);
        }

        $this->render('admin/districts/index', [
            'page_title' => 'Dzielnice',
            'districts_available' => District::isAvailable(),
            'districts' => District::listAll(),
            'cities' => City::listAll(),
            'edit_district' => $edit_district,
            'errors' => $errors
        ]);
    }
}