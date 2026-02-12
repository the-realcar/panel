<?php

class DriverScheduleController extends Controller {
    public function index() {
        requireLogin();

        $rbac = new RBAC();
        if (!$rbac->hasRole('Kierowca') && !$rbac->isAdmin()) {
            setFlashMessage('error', 'Brak dostepu do panelu kierowcy.');
            $this->redirectTo('/index.php');
        }

        $user_id = getCurrentUserId();
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $per_page = 20;
        $offset = ($page - 1) * $per_page;

        $status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
        $allowed_statuses = ['all', 'scheduled', 'completed', 'cancelled'];
        if (!in_array($status_filter, $allowed_statuses)) {
            $status_filter = 'all';
        }

        $today = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime('+30 days'));

        $total_records = Schedule::countForUserInRange($user_id, $today, $end_date, $status_filter);
        $total_pages = (int)ceil($total_records / $per_page);
        $schedules = Schedule::listForUserInRange($user_id, $today, $end_date, $status_filter, $per_page, $offset);

        $this->render('driver/schedule', [
            'page_title' => 'Grafik Pracy',
            'status_filter' => $status_filter,
            'schedules' => $schedules,
            'total_records' => $total_records,
            'page' => $page,
            'total_pages' => $total_pages
        ]);
    }
}
