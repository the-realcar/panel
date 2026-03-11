<?php

class AdminIncidentsController extends Controller {
    public function index() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('incidents', 'read');

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
}
