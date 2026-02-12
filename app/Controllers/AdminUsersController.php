<?php

class AdminUsersController extends Controller {
    public function index() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('users', 'read');

        $status_filter = $_GET['status'] ?? '';
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $per_page = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $per_page;

        $total_items = User::countByStatus($status_filter);
        $total_pages = (int)ceil($total_items / $per_page);
        $users = User::listWithRolesPositions($status_filter, $per_page, $offset);

        $this->render('admin/users/index', [
            'page_title' => 'Zarzadzanie uzytkownikami',
            'users' => $users,
            'status_filter' => $status_filter,
            'page' => $page,
            'total_pages' => $total_pages,
            'rbac' => $rbac
        ]);
    }

    public function create() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('users', 'create');

        $errors = [];
        $form = [
            'username' => '',
            'email' => '',
            'first_name' => '',
            'last_name' => '',
            'active' => true,
            'discord_id' => '',
            'roblox_id' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/admin/users/create.php');
            }

            $form['username'] = trim($_POST['username'] ?? '');
            $form['email'] = trim($_POST['email'] ?? '');
            $form['first_name'] = trim($_POST['first_name'] ?? '');
            $form['last_name'] = trim($_POST['last_name'] ?? '');
            $form['active'] = isset($_POST['active']);
            $form['discord_id'] = trim($_POST['discord_id'] ?? '');
            $form['roblox_id'] = trim($_POST['roblox_id'] ?? '');
            $password = $_POST['password'] ?? '';

            $validator = new Validator($_POST);
            $validator->required('username', 'Nazwa uzytkownika jest wymagana')
                      ->required('email', 'Email jest wymagany')
                      ->email('email', 'Podaj poprawny adres email')
                      ->required('password', 'Haslo jest wymagane')
                      ->minLength('password', PASSWORD_MIN_LENGTH, 'Haslo jest za krotkie');

            if ($validator->passes()) {
                if (User::findByUsername($form['username'])) {
                    $errors['username'] = 'Taki login juz istnieje.';
                }

                if (User::findByEmail($form['email'])) {
                    $errors['email'] = 'Taki email juz istnieje.';
                }

                if ($form['discord_id'] !== '' && User::findByProviderId('discord', $form['discord_id'])) {
                    $errors['discord_id'] = 'Ten Discord ID jest juz przypisany.';
                }

                if ($form['roblox_id'] !== '' && User::findByProviderId('roblox', $form['roblox_id'])) {
                    $errors['roblox_id'] = 'Ten Roblox ID jest juz przypisany.';
                }
            } else {
                $errors = $validator->getErrors();
            }

            if (empty($errors)) {
                $password_hash = password_hash($password, PASSWORD_BCRYPT);
                $data = $form;
                $data['password_hash'] = $password_hash;
                $data['discord_id'] = $form['discord_id'] !== '' ? $form['discord_id'] : null;
                $data['roblox_id'] = $form['roblox_id'] !== '' ? $form['roblox_id'] : null;

                User::create($data);
                setFlashMessage('success', 'Uzytkownik zostal utworzony.');
                $this->redirectTo('/admin/users/index.php');
            }
        }

        $this->render('admin/users/create', [
            'page_title' => 'Dodaj uzytkownika',
            'errors' => $errors,
            'form' => $form
        ]);
    }

    public function edit() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('users', 'update');

        $user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$user_id) {
            setFlashMessage('error', 'Nieprawidlowy ID uzytkownika.');
            $this->redirectTo('/admin/users/index.php');
        }

        $user = User::find($user_id);
        if (!$user) {
            setFlashMessage('error', 'Uzytkownik nie zostal znaleziony.');
            $this->redirectTo('/admin/users/index.php');
        }

        $errors = [];
        $form = [
            'username' => $user['username'],
            'email' => $user['email'],
            'first_name' => $user['first_name'] ?? '',
            'last_name' => $user['last_name'] ?? '',
            'active' => (bool)$user['active'],
            'discord_id' => $user['discord_id'] ?? '',
            'roblox_id' => $user['roblox_id'] ?? ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/admin/users/edit.php?id=' . $user_id);
            }

            $form['username'] = trim($_POST['username'] ?? '');
            $form['email'] = trim($_POST['email'] ?? '');
            $form['first_name'] = trim($_POST['first_name'] ?? '');
            $form['last_name'] = trim($_POST['last_name'] ?? '');
            $form['active'] = isset($_POST['active']);
            $form['discord_id'] = trim($_POST['discord_id'] ?? '');
            $form['roblox_id'] = trim($_POST['roblox_id'] ?? '');
            $password = $_POST['password'] ?? '';

            $validator = new Validator($_POST);
            $validator->required('username', 'Nazwa uzytkownika jest wymagana')
                      ->required('email', 'Email jest wymagany')
                      ->email('email', 'Podaj poprawny adres email');

            if ($password !== '') {
                $validator->minLength('password', PASSWORD_MIN_LENGTH, 'Haslo jest za krotkie');
            }

            if ($validator->passes()) {
                $existing = User::findByUsername($form['username']);
                if ($existing && (int)$existing['id'] !== $user_id) {
                    $errors['username'] = 'Taki login juz istnieje.';
                }

                $existing = User::findByEmail($form['email']);
                if ($existing && (int)$existing['id'] !== $user_id) {
                    $errors['email'] = 'Taki email juz istnieje.';
                }

                if ($form['discord_id'] !== '') {
                    $existing = User::findByProviderId('discord', $form['discord_id']);
                    if ($existing && (int)$existing['id'] !== $user_id) {
                        $errors['discord_id'] = 'Ten Discord ID jest juz przypisany.';
                    }
                }

                if ($form['roblox_id'] !== '') {
                    $existing = User::findByProviderId('roblox', $form['roblox_id']);
                    if ($existing && (int)$existing['id'] !== $user_id) {
                        $errors['roblox_id'] = 'Ten Roblox ID jest juz przypisany.';
                    }
                }
            } else {
                $errors = $validator->getErrors();
            }

            if (empty($errors)) {
                $data = $form;
                $data['discord_id'] = $form['discord_id'] !== '' ? $form['discord_id'] : null;
                $data['roblox_id'] = $form['roblox_id'] !== '' ? $form['roblox_id'] : null;

                User::update($user_id, $data);

                if ($password !== '') {
                    $password_hash = password_hash($password, PASSWORD_BCRYPT);
                    User::updatePassword($user_id, $password_hash);
                }

                setFlashMessage('success', 'Uzytkownik zostal zaktualizowany.');
                $this->redirectTo('/admin/users/index.php');
            }
        }

        $this->render('admin/users/edit', [
            'page_title' => 'Edytuj uzytkownika',
            'errors' => $errors,
            'form' => $form,
            'user_id' => $user_id
        ]);
    }

    public function assignPosition() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('users', 'update');

        $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
        if (!$user_id) {
            setFlashMessage('error', 'Nieprawidlowy ID uzytkownika.');
            $this->redirectTo('/admin/users/index.php');
        }

        $user = User::find($user_id);
        if (!$user) {
            setFlashMessage('error', 'Uzytkownik nie zostal znaleziony.');
            $this->redirectTo('/admin/users/index.php');
        }

        $errors = [];
        $positions = Position::listActive();
        $current_positions = Position::getUserPositions($user_id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'assign') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/admin/users/assign-position.php?user_id=' . $user_id);
            }

            $position_id = isset($_POST['position_id']) ? (int)$_POST['position_id'] : 0;
            if (!$position_id) {
                $errors['position'] = 'Wybierz stanowisko.';
            }

            if (empty($errors)) {
                try {
                    if (Position::assignmentExists($user_id, $position_id)) {
                        setFlashMessage('warning', 'Uzytkownik jest juz przypisany do tego stanowiska.');
                    } else {
                        Position::assignToUser($user_id, $position_id);
                        setFlashMessage('success', 'Stanowisko zostalo przypisane pomyslnie.');
                    }

                    $this->redirectTo('/admin/users/assign-position.php?user_id=' . $user_id);
                } catch (Exception $e) {
                    error_log('Error assigning position: ' . $e->getMessage());

                    if (strpos($e->getMessage(), 'Limit') !== false || strpos($e->getMessage(), 'limit') !== false) {
                        setFlashMessage('error', 'Nie mozna przypisac stanowiska. Osiagnieto maksymalny limit pracownikow dla tego stanowiska.');
                    } else {
                        setFlashMessage('error', 'Wystapil blad podczas przypisywania stanowiska.');
                    }
                }
            }
        }

        if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['assignment_id'])) {
            if (!verifyCsrfToken($_GET['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/admin/users/assign-position.php?user_id=' . $user_id);
            }

            $assignment_id = (int)$_GET['assignment_id'];

            try {
                Position::removeFromUser($assignment_id, $user_id);
                setFlashMessage('success', 'Stanowisko zostalo usuniete pomyslnie.');
                $this->redirectTo('/admin/users/assign-position.php?user_id=' . $user_id);
            } catch (Exception $e) {
                error_log('Error removing position: ' . $e->getMessage());
                setFlashMessage('error', 'Wystapil blad podczas usuwania stanowiska.');
            }
        }

        $this->render('admin/users/assign-position', [
            'page_title' => 'Przypisz stanowisko',
            'errors' => $errors,
            'user' => $user,
            'user_id' => $user_id,
            'positions' => $positions,
            'current_positions' => $current_positions
        ]);
    }

    public function toggleStatus() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('users', 'update');

        if (!verifyCsrfToken($_GET['csrf_token'] ?? '')) {
            setFlashMessage('error', 'Nieprawidlowy token CSRF.');
            $this->redirectTo('/admin/users/index.php');
        }

        $user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $action = $_GET['action'] ?? '';

        if (!$user_id || !in_array($action, ['activate', 'deactivate'])) {
            setFlashMessage('error', 'Nieprawidlowe parametry.');
            $this->redirectTo('/admin/users/index.php');
        }

        if ($user_id == getCurrentUserId()) {
            setFlashMessage('error', 'Nie mozesz dezaktywowac wlasnego konta.');
            $this->redirectTo('/admin/users/index.php');
        }

        $user = User::find($user_id);
        if (!$user) {
            setFlashMessage('error', 'Uzytkownik nie zostal znaleziony.');
            $this->redirectTo('/admin/users/index.php');
        }

        try {
            $new_status = $action === 'activate';
            User::updateStatus($user_id, $new_status);

            $message = $action === 'activate' ? 'Uzytkownik zostal aktywowany.' : 'Uzytkownik zostal dezaktywowany.';
            setFlashMessage('success', $message);
        } catch (Exception $e) {
            error_log('Error toggling user status: ' . $e->getMessage());
            setFlashMessage('error', 'Wystapil blad podczas zmiany statusu uzytkownika.');
        }

        $this->redirectTo('/admin/users/index.php');
    }
}
