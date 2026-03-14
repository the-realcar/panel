<?php

class AdminIncidentsController extends Controller {
    public function index() {
        requireLogin();

        $rbac = new RBAC();
        if (!$rbac->isAdmin() && !$rbac->hasAnyRole(['Dyspozytor', 'Nadzór Ruchu', 'Zarząd'])) {
            setFlashMessage('error', 'Brak dostepu.');
            $this->redirectTo('/index.php');
        }

        $status_filter = $_GET['status'] ?? '';
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $per_page = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $per_page;

        $total_items = Incident::countAll($status_filter);
        $total_pages = (int)ceil($total_items / $per_page);
        $incidents = Incident::listForAdmin($status_filter, $per_page, $offset);

        $this->render('admin/incidents/index', [
            'page_title' => 'Zarzadzanie zgloszeniami',
            'incidents' => $incidents,
            'status_filter' => $status_filter,
            'page' => $page,
            'total_pages' => $total_pages
        ]);
    }

    public function view() {
        requireLogin();

        $rbac = new RBAC();
        if (!$rbac->isAdmin() && !$rbac->hasAnyRole(['Dyspozytor', 'Nadzór Ruchu', 'Zarząd'])) {
            setFlashMessage('error', 'Brak dostepu.');
            $this->redirectTo('/index.php');
        }

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $incident = Incident::find($id);

        if (!$incident) {
            setFlashMessage('error', 'Zgloszenie nie istnieje.');
            $this->redirectTo('/admin/incidents/index.php');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Blad weryfikacji formularza.');
                $this->redirectTo('/admin/incidents/view.php?id=' . $id);
            }

            $allowed_types    = ['breakdown', 'accident', 'complaint', 'other'];
            $allowed_severity = ['low', 'medium', 'high', 'critical'];
            $allowed_status   = ['open', 'in_progress', 'resolved', 'closed'];

            $incident_type    = $_POST['incident_type'] ?? $incident['incident_type'];
            $severity         = $_POST['severity']      ?? $incident['severity'];
            $title            = trim($_POST['title']    ?? $incident['title']);
            $description      = trim($_POST['description'] ?? $incident['description']);
            $status           = $_POST['status']        ?? $incident['status'];
            $resolution_notes = trim($_POST['resolution_notes'] ?? '');

            $errors = [];
            if (empty($title))                               $errors[] = 'Tytul jest wymagany.';
            if (!in_array($incident_type, $allowed_types))   $errors[] = 'Nieprawidlowy typ.';
            if (!in_array($severity, $allowed_severity))     $errors[] = 'Nieprawidlowa waga.';
            if (!in_array($status, $allowed_status))         $errors[] = 'Nieprawidlowy status.';

            if ($errors) {
                setFlashMessage('error', implode(' ', $errors));
                $this->redirectTo('/admin/incidents/view.php?id=' . $id);
            }

            Incident::update($id, [
                'incident_type'    => $incident_type,
                'severity'         => $severity,
                'title'            => $title,
                'description'      => $description,
                'status'           => $status,
                'resolution_notes' => $resolution_notes,
            ]);
            AuditLog::log('incident.update', 'incidents', $id, ['status' => $incident['status'], 'severity' => $incident['severity']], ['status' => $status, 'severity' => $severity]);

            setFlashMessage('success', 'Zgloszenie zostalo zaktualizowane.');
            $this->redirectTo('/admin/incidents/view.php?id=' . $id);
        }

        $vehicles = Vehicle::listAll();

        $this->render('admin/incidents/view', [
            'page_title' => 'Zgloszenie #' . $id,
            'incident'   => $incident,
            'vehicles'   => $vehicles,
        ]);
    }
}
