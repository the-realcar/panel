<?php

class AdminRouteVariantsController extends Controller {
    public function index() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('route_variants', 'read');

        $line_filter = isset($_GET['line_id']) && $_GET['line_id'] !== '' ? (int)$_GET['line_id'] : null;
        $active_only = isset($_GET['active']) && $_GET['active'] === '1';
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $per_page = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $per_page;

        $total_items = RouteVariant::countByLine($line_filter, $active_only);
        $total_pages = max(1, (int)ceil($total_items / $per_page));
        $variants = RouteVariant::listAll($per_page, $offset, $active_only);

        if ($line_filter !== null) {
            $variants = array_values(array_filter($variants, static function ($row) use ($line_filter) {
                return (int)$row['line_id'] === (int)$line_filter;
            }));
        }

        $lines = Line::listActive();

        $this->render('admin/route-variants/index', [
            'page_title' => 'Warianty tras',
            'variants' => $variants,
            'lines' => $lines,
            'line_filter' => $line_filter,
            'active_only' => $active_only,
            'page' => $page,
            'total_pages' => $total_pages,
            'rbac' => $rbac
        ]);
    }

    public function create() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('route_variants', 'create');

        $errors = [];
        $lines = Line::listActive();
        $form_data = ['is_active' => 'on', 'variant_type' => 'normal'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/admin/route-variants/create.php');
            }

            $form_data = $_POST;
            $validator = new Validator($form_data);
            $validator->required('line_id', 'Linia jest wymagana.')
                      ->required('variant_name', 'Nazwa wariantu jest wymagana.')
                      ->required('variant_type', 'Typ wariantu jest wymagany.');

            if ($validator->fails()) {
                $errors = $validator->getErrors();
            }

            if (empty($errors)) {
                $new_id = RouteVariant::create([
                    'line_id' => (int)$form_data['line_id'],
                    'variant_name' => trim($form_data['variant_name']),
                    'variant_type' => $form_data['variant_type'],
                    'direction' => trim($form_data['direction'] ?? '') !== '' ? trim($form_data['direction']) : null,
                    'is_active' => isset($form_data['is_active']) ? 'true' : 'false'
                ]);

                AuditLog::log('route_variant.create', 'route_variants', $new_id, null, [
                    'line_id' => (int)$form_data['line_id'],
                    'variant_name' => trim($form_data['variant_name'])
                ]);

                setFlashMessage('success', 'Wariant trasy zostal dodany.');
                $this->redirectTo('/admin/route-variants/index.php');
            }
        }

        $this->render('admin/route-variants/create', [
            'page_title' => 'Dodaj wariant trasy',
            'errors' => $errors,
            'form_data' => $form_data,
            'lines' => $lines
        ]);
    }

    public function edit() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('route_variants', 'update');

        $variant_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($variant_id <= 0) {
            setFlashMessage('error', 'Nieprawidlowy ID wariantu.');
            $this->redirectTo('/admin/route-variants/index.php');
        }

        $variant = RouteVariant::find($variant_id);
        if (!$variant) {
            setFlashMessage('error', 'Wariant trasy nie istnieje.');
            $this->redirectTo('/admin/route-variants/index.php');
        }

        $errors = [];
        $lines = Line::listActive();
        $form_data = $variant;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/admin/route-variants/edit.php?id=' . $variant_id);
            }

            $form_data = array_merge($variant, $_POST);
            $validator = new Validator($form_data);
            $validator->required('line_id', 'Linia jest wymagana.')
                      ->required('variant_name', 'Nazwa wariantu jest wymagana.')
                      ->required('variant_type', 'Typ wariantu jest wymagany.');

            if ($validator->fails()) {
                $errors = $validator->getErrors();
            }

            if (empty($errors)) {
                RouteVariant::update($variant_id, [
                    'line_id' => (int)$form_data['line_id'],
                    'variant_name' => trim($form_data['variant_name']),
                    'variant_type' => $form_data['variant_type'],
                    'direction' => trim($form_data['direction'] ?? '') !== '' ? trim($form_data['direction']) : null,
                    'is_active' => isset($form_data['is_active']) ? 'true' : 'false'
                ]);

                AuditLog::log('route_variant.update', 'route_variants', $variant_id, [
                    'line_id' => $variant['line_id'],
                    'variant_name' => $variant['variant_name']
                ], [
                    'line_id' => (int)$form_data['line_id'],
                    'variant_name' => trim($form_data['variant_name'])
                ]);

                setFlashMessage('success', 'Wariant trasy zostal zaktualizowany.');
                $this->redirectTo('/admin/route-variants/index.php');
            }
        }

        $this->render('admin/route-variants/edit', [
            'page_title' => 'Edytuj wariant trasy',
            'errors' => $errors,
            'form_data' => $form_data,
            'lines' => $lines,
            'variant_id' => $variant_id
        ]);
    }

    public function stops() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('route_variants', 'update');

        $variant_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($variant_id <= 0) {
            setFlashMessage('error', 'Nieprawidlowy ID wariantu trasy.');
            $this->redirectTo('/admin/route-variants/index.php');
        }

        $variant = RouteVariant::find($variant_id);
        if (!$variant) {
            setFlashMessage('error', 'Nie znaleziono wariantu trasy.');
            $this->redirectTo('/admin/route-variants/index.php');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo('/admin/route-variants/stops.php?id=' . $variant_id);
            }

            $action = $_POST['action'] ?? '';

            if ($action === 'add') {
                $platform_id = (int)($_POST['platform_id'] ?? 0);
                $travel_time = trim((string)($_POST['travel_time_minutes'] ?? ''));
                $travel_time = $travel_time !== '' ? (int)$travel_time : null;
                $is_timing_point = isset($_POST['is_timing_point']) ? 'true' : 'false';

                if ($platform_id <= 0) {
                    setFlashMessage('error', 'Wybierz stanowisko do dodania.');
                } elseif (RouteStop::exists($variant_id, $platform_id)) {
                    setFlashMessage('error', 'To stanowisko jest juz przypisane do wariantu.');
                } else {
                    $sequence = RouteStop::getMaxSequence($variant_id) + 1;
                    $new_ok = RouteStop::create([
                        'route_variant_id' => $variant_id,
                        'platform_id' => $platform_id,
                        'stop_sequence' => $sequence,
                        'travel_time_minutes' => $travel_time,
                        'is_timing_point' => $is_timing_point
                    ]);

                    if ($new_ok) {
                        AuditLog::log('route_stop.create', 'route_stops', null, null, [
                            'route_variant_id' => $variant_id,
                            'platform_id' => $platform_id,
                            'stop_sequence' => $sequence
                        ]);
                        setFlashMessage('success', 'Przystanek zostal dodany do wariantu.');
                    }
                }
            }

            if ($action === 'delete') {
                $stop_id = (int)($_POST['stop_id'] ?? 0);
                $stop = RouteStop::find($stop_id);

                if (!$stop || (int)$stop['route_variant_id'] !== $variant_id) {
                    setFlashMessage('error', 'Nie znaleziono przystanku na trasie.');
                } else {
                    RouteStop::delete($stop_id);
                    RouteStop::reorderSequences($variant_id);
                    AuditLog::log('route_stop.delete', 'route_stops', $stop_id, [
                        'route_variant_id' => $variant_id,
                        'platform_id' => $stop['platform_id'],
                        'stop_sequence' => $stop['stop_sequence']
                    ], null);
                    setFlashMessage('success', 'Przystanek zostal usuniety z trasy.');
                }
            }

            if ($action === 'update') {
                $stop_id = (int)($_POST['stop_id'] ?? 0);
                $stop = RouteStop::find($stop_id);

                if (!$stop || (int)$stop['route_variant_id'] !== $variant_id) {
                    setFlashMessage('error', 'Nie znaleziono przystanku na trasie.');
                } else {
                    $travel_time = trim((string)($_POST['travel_time_minutes'] ?? ''));
                    $travel_time = $travel_time !== '' ? (int)$travel_time : null;
                    $is_timing_point = isset($_POST['is_timing_point']) ? 'true' : 'false';

                    RouteStop::update($stop_id, [
                        'platform_id' => $stop['platform_id'],
                        'stop_sequence' => $stop['stop_sequence'],
                        'travel_time_minutes' => $travel_time,
                        'is_timing_point' => $is_timing_point
                    ]);

                    AuditLog::log('route_stop.update', 'route_stops', $stop_id, [
                        'travel_time_minutes' => $stop['travel_time_minutes'],
                        'is_timing_point' => $stop['is_timing_point']
                    ], [
                        'travel_time_minutes' => $travel_time,
                        'is_timing_point' => $is_timing_point
                    ]);

                    setFlashMessage('success', 'Zaktualizowano parametry przystanku.');
                }
            }

            if ($action === 'move_up' || $action === 'move_down') {
                $stop_id = (int)($_POST['stop_id'] ?? 0);
                $stop = RouteStop::find($stop_id);

                if ($stop && (int)$stop['route_variant_id'] === $variant_id) {
                    $current_seq = (int)$stop['stop_sequence'];
                    $target_seq = $action === 'move_up' ? $current_seq - 1 : $current_seq + 1;

                    $db = new Database();
                    $neighbor = $db->queryOne(
                        "SELECT id, stop_sequence FROM route_stops WHERE route_variant_id = :variant_id AND stop_sequence = :seq",
                        [':variant_id' => $variant_id, ':seq' => $target_seq]
                    );

                    if ($neighbor) {
                        $db->beginTransaction();
                        try {
                            $db->execute("UPDATE route_stops SET stop_sequence = -1 WHERE id = :id", [':id' => $stop['id']]);
                            $db->execute("UPDATE route_stops SET stop_sequence = :seq WHERE id = :id", [':seq' => $current_seq, ':id' => $neighbor['id']]);
                            $db->execute("UPDATE route_stops SET stop_sequence = :seq WHERE id = :id", [':seq' => $target_seq, ':id' => $stop['id']]);
                            $db->commit();
                            AuditLog::log('route_stop.reorder', 'route_stops', $stop_id, null, [
                                'route_variant_id' => $variant_id,
                                'from_sequence' => $current_seq,
                                'to_sequence' => $target_seq
                            ]);
                            setFlashMessage('success', 'Zmieniono kolejnosc przystanku.');
                        } catch (Exception $e) {
                            if ($db->inTransaction()) {
                                $db->rollback();
                            }
                            setFlashMessage('error', 'Nie udalo sie zmienic kolejnosci przystanku.');
                        }
                    }
                }
            }

            $this->redirectTo('/admin/route-variants/stops.php?id=' . $variant_id);
        }

        $route_stops = RouteStop::listByVariant($variant_id);
        $platforms = Platform::listAll(5000, 0);

        $this->render('admin/route-variants/stops', [
            'page_title' => 'Builder trasy',
            'variant' => $variant,
            'route_stops' => $route_stops,
            'platforms' => $platforms
        ]);
    }

    public function delete() {
        requireLogin();

        $rbac = new RBAC();
        $rbac->requirePermission('route_variants', 'delete');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            setFlashMessage('error', 'Nieprawidlowe zadanie.');
            $this->redirectTo('/admin/route-variants/index.php');
        }

        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlashMessage('error', 'Nieprawidlowy token CSRF.');
            $this->redirectTo('/admin/route-variants/index.php');
        }

        $variant_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($variant_id <= 0) {
            setFlashMessage('error', 'Nieprawidlowy ID wariantu.');
            $this->redirectTo('/admin/route-variants/index.php');
        }

        $variant = RouteVariant::find($variant_id);
        if (!$variant) {
            setFlashMessage('error', 'Wariant trasy nie istnieje.');
            $this->redirectTo('/admin/route-variants/index.php');
        }

        try {
            RouteVariant::delete($variant_id);
            AuditLog::log('route_variant.delete', 'route_variants', $variant_id, [
                'line_id' => $variant['line_id'],
                'variant_name' => $variant['variant_name']
            ], null);
            setFlashMessage('success', 'Wariant trasy zostal usuniety.');
        } catch (Exception $e) {
            error_log('Error deleting route variant: ' . $e->getMessage());
            setFlashMessage('error', 'Nie mozna usunac wariantu. Prawdopodobnie jest uzywany w trasach lub kartach drogowych.');
        }

        $this->redirectTo('/admin/route-variants/index.php');
    }
}
