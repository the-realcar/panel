<?php

class DispatcherController extends Controller {
    public function dashboard() {
        requireLogin();

        $rbac = new RBAC();
        if (!$rbac->hasRole('Dyspozytor') && !$rbac->isAdmin()) {
            setFlashMessage('error', 'Brak dostepu do panelu dyspozytora.');
            $this->redirectTo('/index.php');
        }

        // Dzisiejsze grafiki
        $today = date('Y-m-d');
        $schedules_today = Schedule::listForDate($today);

        // Status floty
        $vehicles_available = Vehicle::countByStatus('sprawny');
        $vehicles_in_use = Vehicle::countByStatus('zawieszony');
        $vehicles_maintenance = Vehicle::countByStatus('w naprawie');
        $vehicles_broken = Vehicle::countByStatus('odstawiony');

        // Otwarte incydenty
        $open_incidents = Incident::countByStatus('open');
        $in_progress_incidents = Incident::countByStatus('in_progress');

        // Statystyki
        $stats = [
            'vehicles_available' => $vehicles_available,
            'vehicles_in_use' => $vehicles_in_use,
            'vehicles_maintenance' => $vehicles_maintenance,
            'vehicles_broken' => $vehicles_broken,
            'open_incidents' => $open_incidents,
            'in_progress_incidents' => $in_progress_incidents
        ];

        $this->render('dispatcher/dashboard', [
            'page_title' => 'Panel Dyspozytora',
            'schedules_today' => $schedules_today,
            'stats' => $stats
        ]);
    }

    public function fleet() {
        requireLogin();

        $rbac = new RBAC();
        if (!$rbac->hasRole('Dyspozytor') && !$rbac->isAdmin()) {
            setFlashMessage('error', 'Brak dostepu do panelu dyspozytora.');
            $this->redirectTo('/index.php');
        }

        $status_filter = $_GET['status'] ?? '';
        $vehicles = Vehicle::listByStatus($status_filter, 100, 0);

        $this->render('dispatcher/fleet', [
            'page_title' => 'Status Floty',
            'vehicles' => $vehicles,
            'status_filter' => $status_filter
        ]);
    }

    public function schedules() {
        requireLogin();

        $rbac = new RBAC();
        if (!$rbac->hasRole('Dyspozytor') && !$rbac->hasRole('Zarząd') && !$rbac->isAdmin()) {
            setFlashMessage('error', 'Brak dostepu.');
            $this->redirectTo('/index.php');
        }

        $date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-7 days'));
        $date_to   = $_GET['date_to']   ?? date('Y-m-d', strtotime('+7 days'));
        $page      = max(1, (int)($_GET['page'] ?? 1));
        $per_page  = ITEMS_PER_PAGE;
        $offset    = ($page - 1) * $per_page;

        $total_items = Schedule::countAll($date_from, $date_to);
        $total_pages = (int)ceil($total_items / $per_page);
        $schedules   = Schedule::listAll($date_from, $date_to, null, $per_page, $offset);

        $this->render('dispatcher/schedules', [
            'page_title'  => 'Przegląd Grafików',
            'schedules'   => $schedules,
            'date_from'   => $date_from,
            'date_to'     => $date_to,
            'page'        => $page,
            'total_pages' => $total_pages,
        ]);
    }

    public function assignSchedule() {
        requireLogin();

        $rbac = new RBAC();
        if (!$rbac->hasRole('Dyspozytor') && !$rbac->isAdmin()) {
            setFlashMessage('error', 'Brak dostepu do panelu dyspozytora.');
            $this->redirectTo('/index.php');
        }

        $errors = [];
        $form_data = [];
        
        $users = User::listByRole('Kierowca');
        $vehicles = Vehicle::listNotBroken();
        $lines = Line::listActive();
        $brigades = Brigade::listActive();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/dispatcher/assign-schedule.php');
            }

            $form_data = $_POST;

