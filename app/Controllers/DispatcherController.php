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
        $vehicles_available = Vehicle::countByStatus('available');
        $vehicles_in_use = Vehicle::countByStatus('in_use');
        $vehicles_maintenance = Vehicle::countByStatus('maintenance');
        $vehicles_broken = Vehicle::countByStatus('broken');

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
                    Schedule::create([
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
}
