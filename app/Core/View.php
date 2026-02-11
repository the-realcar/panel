<?php

class View {
    public static function render($view, array $data = []) {
        $view_path = BASE_PATH . '/app/Views/' . $view . '.php';
        if (!file_exists($view_path)) {
            throw new RuntimeException('View not found: ' . $view);
        }
        extract($data, EXTR_SKIP);
        require $view_path;
    }

    public static function partial($partial, array $data = []) {
        $partial_path = BASE_PATH . '/app/Views/' . $partial . '.php';
        if (!file_exists($partial_path)) {
            throw new RuntimeException('Partial not found: ' . $partial);
        }
        extract($data, EXTR_SKIP);
        require $partial_path;
    }
}
