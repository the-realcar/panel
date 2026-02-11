<?php

class DriverIncidentController extends Controller {
    public function index() {
        requireLogin();

        $rbac = new RBAC();
        if (!$rbac->hasRole('Kierowca') && !$rbac->isAdmin()) {
            setFlashMessage('error', 'Brak dostepu do panelu kierowcy.');
            $this->redirectTo('/public/index.php');
        }

        $user_id = getCurrentUserId();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Blad weryfikacji formularza. Sprobuj ponownie.');
                $this->redirectTo('/public/driver/report-incident.php');
            }

            $validator = new Validator($_POST);
            $validator->required('incident_type', 'Typ incydentu jest wymagany.')
                      ->required('severity', 'Poziom waznosci jest wymagany.')
                      ->required('title', 'Tytul jest wymagany.')
                      ->required('description', 'Opis incydentu jest wymagany.')
                      ->required('incident_date', 'Data incydentu jest wymagana.')
                      ->minLength('title', 5, 'Tytul musi miec co najmniej 5 znakow.')
                      ->minLength('description', 10, 'Opis musi miec co najmniej 10 znakow.');

            if ($validator->fails()) {
                $errors = $validator->getErrors();
                setFlashMessage('error', 'Popraw bledy w formularzu: ' . implode(', ', $errors));
            } else {
                $allowed_types = ['breakdown', 'accident', 'complaint', 'other'];
                $allowed_severities = ['low', 'medium', 'high', 'critical'];

                if (!in_array($_POST['incident_type'], $allowed_types)) {
                    setFlashMessage('error', 'Nieprawidlowy typ incydentu.');
                } elseif (!in_array($_POST['severity'], $allowed_severities)) {
                    setFlashMessage('error', 'Nieprawidlowy poziom waznosci.');
                } else {
                    try {
                        Incident::create([
                            'reported_by' => $user_id,
                            'vehicle_id' => !empty($_POST['vehicle_id']) ? $_POST['vehicle_id'] : null,
                            'incident_type' => $_POST['incident_type'],
                            'severity' => $_POST['severity'],
                            'title' => trim($_POST['title']),
                            'description' => trim($_POST['description']),
                            'incident_date' => $_POST['incident_date']
                        ]);

                        setFlashMessage('success', 'Zgloszenie zostalo zapisane pomyslnie. Dziekujemy za zgloszenie.');
                        $this->redirectTo('/public/driver/report-incident.php');
                    } catch (Exception $e) {
                        setFlashMessage('error', 'Blad podczas zapisywania zgloszenia: ' . $e->getMessage());
                    }
                }
            }
        }

        $vehicles = Vehicle::listAll();
        $recent_incidents = Incident::getRecentByUser($user_id, 10);

        $this->render('driver/report-incident', [
            'page_title' => 'Zglos Incydent',
            'vehicles' => $vehicles,
            'recent_incidents' => $recent_incidents
        ]);
    }
}
