<?php

class AdminDashboardController extends Controller {
    public function index() {
        requireLogin();

        $rbac = new RBAC();
        if (!$rbac->hasAnyRole(['Administrator', 'Dyspozytor'])) {
            setFlashMessage('error', 'Brak dostepu do panelu administracyjnego.');
            $this->redirectTo('/index.php');
        }

        $stats = AdminStats::getStats();
        $sla_checks = AdminStats::getSlaChecks();
        $recent_logins = LoginLog::getRecent(10);
        $recent_incidents = Incident::getRecentForAdmin(10);

        $this->render('admin/dashboard', [
            'page_title' => 'Panel Administracyjny',
            'stats' => $stats,
            'sla_checks' => $sla_checks,
            'recent_logins' => $recent_logins,
            'recent_incidents' => $recent_incidents
        ]);
    }
}
