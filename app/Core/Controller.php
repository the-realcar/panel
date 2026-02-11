<?php

class Controller {
    protected function render($view, array $data = []) {
        View::render($view, $data);
    }

    protected function redirectTo($url, $status_code = 302) {
        header('Location: ' . $url, true, $status_code);
        exit;
    }
}
