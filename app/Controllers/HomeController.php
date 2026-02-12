<?php

class HomeController extends Controller {
    public function index() {
        if (!isLoggedIn()) {
            $this->redirectTo('/login.php');
        }

        $rbac = new RBAC();

        if ($rbac->isAdmin()) {
            $this->redirectTo('/admin/dashboard.php');
        }

        if ($rbac->hasRole('Kierowca')) {
            $this->redirectTo('/driver/dashboard.php');
        }

        if ($rbac->hasRole('Dyspozytor')) {
            $this->redirectTo('/admin/dashboard.php');
        }

        $this->redirectTo('/driver/dashboard.php');
    }
}