            $validator = new Validator($form_data);
            $validator->required('user_id', 'Kierowca jest wymagany.')
                      ->required('vehicle_id', 'Pojazd jest wymagany.')
                      ->required('line_id', 'Linia jest wymagana.')
                      ->required('schedule_date', 'Data jest wymagana.')
                      ->required('start_time', 'Godzina rozpoczecia jest wymagana.')
                      ->required('end_time', 'Godzina zakonczenia jest wymagana.');

            if ($validator->fails()) {
                $errors = $validator->getErrors();
            }

            if (empty($errors)) {
                try {
                    $new_schedule_id = Schedule::create([
                        'user_id' => $form_data['user_id'],
                        'vehicle_id' => $form_data['vehicle_id'],
                        'line_id' => $form_data['line_id'],
                        'brigade_id' => !empty($form_data['brigade_id']) ? $form_data['brigade_id'] : null,
                        'schedule_date' => $form_data['schedule_date'],
                        'start_time' => $form_data['start_time'],
                        'end_time' => $form_data['end_time'],
                        'status' => 'scheduled',
                        'notes' => !empty($form_data['notes']) ? $form_data['notes'] : null
                    ]);
                    AuditLog::log('schedule.create', 'schedules', $new_schedule_id, null, ['user_id' => $form_data['user_id'], 'schedule_date' => $form_data['schedule_date'], 'line_id' => $form_data['line_id']]);

                    setFlashMessage('success', 'Grafik zostal przydzielony pomyslnie.');
                    $this->redirectTo('/dispatcher/dashboard.php');
                } catch (Exception $e) {
                    error_log('Error creating schedule: ' . $e->getMessage());
                    $errors['general'] = 'Wystapil blad podczas przydzielania grafiku.';
                }
            }
        }

        $this->render('dispatcher/assign-schedule', [
            'page_title' => 'Przydziel Grafik',
            'errors' => $errors,
            'form_data' => $form_data,
            'users' => $users,
            'vehicles' => $vehicles,
            'lines' => $lines,
            'brigades' => $brigades
        ]);
    }

    public function messages() {
        requireLogin();

        $rbac = new RBAC();
        if (!$rbac->hasRole('Dyspozytor') && !$rbac->isAdmin()) {
            setFlashMessage('error', 'Brak dostepu do panelu dyspozytora.');
            $this->redirectTo('/index.php');
        }

        $sender_id = getCurrentUserId();
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/dispatcher/messages.php');
            }

            $recipient_id = (int)($_POST['recipient_id'] ?? 0);
            $message = trim($_POST['message'] ?? '');

            if ($recipient_id <= 0) {
                $errors['recipient_id'] = 'Wybierz kierowce.';
            }

            if ($message === '') {
                $errors['message'] = 'Tresc komunikatu jest wymagana.';
            } elseif (mb_strlen($message) > 2000) {
                $errors['message'] = 'Komunikat moze miec maksymalnie 2000 znakow.';
            }

            if (empty($errors)) {
                $dispatch_id = Dispatch::create([
                    'sender_id' => $sender_id,
                    'recipient_id' => $recipient_id,
                    'message' => $message
                ]);

                AuditLog::log('dispatch.create', 'dispatches', $dispatch_id, null, [
                    'recipient_id' => $recipient_id,
                    'message_length' => mb_strlen($message)
                ]);

                setFlashMessage('success', 'Komunikat zostal wyslany.');
                $this->redirectTo('/dispatcher/messages.php');
            }
        }

        $drivers = User::listByRole('Kierowca');
        $sent_messages = Dispatch::listSentBy($sender_id, 50);

        $this->render('dispatcher/messages', [
            'page_title' => 'Dyspozycje dla kierowcow',
            'drivers' => $drivers,
            'sent_messages' => $sent_messages,
            'errors' => $errors,
            'form' => [
                'recipient_id' => (int)($_POST['recipient_id'] ?? 0),
                'message' => $_POST['message'] ?? ''
            ]
        ]);
    }
}
