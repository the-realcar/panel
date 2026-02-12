<?php

class AuthController extends Controller {
    public function login() {
        if (isLoggedIn()) {
            $this->redirectTo('/index.php');
        }

        $errors = [];
        $username = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/login.php');
            }

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
                    if ($auth->getLastError() === 'lockout') {
                        $remaining = $auth->getLastErrorContext()['remaining_seconds'] ?? 0;
                        $minutes = (int)ceil($remaining / 60);
                        $suffix = $minutes > 0 ? ' Sprobuj ponownie za ' . $minutes . ' min.' : '';
                        $errors['login'] = 'Zbyt wiele prob logowania.' . $suffix;
                    } else {
                        $errors['login'] = 'Nieprawidlowa nazwa uzytkownika lub haslo.';
                    }
                }
            } else {
                $errors = $validator->getErrors();
            }
        }

        $oauth = [
            'discord' => (bool)DISCORD_CLIENT_ID && (bool)DISCORD_CLIENT_SECRET,
            'roblox' => (bool)ROBLOX_CLIENT_ID && (bool)ROBLOX_CLIENT_SECRET
        ];

        $this->render('auth/login', [
            'errors' => $errors,
            'username' => $username,
            'oauth' => $oauth
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
                if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                    setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                    $this->redirectTo('/reset-password.php');
                }

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

    public function discord() {
        $this->startOAuth('discord');
    }

    public function discordCallback() {
        $this->handleOAuthCallback('discord');
    }

    public function roblox() {
        $this->startOAuth('roblox');
    }

    public function robloxCallback() {
        $this->handleOAuthCallback('roblox');
    }

    private function startOAuth($provider) {
        $config = $this->getProviderConfig($provider);
        if (!$config) {
            setFlashMessage('error', 'Logowanie OAuth jest niedostepne.');
            $this->redirectTo('/login.php');
        }

        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = [
            'provider' => $provider,
            'value' => $state,
            'created_at' => time()
        ];

        $params = [
            'client_id' => $config['client_id'],
            'response_type' => 'code',
            'redirect_uri' => $config['redirect_uri'],
            'scope' => $config['scope'],
            'state' => $state
        ];

        $url = $config['authorize_url'] . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $this->redirectTo($url);
    }

    private function handleOAuthCallback($provider) {
        $config = $this->getProviderConfig($provider);
        if (!$config) {
            setFlashMessage('error', 'Logowanie OAuth jest niedostepne.');
            $this->redirectTo('/login.php');
        }

        if (!empty($_GET['error'])) {
            setFlashMessage('error', 'Logowanie OAuth nie powiodlo sie.');
            $this->redirectTo('/login.php');
        }

        $state = $_GET['state'] ?? '';
        $code = $_GET['code'] ?? '';

        if (!$this->isValidOauthState($provider, $state)) {
            setFlashMessage('error', 'Nieprawidlowy stan logowania OAuth.');
            $this->redirectTo('/login.php');
        }

        if (!$code) {
            setFlashMessage('error', 'Brak kodu logowania OAuth.');
            $this->redirectTo('/login.php');
        }

        $tokenResponse = $this->httpPostForm($config['token_url'], [
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $config['redirect_uri']
        ]);

        if (!$tokenResponse['ok'] || empty($tokenResponse['data']['access_token'])) {
            setFlashMessage('error', 'Nie udalo sie pobrac tokenu OAuth.');
            $this->redirectTo('/login.php');
        }

        $accessToken = $tokenResponse['data']['access_token'];
        $profileResponse = $this->httpGetJson($config['user_url'], [
            'Authorization: Bearer ' . $accessToken,
            'Accept: application/json'
        ]);

        if (!$profileResponse['ok']) {
            setFlashMessage('error', 'Nie udalo sie pobrac profilu OAuth.');
            $this->redirectTo('/login.php');
        }

        $providerId = $this->extractProviderId($provider, $profileResponse['data']);
        if (!$providerId) {
            setFlashMessage('error', 'Nie znaleziono identyfikatora konta w profilu OAuth.');
            $this->redirectTo('/login.php');
        }

        $auth = new Auth();
        $user = $auth->findActiveUserByProviderId($provider, $providerId);
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        if (!$user) {
            $auth->recordLoginAttempt(null, $ip_address, $user_agent, false);
            setFlashMessage('error', 'Nie znaleziono konta powiazanego z tym identyfikatorem.' );
            $this->redirectTo('/login.php');
        }

        if ($auth->loginWithUser($user, $ip_address, $user_agent)) {
            $this->redirectTo('/index.php');
        }

        $auth->recordLoginAttempt($user['id'], $ip_address, $user_agent, false);
        setFlashMessage('error', 'Logowanie OAuth nie powiodlo sie.');
        $this->redirectTo('/login.php');
    }

    private function getProviderConfig($provider) {
        if ($provider === 'discord') {
            if (!DISCORD_CLIENT_ID || !DISCORD_CLIENT_SECRET) {
                return null;
            }

            return [
                'client_id' => DISCORD_CLIENT_ID,
                'client_secret' => DISCORD_CLIENT_SECRET,
                'scope' => DISCORD_SCOPE,
                'authorize_url' => DISCORD_AUTHORIZE_URL,
                'token_url' => DISCORD_TOKEN_URL,
                'user_url' => DISCORD_USER_URL,
                'redirect_uri' => DISCORD_REDIRECT_URI
            ];
        }

        if ($provider === 'roblox') {
            if (!ROBLOX_CLIENT_ID || !ROBLOX_CLIENT_SECRET) {
                return null;
            }

            return [
                'client_id' => ROBLOX_CLIENT_ID,
                'client_secret' => ROBLOX_CLIENT_SECRET,
                'scope' => ROBLOX_SCOPE,
                'authorize_url' => ROBLOX_AUTHORIZE_URL,
                'token_url' => ROBLOX_TOKEN_URL,
                'user_url' => ROBLOX_USER_URL,
                'redirect_uri' => ROBLOX_REDIRECT_URI
            ];
        }

        return null;
    }

    private function isValidOauthState($provider, $state) {
        $stored = $_SESSION['oauth_state'] ?? null;
        if (!$stored) {
            return false;
        }

        $isValid = $stored['provider'] === $provider
            && $stored['value'] === $state
            && (time() - $stored['created_at']) < 600;

        unset($_SESSION['oauth_state']);

        return $isValid;
    }

    private function extractProviderId($provider, $profile) {
        if (!is_array($profile)) {
            return null;
        }

        if ($provider === 'discord') {
            return $profile['id'] ?? null;
        }

        if ($provider === 'roblox') {
            return $profile['sub'] ?? $profile['id'] ?? $profile['user_id'] ?? null;
        }

        return null;
    }

    private function httpPostForm($url, array $data) {
        $ch = curl_init($url);
        $payload = http_build_query($data, '', '&', PHP_QUERY_RFC3986);

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json'
            ]
        ]);

        $raw = curl_exec($ch);
        $error = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($raw === false) {
            return ['ok' => false, 'status' => $status, 'data' => null, 'error' => $error];
        }

        $data = json_decode($raw, true);
        return [
            'ok' => $status >= 200 && $status < 300,
            'status' => $status,
            'data' => is_array($data) ? $data : null,
            'error' => null
        ];
    }

    private function httpGetJson($url, array $headers = []) {
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => $headers
        ]);

        $raw = curl_exec($ch);
        $error = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($raw === false) {
            return ['ok' => false, 'status' => $status, 'data' => null, 'error' => $error];
        }

        $data = json_decode($raw, true);
        return [
            'ok' => $status >= 200 && $status < 300,
            'status' => $status,
            'data' => is_array($data) ? $data : null,
            'error' => null
        ];
    }
}
