<?php

class AuthController extends Controller {
    public function login() {
        if (isLoggedIn()) {
            $this->redirectTo('/index.php');
        }

        $errors = [];
        $username = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            $validator = new Validator($_POST);
            $validator->required('username', 'Nazwa uzytkownika jest wymagana')
                      ->required('password', 'Haslo jest wymagane');

            if ($validator->passes()) {
                $auth = new Auth();
                $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

                if ($auth->login($username, $password, $ip_address, $user_agent)) {
                    $redirect = $_GET['redirect'] ?? '/index.php';
                    $this->redirectTo($redirect);
                } else {
                    $errors['login'] = 'Nieprawidlowa nazwa uzytkownika lub haslo.';
                }
            } else {
                $errors = $validator->getErrors();
            }
        }

        $this->render('auth/login', [
            'errors' => $errors,
            'username' => $username
        ]);
    }

    public function logout() {
        $auth = new Auth();
        $auth->logout();
        $this->redirectTo('/login.php');
    }

    public function resetPassword() {
        $errors = [];
        $success = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action']) && $_POST['action'] === 'request') {
                $email = $_POST['email'] ?? '';

                $validator = new Validator($_POST);
                $validator->required('email', 'Email jest wymagany')
                          ->email('email', 'Podaj poprawny adres email');

                if ($validator->passes()) {
                    $auth = new Auth();
                    if ($auth->requestPasswordReset($email)) {
                        $success = true;
                        setFlashMessage('success', 'Link do resetowania hasla zostal wyslany na podany adres email.');
                    } else {
                        $errors['email'] = 'Nie znaleziono uzytkownika z podanym adresem email.';
                    }
                } else {
                    $errors = $validator->getErrors();
                }
            }
        }

        $this->render('auth/reset-password', [
            'errors' => $errors,
            'success' => $success
        ]);
    }
}
