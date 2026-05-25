<?php

class UserSettingsController extends Controller {
    public function index() {
        requireLogin();

        $user_id = getCurrentUserId();
        $user = User::find($user_id);
        if (!$user) {
            setFlashMessage('error', 'Nie znaleziono konta uzytkownika.');
            $this->redirectTo('/index.php');
        }

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/settings.php');
            }

            $current_password = (string)($_POST['current_password'] ?? '');
            $new_password = (string)($_POST['new_password'] ?? '');
            $confirm_password = (string)($_POST['confirm_password'] ?? '');

            if ($current_password === '' || !password_verify($current_password, $user['password_hash'] ?? '')) {
                $errors['current_password'] = 'Aktualne haslo jest nieprawidlowe.';
            }

            if ($new_password === '') {
                $errors['new_password'] = 'Nowe haslo jest wymagane.';
            } elseif (strlen($new_password) < PASSWORD_MIN_LENGTH) {
                $errors['new_password'] = 'Nowe haslo jest za krotkie.';
            }

            if ($confirm_password !== $new_password) {
                $errors['confirm_password'] = 'Potwierdzenie hasla nie zgadza sie z nowym haslem.';
            }

            if (empty($errors)) {
                $password_hash = password_hash($new_password, PASSWORD_BCRYPT);
                User::updatePassword($user_id, $password_hash);

                AuditLog::log('user.change_password', 'users', $user_id, null, [
                    'username' => $user['username'] ?? null
                ]);

                setFlashMessage('success', 'Haslo zostalo zmienione.');
                $this->redirectTo('/settings.php');
            }
        }

        $this->render('settings/index', [
            'page_title' => 'Ustawienia konta',
            'errors' => $errors,
            'user' => $user
        ]);
    }
}
