<?php

class AdminApplicationsController extends Controller {

    public function index() {
        requireLogin();

        $rbac = new RBAC();
        if (!$rbac->isAdmin() && !$rbac->hasAnyRole(['Zarząd', 'Nadzór Ruchu', 'Kadry', 'Dyspozytor'])) {
            setFlashMessage('error', 'Brak dostepu.');
            $this->redirectTo('/index.php');
        }

        $status_filter = $_GET['status'] ?? '';
        $type_filter   = $_GET['type']   ?? '';
        $page          = max(1, (int)($_GET['page'] ?? 1));
        $per_page      = ITEMS_PER_PAGE;
        $offset        = ($page - 1) * $per_page;

        $total_items = Application::countAll($status_filter, $type_filter);
        $total_pages = (int)ceil($total_items / $per_page);
        $applications = Application::getAll($status_filter, $type_filter, $per_page, $offset);

        $this->render('admin/applications/index', [
            'page_title'    => 'Wnioski pracownikow',
            'applications'  => $applications,
            'status_filter' => $status_filter,
            'type_filter'   => $type_filter,
            'page'          => $page,
            'total_pages'   => $total_pages,
            'rbac'          => $rbac,
        ]);
    }

    public function view() {
        requireLogin();

        $rbac = new RBAC();
        if (!$rbac->isAdmin() && !$rbac->hasAnyRole(['Zarząd', 'Nadzór Ruchu', 'Kadry', 'Dyspozytor'])) {
            setFlashMessage('error', 'Brak dostepu.');
            $this->redirectTo('/index.php');
        }

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $application = Application::find($id);

        if (!$application) {
            setFlashMessage('error', 'Wniosek nie istnieje.');
            $this->redirectTo('/admin/applications/index.php');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Blad weryfikacji formularza.');
                $this->redirectTo('/admin/applications/view.php?id=' . $id);
            }

            $new_status   = $_POST['status'] ?? '';
            $review_notes = trim($_POST['review_notes'] ?? '');

            $allowed = ['approved', 'rejected'];
            if (!in_array($new_status, $allowed, true)) {
                setFlashMessage('error', 'Nieprawidlowy status.');
                $this->redirectTo('/admin/applications/view.php?id=' . $id);
            }

            Application::updateStatus($id, $new_status, getCurrentUserId(), $review_notes);
            AuditLog::log('application.' . $new_status, 'applications', $id, ['status' => $application['status']], ['status' => $new_status, 'review_notes' => $review_notes]);
            $label = $new_status === 'approved' ? 'zatwierdzony' : 'odrzucony';
            setFlashMessage('success', 'Wniosek zostal ' . $label . '.');
            $this->redirectTo('/admin/applications/index.php');
        }

        $this->render('admin/applications/view', [
            'page_title'  => 'Wniosek #' . $id,
            'application' => $application,
            'rbac'        => $rbac,
        ]);
    }
}
