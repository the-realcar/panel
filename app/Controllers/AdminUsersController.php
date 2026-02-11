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
