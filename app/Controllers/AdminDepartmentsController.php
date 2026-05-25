<?php

class AdminDepartmentsController extends Controller {
    public function index(): void {
        requireLogin();

        $rbac = new RBAC();
        if (!$rbac->isAdmin()) {
            setFlashMessage('error', 'Brak uprawnien do zarzadzania dzialami.');
            $this->redirectTo('/index.php');
        }

        $errors = [];
        $edit_department = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/admin/departments/index.php');
            }

            $action = $_POST['department_action'] ?? 'create';
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $name = trim((string)($_POST['name'] ?? ''));
            $description = trim((string)($_POST['description'] ?? ''));
            $active = isset($_POST['active']);

            if (in_array($action, ['create', 'update'], true)) {
                if ($name === '') {
                    $errors['name'] = 'Nazwa dzialu jest wymagana.';
                } elseif (Department::existsByName($name, $action === 'update' ? $id : null)) {
                    $errors['name'] = 'Dzial o tej nazwie juz istnieje.';
                }
            }

            if (empty($errors)) {
                try {
                    if ($action === 'create') {
                        $new_id = Department::create([
                            'name' => $name,
                            'description' => $description !== '' ? $description : null,
                            'active' => $active
                        ]);
                        AuditLog::log('department.create', 'departments', (int)$new_id, null, ['name' => $name]);
                        setFlashMessage('success', 'Dzial zostal dodany.');
                    } elseif ($action === 'update' && $id > 0) {
                        $existing = Department::find($id);
                        if (!$existing) {
                            setFlashMessage('error', 'Wybrany dzial nie istnieje.');
                            $this->redirectTo('/admin/departments/index.php');
                        }

                        Department::update($id, [
                            'name' => $name,
                            'description' => $description !== '' ? $description : null,
                            'active' => $active
                        ]);
                        AuditLog::log('department.update', 'departments', $id, $existing, ['name' => $name]);
                        setFlashMessage('success', 'Dzial zostal zaktualizowany.');
                    } elseif ($action === 'delete' && $id > 0) {
                        $existing = Department::find($id);
                        if (!$existing) {
                            setFlashMessage('error', 'Wybrany dzial nie istnieje.');
                            $this->redirectTo('/admin/departments/index.php');
                        }

                        if (Department::getPositionsCount($id) > 0) {
                            setFlashMessage('error', 'Nie mozna usunac dzialu z przypisanymi stanowiskami.');
                            $this->redirectTo('/admin/departments/index.php');
                        }

                        Department::delete($id);
                        AuditLog::log('department.delete', 'departments', $id, $existing, null);
                        setFlashMessage('success', 'Dzial zostal usuniety.');
                    }

                    $this->redirectTo('/admin/departments/index.php');
                } catch (Exception $e) {
                    error_log('Error saving department: ' . $e->getMessage());
                    $errors['general'] = 'Wystapil blad podczas zapisu dzialu.';
                }
            }

            if ($action === 'update' && $id > 0) {
                $edit_department = [
                    'id' => $id,
                    'name' => $name,
                    'description' => $description,
                    'active' => $active
                ];
            }
        }

        if ($edit_department === null && isset($_GET['edit'])) {
            $edit_department = Department::find((int)$_GET['edit']);
        }

        $this->render('admin/departments/index', [
            'page_title' => 'Dzialy',
            'departments' => Department::listAll(),
            'edit_department' => $edit_department,
            'errors' => $errors
        ]);
    }
}