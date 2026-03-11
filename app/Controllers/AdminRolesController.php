<?php

class AdminRolesController extends Controller {
    public function index() {
        requireLogin();

        $rbac = new RBAC();
        if (!$rbac->isAdmin()) {
            setFlashMessage('error', 'Brak uprawnien do zarzadzania rolami.');
            $this->redirectTo('/index.php');
        }

        $roles = Role::listAll();

        $this->render('admin/roles/index', [
            'page_title' => 'Zarzadzanie rolami',
            'roles' => $roles
        ]);
    }

    public function edit() {
        requireLogin();

        $rbac = new RBAC();
        if (!$rbac->isAdmin()) {
            setFlashMessage('error', 'Brak uprawnien do zarzadzania rolami.');
            $this->redirectTo('/index.php');
        }

        $role_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$role_id) {
            setFlashMessage('error', 'Nieprawidlowe ID roli.');
            $this->redirectTo('/admin/roles/index.php');
        }

        $role = Role::find($role_id);
        if (!$role) {
            setFlashMessage('error', 'Rola nie zostala znaleziona.');
            $this->redirectTo('/admin/roles/index.php');
        }

        $errors = [];
        $permission_definition = Role::getPermissionDefinition();
        $selected_permissions = json_decode($role['permissions'] ?? '{}', true);
        if (!is_array($selected_permissions)) {
            $selected_permissions = [];
        }

        $form = [
            'name' => $role['name'],
            'description' => $role['description'] ?? ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/admin/roles/edit.php?id=' . $role_id);
            }

            $form['name'] = trim($_POST['name'] ?? '');
            $form['description'] = trim($_POST['description'] ?? '');
            $posted_permissions = $_POST['permissions'] ?? [];

            $validator = new Validator($form);
            $validator->required('name', 'Nazwa roli jest wymagana.');

            if ($validator->fails()) {
                $errors = $validator->getErrors();
            }

            if (empty($errors['name']) && Role::existsByName($form['name'], $role_id)) {
                $errors['name'] = 'Rola o tej nazwie juz istnieje.';
            }

            $selected_permissions = [];
            foreach ($permission_definition as $resource => $config) {
                foreach ($config['actions'] as $action) {
                    if (!empty($posted_permissions[$resource][$action])) {
                        if (!isset($selected_permissions[$resource])) {
                            $selected_permissions[$resource] = [];
                        }

                        $selected_permissions[$resource][] = $action;
                    }
                }
            }

            if (empty($selected_permissions)) {
                $errors['permissions'] = 'Wybierz przynajmniej jedno uprawnienie.';
            }

            if (empty($errors)) {
                try {
                    Role::update($role_id, [
                        'name' => $form['name'],
                        'description' => $form['description'],
                        'permissions' => json_encode($selected_permissions, JSON_UNESCAPED_UNICODE)
                    ]);

                    User::refreshSessionAuthorizationForUser(getCurrentUserId());
                    setFlashMessage('success', 'Rola zostala zaktualizowana.');
                    $this->redirectTo('/admin/roles/index.php');
                } catch (Exception $e) {
                    error_log('Error updating role: ' . $e->getMessage());
                    $errors['general'] = 'Wystapil blad podczas aktualizacji roli.';
                }
            }
        }

        $this->render('admin/roles/edit', [
            'page_title' => 'Edytuj role',
            'role' => $role,
            'form' => $form,
            'permission_definition' => $permission_definition,
            'selected_permissions' => $selected_permissions,
            'errors' => $errors,
            'role_id' => $role_id
        ]);
    }
}
