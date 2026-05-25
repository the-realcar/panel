<?php

class DriverDocumentationController extends Controller {
    public function index() {
        requireLogin();

        $rbac = new RBAC();
        $can_edit = $rbac->hasRole('Zarząd') || $rbac->isAdmin();
        
        if (!$rbac->hasRole('Kierowca') && !$can_edit) {
            setFlashMessage('error', 'Brak dostepu do dokumentacji kierowcy.');
            $this->redirectTo('/index.php');
        }

        $docs_dir = BASE_PATH . '/docs/driver';
        if (!is_dir($docs_dir)) {
            @mkdir($docs_dir, 0775, true);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $can_edit) {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/driver/documentation.php');
            }

            $action = (string)($_POST['action'] ?? '');

            if ($action === 'create') {
                $file_name = $this->sanitizeDocumentName((string)($_POST['file_name'] ?? ''));
                $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $file_path = $this->buildDocumentPath($docs_dir, $file_name);
                $content = (string)($_POST['content'] ?? '');

                if ($file_name === '') {
                    setFlashMessage('error', 'Podaj nazwe dokumentu.');
                } elseif (!in_array($extension, ['txt', 'md'], true)) {
                    setFlashMessage('error', 'Mozesz utworzyc tylko dokument TXT lub MD.');
                } elseif ($file_path === null) {
                    setFlashMessage('error', 'Nieprawidlowa nazwa dokumentu.');
                } elseif (is_file($file_path)) {
                    setFlashMessage('error', 'Dokument o tej nazwie juz istnieje.');
                } elseif (file_put_contents($file_path, $content) === false) {
                    setFlashMessage('error', 'Blad podczas tworzenia dokumentu.');
                } else {
                    AuditLog::log('documentation.create', 'driver_docs', null, null, ['file' => $file_name]);
                    setFlashMessage('success', 'Dokument zostal dodany.');
                }

                $this->redirectTo('/driver/documentation.php' . ($file_name !== '' ? '?file=' . urlencode($file_name) : ''));
            }

            if ($action === 'upload') {
                $uploaded_file = $_FILES['document_file'] ?? null;
                $file_name = $this->sanitizeDocumentName((string)($uploaded_file['name'] ?? ''));
                $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $file_path = $this->buildDocumentPath($docs_dir, $file_name);
                $size = (int)($uploaded_file['size'] ?? 0);
                $tmp_name = (string)($uploaded_file['tmp_name'] ?? '');
                $error_code = (int)($uploaded_file['error'] ?? UPLOAD_ERR_NO_FILE);

                if ($error_code === UPLOAD_ERR_NO_FILE) {
                    setFlashMessage('error', 'Wybierz plik do przeslania.');
                } elseif ($error_code !== UPLOAD_ERR_OK) {
                    setFlashMessage('error', 'Blad podczas przesylania pliku.');
                } elseif ($file_name === '' || $file_path === null) {
                    setFlashMessage('error', 'Nieprawidlowa nazwa pliku.');
                } elseif (!in_array($extension, ['txt', 'md', 'pdf'], true)) {
                    setFlashMessage('error', 'Dozwolone sa tylko pliki TXT, MD i PDF.');
                } elseif ($size > 10 * 1024 * 1024) {
                    setFlashMessage('error', 'Plik jest za duzy. Maksymalny rozmiar to 10 MB.');
                } elseif (is_file($file_path)) {
                    setFlashMessage('error', 'Dokument o tej nazwie juz istnieje.');
                } elseif (!is_uploaded_file($tmp_name) || !move_uploaded_file($tmp_name, $file_path)) {
                    setFlashMessage('error', 'Nie udalo sie zapisac przeslanego pliku.');
                } else {
                    AuditLog::log('documentation.upload', 'driver_docs', null, null, ['file' => $file_name]);
                    setFlashMessage('success', 'Dokument zostal przeslany.');
                }

                $this->redirectTo('/driver/documentation.php' . ($file_name !== '' ? '?file=' . urlencode($file_name) : ''));
            }

            if ($action === 'delete') {
                $file_name = $this->sanitizeDocumentName((string)($_POST['file_name'] ?? ''));
                $file_path = $this->buildDocumentPath($docs_dir, $file_name);

                if ($file_name === '' || $file_path === null) {
                    setFlashMessage('error', 'Nieprawidlowy dokument.');
                } elseif (!is_file($file_path)) {
                    setFlashMessage('error', 'Dokument nie istnieje.');
                } elseif (!unlink($file_path)) {
                    setFlashMessage('error', 'Nie udalo sie usunac dokumentu.');
                } else {
                    AuditLog::log('documentation.delete', 'driver_docs', null, null, ['file' => $file_name]);
                    setFlashMessage('success', 'Dokument zostal usuniety.');
                }

                $this->redirectTo('/driver/documentation.php');
            }

            if ($action === 'update') {
                $file_name = $this->sanitizeDocumentName((string)($_POST['file_name'] ?? ''));
                $new_content = $_POST['content'] ?? '';
                $file_path = $this->buildDocumentPath($docs_dir, $file_name);

                if ($file_name === '' || !in_array(strtolower(pathinfo($file_name, PATHINFO_EXTENSION)), ['txt', 'md'], true)) {
                    setFlashMessage('error', 'Mozesz edytowac tylko dokument TXT lub MD.');
                } elseif ($file_path === null || !is_file($file_path)) {
                    setFlashMessage('error', 'Dokument nie istnieje.');
                } elseif (file_put_contents($file_path, $new_content) !== false) {
                    AuditLog::log('documentation.update', 'driver_docs', null, null, ['file' => $file_name]);
                    setFlashMessage('success', 'Dokument zostal zaktualizowany.');
                } else {
                    setFlashMessage('error', 'Blad podczas zapisywania dokumentu.');
                }

                $this->redirectTo('/driver/documentation.php?file=' . urlencode($file_name));
            }
        }

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
            'content' => $content,
            'can_edit' => $can_edit
        ]);
    }

    private function sanitizeDocumentName(string $file_name): string {
        $file_name = trim(str_replace(['\\', '/'], '', $file_name));
        $file_name = preg_replace('/\s+/', '-', $file_name) ?? '';
        $file_name = preg_replace('/[^A-Za-z0-9._-]/', '', $file_name) ?? '';

        return ltrim($file_name, '.');
    }

    private function buildDocumentPath(string $docs_dir, string $file_name): ?string {
        if ($file_name === '' || strpos($file_name, '..') !== false) {
            return null;
        }

        return rtrim($docs_dir, '/\\') . '/' . $file_name;
    }
}
