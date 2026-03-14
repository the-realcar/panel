<?php

class AdminLogsController extends Controller {
    public function index() {
        requireLogin();

        $rbac = new RBAC();
        if (!$rbac->isAdmin()) {
            setFlashMessage('error', 'Brak dostepu do logow systemowych.');
            $this->redirectTo('/index.php');
        }

        $filters = [
            'username' => trim((string)($_GET['username'] ?? '')),
            'action' => trim((string)($_GET['action'] ?? '')),
            'date_from' => trim((string)($_GET['date_from'] ?? '')),
            'date_to' => trim((string)($_GET['date_to'] ?? '')),
            'success' => (string)($_GET['success'] ?? '')
        ];

        $login_logs = SystemLog::listLoginLogs($filters, 150);
        $audit_logs = SystemLog::listAuditLogs($filters, 250);
        $error_log_lines = SystemLog::readErrorLogTail(120);

        $this->render('admin/logs/index', [
            'page_title' => 'Logi systemowe',
            'filters' => $filters,
            'login_logs' => $login_logs,
            'audit_logs' => $audit_logs,
            'error_log_lines' => $error_log_lines
        ]);
    }
}
