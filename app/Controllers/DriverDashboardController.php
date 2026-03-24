<?php

class DriverDashboardController extends Controller {
    public function index() {
        requireLogin();

        $rbac = new RBAC();
        if (!$rbac->hasRole('Kierowca') && !$rbac->isAdmin()) {
            setFlashMessage('error', 'Brak dostepu do panelu kierowcy.');
            $this->redirectTo('/index.php');
        }

        $user_id = getCurrentUserId();
        $today = date('Y-m-d');

        $today_schedules = Schedule::getTodaySchedules($user_id, $today);
        $stats = Schedule::getUserStats($user_id, $today);
        $recent_incidents = Incident::getRecentByUser($user_id, 5);
        $recent_dispatches = [];
        $unread_dispatches = 0;

        try {
            $recent_dispatches = Dispatch::listForRecipient($user_id, 5);
            $unread_dispatches = Dispatch::countUnreadForRecipient($user_id);

            if ($unread_dispatches > 0) {
                Dispatch::markAllReadForRecipient($user_id);
            }
        } catch (Throwable $e) {
            error_log('DriverDashboardController dispatches error: ' . $e->getMessage());
        }

        $this->render('driver/dashboard', [
            'page_title' => 'Panel Kierowcy',
            'today' => $today,
            'today_schedules' => $today_schedules,
            'stats' => $stats,
            'recent_incidents' => $recent_incidents,
            'recent_dispatches' => $recent_dispatches,
            'unread_dispatches' => $unread_dispatches
        ]);
    }
}
