<?php

class DriverDocumentationController extends Controller {
    public function index() {
        requireLogin();

        $rbac = new RBAC();
        if (!$rbac->hasRole('Kierowca') && !$rbac->isAdmin()) {
            setFlashMessage('error', 'Brak dostepu do dokumentacji kierowcy.');
            $this->redirectTo('/index.php');
        }

        $docs_dir = BASE_PATH . '/docs/driver';
        $documents = [];

        if (is_dir($docs_dir)) {
            $files = scandir($docs_dir);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                $full_path = $docs_dir . '/' . $file;
                if (!is_file($full_path)) {
                    continue;
                }

                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (!in_array($extension, ['txt', 'md', 'pdf'], true)) {
                    continue;
                }

                $documents[] = [
                    'name' => $file,
                    'extension' => $extension,
                    'size' => filesize($full_path) ?: 0,
                    'mtime' => filemtime($full_path) ?: 0
                ];
            }
        }

        usort($documents, static function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        $selected_name = basename((string)($_GET['file'] ?? ''));
        $selected = null;
        $content = null;

        if ($selected_name !== '') {
            foreach ($documents as $doc) {
                if ($doc['name'] === $selected_name) {
                    $selected = $doc;
                    break;
                }
            }

            if ($selected !== null && in_array($selected['extension'], ['txt', 'md'], true)) {
                $file_path = $docs_dir . '/' . $selected['name'];
                $loaded = @file_get_contents($file_path);
                $content = $loaded !== false ? $loaded : null;
            }
        }

        $this->render('driver/documentation', [
            'page_title' => 'Dokumentacja kierowcy',
            'documents' => $documents,
            'selected' => $selected,
            'content' => $content
        ]);
    }
}
