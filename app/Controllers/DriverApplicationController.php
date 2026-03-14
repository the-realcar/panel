<?php

class DriverApplicationController extends Controller {

    private static $VALID_TYPES = [
        'kzw', 'cancel_duty', 'day_off', 'vacation',
        'permanent_vehicle', 'change_vehicle', 'no_vehicle_assign',
        'change_status', 'resignation',
    ];

    public function index() {
        requireLogin();

        $rbac = new RBAC();
        if (!$rbac->hasRole('Kierowca') && !$rbac->isAdmin()) {
            setFlashMessage('error', 'Brak dostepu do panelu kierowcy.');
            $this->redirectTo('/index.php');
        }

        $user_id = getCurrentUserId();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Blad weryfikacji formularza. Sprobuj ponownie.');
                $this->redirectTo('/driver/applications.php');
            }

            // Handle cancellation of pending application
            if (isset($_POST['action']) && $_POST['action'] === 'cancel') {
                $app_id = (int)($_POST['app_id'] ?? 0);
                if ($app_id && Application::cancel($app_id, $user_id)) {
                    AuditLog::log('application.cancel', 'applications', $app_id, null, ['user_id' => $user_id]);
                    setFlashMessage('success', 'Wniosek zostal anulowany.');
                } else {
                    setFlashMessage('error', 'Nie mozna anulowac wniosku.');
                }
                $this->redirectTo('/driver/applications.php');
            }

            $type = $_POST['type'] ?? '';
            if (!in_array($type, self::$VALID_TYPES, true)) {
                setFlashMessage('error', 'Nieprawidlowy typ wniosku.');
                $this->redirectTo('/driver/applications.php');
            }

            $data = [
                'user_id' => $user_id,
                'type'    => $type,
                'reason'  => trim($_POST['reason_' . $type] ?? $_POST['reason'] ?? ''),
                'notes'   => trim($_POST['notes'] ?? ''),
            ];

            $error = null;

            switch ($type) {
                case 'kzw':
                    if (empty($_POST['execution_date_kzw'])) {
                        $error = 'Data wykonania jest wymagana.';
                        break;
                    }
                    $data['execution_date'] = $_POST['execution_date_kzw'];
                    $data['vehicle_id'] = !empty($_POST['vehicle_id_kzw']) ? (int)$_POST['vehicle_id_kzw'] : null;
                    break;

                case 'cancel_duty':
                    if (empty($_POST['schedule_id'])) {
                        $error = 'Wybierz sluzbe do anulowania.';
                        break;
                    }
                    $data['schedule_id'] = (int)$_POST['schedule_id'];
                    if (empty($data['reason'])) {
                        $error = 'Powod jest wymagany.';
                    }
                    break;

                case 'day_off':
                    if (empty($_POST['execution_date_day_off'])) {
                        $error = 'Data dnia wolnego jest wymagana.';
                        break;
                    }
                    $data['execution_date'] = $_POST['execution_date_day_off'];
                    if (empty($data['reason'])) {
                        $error = 'Powod jest wymagany.';
                    }
                    break;

                case 'vacation':
                    if (empty($_POST['date_from']) || empty($_POST['date_to'])) {
                        $error = 'Daty urlopu sa wymagane.';
                        break;
                    }
                    $data['date_from'] = $_POST['date_from'];
                    $data['date_to']   = $_POST['date_to'];
                    if (empty($data['reason'])) {
                        $error = 'Powod jest wymagany.';
                    }
                    break;

                case 'permanent_vehicle':
                case 'change_vehicle':
                    $vid_key = $type === 'permanent_vehicle' ? 'vehicle_id_perm' : 'vehicle_id_change';
                    if (empty($_POST[$vid_key])) {
                        $error = 'Pojazd jest wymagany.';
                        break;
                    }
                    $data['vehicle_id'] = (int)$_POST[$vid_key];
                    break;

                case 'no_vehicle_assign':
                    if (empty($_POST['vehicle_ids'])) {
                        $error = 'Wybierz co najmniej jeden pojazd.';
                        break;
                    }
                    $data['vehicles_json'] = array_map('intval', (array)$_POST['vehicle_ids']);
                    if (empty($data['reason'])) {
                        $error = 'Powod jest wymagany.';
                    }
                    break;

                case 'change_status':
                    $days = isset($_POST['work_days']) ? array_values(array_filter((array)$_POST['work_days'])) : [];
                    $data['work_days'] = $days;
                    if (empty($data['reason'])) {
                        $error = 'Powod jest wymagany.';
                    }
                    break;

                case 'resignation':
                    break;
            }

            if ($error) {
                setFlashMessage('error', $error);
                $this->redirectTo('/driver/applications.php');
            }

            try {
                $id = Application::create($data);
                if ($id) {
                    AuditLog::log('application.create', 'applications', $id, null, ['type' => $type, 'user_id' => $user_id]);
                    setFlashMessage('success', 'Wniosek zostal zlozony pomyslnie (ID: ' . $id . ').');
                } else {
                    setFlashMessage('error', 'Nie udalo sie zlozyc wniosku.');
                }
            } catch (Exception $e) {
                setFlashMessage('error', 'Blad podczas skladania wniosku.');
            }

            $this->redirectTo('/driver/applications.php');
        }

        // GET — load form data
        $today   = date('Y-m-d');
        $future  = date('Y-m-d', strtotime('+60 days'));

        $upcoming_schedules = Schedule::listForUserInRange($user_id, $today, $future, 'scheduled', 50, 0);
        $vehicles           = Vehicle::listAll();
        $history            = Application::getByUser($user_id, 20, 0);

        $this->render('driver/applications', [
            'page_title'         => 'Wnioski',
            'upcoming_schedules' => $upcoming_schedules,
            'vehicles'           => $vehicles,
            'history'            => $history,
        ]);
    }
}
