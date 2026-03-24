<?php

class AdminSettingsController extends Controller {
    private const ALLOWED_KEYS = [
        'company_name',
        'base_url',
        'support_email',
        'session_timeout'
    ];

    public function index() {
        requireLogin();

        $rbac = new RBAC();
        if (!$rbac->isAdmin()) {
            setFlashMessage('error', 'Brak uprawnien do zarzadzania ustawieniami systemu.');
            $this->redirectTo('/index.php');
        }

        $errors = [];
        $settings_available = Setting::isAvailable();
        $form = [
            'company_name' => APP_NAME,
            'base_url' => BASE_URL,
            'support_email' => '',
            'session_timeout' => (string)SESSION_TIMEOUT
        ];

        $stored = Setting::getMany(self::ALLOWED_KEYS);
        foreach ($form as $key => $default_value) {
            if (isset($stored[$key]) && $stored[$key] !== '') {
                $form[$key] = $stored[$key];
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$settings_available) {
                setFlashMessage('error', 'Tabela ustawień nie jest dostępna w tej bazie danych.');
                $this->redirectTo('/admin/settings/index.php');
            }

            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/admin/settings/index.php');
            }

            $form['company_name'] = trim($_POST['company_name'] ?? '');
            $form['base_url'] = trim($_POST['base_url'] ?? '');
            $form['support_email'] = trim($_POST['support_email'] ?? '');
            $form['session_timeout'] = trim($_POST['session_timeout'] ?? '');

            $validator = new Validator($form);
            $validator->required('company_name', 'Nazwa firmy jest wymagana.')
                      ->required('base_url', 'Adres bazowy jest wymagany.')
                      ->required('session_timeout', 'Timeout sesji jest wymagany.');

            if ($form['support_email'] !== '') {
                $validator->email('support_email', 'Podaj poprawny email wsparcia.');
            }

            if ($validator->fails()) {
                $errors = $validator->getErrors();
            }

            if (!isset($errors['base_url']) && !filter_var($form['base_url'], FILTER_VALIDATE_URL)) {
                $errors['base_url'] = 'Podaj poprawny adres URL.';
            }

            if (!ctype_digit($form['session_timeout'])) {
                $errors['session_timeout'] = 'Timeout sesji musi byc liczba calkowita w sekundach.';
            } else {
                $session_timeout = (int)$form['session_timeout'];
                if ($session_timeout < 300 || $session_timeout > 86400) {
                    $errors['session_timeout'] = 'Timeout sesji musi byc z zakresu 300-86400 sekund.';
                }
            }

            if (empty($errors)) {
                $before = $stored;
                Setting::setMany([
                    'company_name' => $form['company_name'],
                    'base_url' => $form['base_url'],
                    'support_email' => $form['support_email'],
                    'session_timeout' => $form['session_timeout']
                ], getCurrentUserId());

                AuditLog::log(
                    'settings.update',
                    'settings',
                    null,
                    $before,
                    [
                        'company_name' => $form['company_name'],
                        'base_url' => $form['base_url'],
                        'support_email' => $form['support_email'],
                        'session_timeout' => $form['session_timeout']
                    ]
                );

                setFlashMessage('success', 'Ustawienia systemowe zostaly zapisane.');
                $this->redirectTo('/admin/settings/index.php');
            }
        }

        $all_settings = Setting::listAll();

        $this->render('admin/settings/index', [
            'page_title' => 'Ustawienia systemowe',
            'form' => $form,
            'errors' => $errors,
            'all_settings' => $all_settings,
            'settings_available' => $settings_available
        ]);
    }
}
