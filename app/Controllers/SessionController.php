<?php

class SessionController extends Controller {
    public function checkSession() {
        header('Content-Type: application/json');
        $valid = isLoggedIn() && checkSessionTimeout();

        echo json_encode([
            'valid' => $valid,
            'timestamp' => time()
        ]);
    }

    public function ping() {
        header('Content-Type: application/json');

        if (isLoggedIn()) {
            $_SESSION['last_activity'] = time();
            http_response_code(200);
            echo json_encode(['success' => true]);
            return;
        }

        http_response_code(401);
        echo json_encode(['success' => false]);
    }
}
