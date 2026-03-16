<?php

class ScheduleGeneratorController extends Controller {

    private function requireManagement() {
        requireLogin();
        $rbac = new RBAC();
        if (!$rbac->hasRole('Zarząd') && !$rbac->isAdmin()) {
            setFlashMessage('error', 'Brak uprawnien do generatora rozkladow.');
            $this->redirectTo('/index.php');
        }
        return $rbac;
    }

    public function index() {
        $rbac = $this->requireManagement();
        $lines = Line::listActive();

        $this->render('schedule-generator/index', [
            'page_title' => 'Generator Rozkladow Pasazerow',
            'lines' => $lines,
            'rbac' => $rbac,
        ]);
    }

    public function generate() {
        $rbac = $this->requireManagement();

        $line_id   = isset($_GET['line_id'])   ? (int)$_GET['line_id']   : 0;
        $variant_id = isset($_GET['variant_id']) ? (int)$_GET['variant_id'] : 0;

        if (!$line_id) {
            setFlashMessage('error', 'Wybierz linie.');
            $this->redirectTo('/management/schedule-generator/index.php');
        }

        $line = Line::find($line_id);
        if (!$line) {
            setFlashMessage('error', 'Linia nie istnieje.');
            $this->redirectTo('/management/schedule-generator/index.php');
        }

        $variants = RouteVariant::listByLine($line_id, true);

        $selected_variant = null;
        $stops            = [];
        $timetable        = null;

        if ($variant_id) {
            $selected_variant = RouteVariant::find($variant_id);
            if (!$selected_variant || (int)$selected_variant['line_id'] !== $line_id) {
                setFlashMessage('error', 'Wybrany wariant trasy nie nalezy do tej linii.');
                $this->redirectTo('/management/schedule-generator/generate.php?line_id=' . $line_id);
            }

            $stops     = RouteStop::listByVariant($variant_id);
            $timetable = $this->buildTimetable($line_id, $selected_variant['direction'], $stops);
        }

        $this->render('schedule-generator/generate', [
            'page_title' => 'Generator Rozkladow - Linia ' . e($line['line_number']),
            'line'             => $line,
            'variants'         => $variants,
            'selected_variant' => $selected_variant,
            'stops'            => $stops,
            'timetable'        => $timetable,
            'rbac'             => $rbac,
        ]);
    }

    /**
     * Build a timetable as an array of departures, each with computed times at every stop.
     *
     * Returns:
     *   [
     *     [
     *       'departure_time'  => 'HH:MM',   // time at first stop
     *       'brigade_number'  => '...',
     *       'stop_times'      => ['HH:MM', 'HH:MM', ...],  // one entry per stop in sequence
     *     ],
     *     ...
     *   ]
     * or null when no departures exist.
     */
    private function buildTimetable(int $line_id, ?string $direction, array $stops): ?array {
        $db = new Database();

        $sql = "
            SELECT bd.departure_time, bd.direction, b.brigade_number
            FROM brigade_departures bd
            INNER JOIN brigades b ON bd.brigade_id = b.id
            WHERE b.line_id = :line_id
              AND b.active = TRUE
        ";
        $params = [':line_id' => $line_id];

        if ($direction !== null && $direction !== '') {
            $sql .= " AND bd.direction = :direction";
            $params[':direction'] = $direction;
        }

        $sql .= " ORDER BY bd.departure_time ASC, b.brigade_number ASC";

        $departures = $db->query($sql, $params);

        if (empty($departures) || empty($stops)) {
            return null;
        }

        // Pre-compute cumulative travel offsets (in seconds) for every stop.
        $offsets = [];
        $cumulative = 0;
        foreach ($stops as $stop) {
            if ((int)$stop['stop_sequence'] > 1 && !empty($stop['travel_time_minutes'])) {
                $cumulative += (int)$stop['travel_time_minutes'] * 60;
            }
            $offsets[] = $cumulative;
        }

        $rows = [];
        foreach ($departures as $dep) {
            $base = strtotime('1970-01-01 ' . $dep['departure_time']);
            $stop_times = [];
            foreach ($offsets as $offset) {
                $stop_times[] = date('H:i', $base + $offset);
            }

            $rows[] = [
                'departure_time' => date('H:i', strtotime('1970-01-01 ' . $dep['departure_time'])),
                'brigade_number' => $dep['brigade_number'],
                'stop_times'     => $stop_times,
            ];
        }

        return $rows;
    }
}
