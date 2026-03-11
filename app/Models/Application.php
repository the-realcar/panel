<?php

class Application {

    public static function typeLabel(string $type): string {
        $labels = [
            'kzw'               => 'KZW',
            'cancel_duty'       => 'Anulowanie służby',
            'day_off'           => 'Dzień wolny',
            'vacation'          => 'Urlop',
            'permanent_vehicle' => 'Stały pojazd',
            'change_vehicle'    => 'Zmiana stałego pojazdu',
            'no_vehicle_assign' => 'Nieprzydzielanie pojazdów',
            'change_status'     => 'Zmiana etatu',
            'resignation'       => 'Zwolnienie',
        ];
        return $labels[$type] ?? $type;
    }

    public static function statusLabel(string $status): string {
        $labels = [
            'pending'   => 'Oczekujący',
            'approved'  => 'Zatwierdzony',
            'rejected'  => 'Odrzucony',
            'cancelled' => 'Anulowany',
        ];
        return $labels[$status] ?? $status;
    }

    public static function statusBadgeClass(string $status): string {
        $classes = [
            'pending'   => 'badge-warning',
            'approved'  => 'badge-success',
            'rejected'  => 'badge-danger',
            'cancelled' => 'badge-secondary',
        ];
        return $classes[$status] ?? 'badge-secondary';
    }

    public static function allTypes(): array {
        return [
            'kzw', 'cancel_duty', 'day_off', 'vacation',
            'permanent_vehicle', 'change_vehicle', 'no_vehicle_assign',
            'change_status', 'resignation',
        ];
    }

    public static function create(array $data): ?int {
        $db = new Database();
        $query = "
            INSERT INTO applications
            (user_id, type, execution_date, date_from, date_to, schedule_id,
             vehicle_id, vehicles_json, work_days, reason, notes)
            VALUES
            (:user_id, :type, :execution_date, :date_from, :date_to, :schedule_id,
             :vehicle_id, :vehicles_json, :work_days, :reason, :notes)
            RETURNING id
        ";
        $result = $db->queryOne($query, [
            ':user_id'        => $data['user_id'],
            ':type'           => $data['type'],
            ':execution_date' => $data['execution_date'] ?? null,
            ':date_from'      => $data['date_from'] ?? null,
            ':date_to'        => $data['date_to'] ?? null,
            ':schedule_id'    => $data['schedule_id'] ?? null,
            ':vehicle_id'     => $data['vehicle_id'] ?? null,
            ':vehicles_json'  => isset($data['vehicles_json']) ? json_encode($data['vehicles_json']) : null,
            ':work_days'      => isset($data['work_days']) ? json_encode($data['work_days']) : null,
            ':reason'         => $data['reason'] ?? null,
            ':notes'          => $data['notes'] ?? null,
        ]);
        return $result ? (int)$result['id'] : null;
    }

    public static function getByUser($user_id, $limit = 20, $offset = 0): array {
        $db = new Database();
        $query = "
            SELECT a.*,
                   s.schedule_date, s.start_time as sched_start, s.end_time as sched_end,
                   sl.line_number as sched_line_number,
                   v.nr_poj as vehicle_nr,
                   rv.first_name as reviewer_first, rv.last_name as reviewer_last, rv.username as reviewer_username
            FROM applications a
            LEFT JOIN schedules s ON a.schedule_id = s.id
            LEFT JOIN lines sl ON s.line_id = sl.id
            LEFT JOIN vehicles v ON a.vehicle_id = v.id
            LEFT JOIN users rv ON a.reviewed_by = rv.id
            WHERE a.user_id = :user_id
            ORDER BY a.created_at DESC
            LIMIT :limit OFFSET :offset
        ";
        return $db->query($query, [
            ':user_id' => $user_id,
            ':limit'   => $limit,
            ':offset'  => $offset,
        ]);
    }

    public static function countByUser($user_id): int {
        $db = new Database();
        $result = $db->queryOne("SELECT COUNT(*) as c FROM applications WHERE user_id = :uid", [':uid' => $user_id]);
        return (int)($result['c'] ?? 0);
    }

    public static function getAll($status_filter = '', $type_filter = '', $limit = 20, $offset = 0): array {
        $db = new Database();
        $where = [];
        $params = [':limit' => $limit, ':offset' => $offset];
        if ($status_filter) {
            $where[] = 'a.status = :status';
            $params[':status'] = $status_filter;
        }
        if ($type_filter) {
            $where[] = 'a.type = :type';
            $params[':type'] = $type_filter;
        }
        $where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
        $query = "
            SELECT a.*,
                   u.username, u.first_name, u.last_name,
                   v.nr_poj as vehicle_nr
            FROM applications a
            JOIN users u ON a.user_id = u.id
            LEFT JOIN vehicles v ON a.vehicle_id = v.id
            $where_sql
            ORDER BY
                CASE a.status WHEN 'pending' THEN 0 ELSE 1 END,
                a.created_at DESC
            LIMIT :limit OFFSET :offset
        ";
        return $db->query($query, $params);
    }

    public static function countAll($status_filter = '', $type_filter = ''): int {
        $db = new Database();
        $where = [];
        $params = [];
        if ($status_filter) {
            $where[] = 'a.status = :status';
            $params[':status'] = $status_filter;
        }
        if ($type_filter) {
            $where[] = 'a.type = :type';
            $params[':type'] = $type_filter;
        }
        $where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
        $result = $db->queryOne("SELECT COUNT(*) as c FROM applications a $where_sql", $params);
        return (int)($result['c'] ?? 0);
    }

    public static function find($id): ?array {
        $db = new Database();
        $query = "
            SELECT a.*,
                   u.username, u.first_name, u.last_name, u.email,
                   s.schedule_date, s.start_time as sched_start, s.end_time as sched_end,
                   sl.line_number as sched_line_number, sl.name as sched_line_name,
                   v.nr_poj as vehicle_nr, v.model as vehicle_model, v.reg_plate as vehicle_plate,
                   rv.username as reviewer_username,
                   rv.first_name as reviewer_first, rv.last_name as reviewer_last
            FROM applications a
            JOIN users u ON a.user_id = u.id
            LEFT JOIN schedules s ON a.schedule_id = s.id
            LEFT JOIN lines sl ON s.line_id = sl.id
            LEFT JOIN vehicles v ON a.vehicle_id = v.id
            LEFT JOIN users rv ON a.reviewed_by = rv.id
            WHERE a.id = :id
        ";
        $row = $db->queryOne($query, [':id' => $id]);
        return $row ?: null;
    }

    public static function updateStatus($id, $status, $reviewed_by, $review_notes): void {
        $db = new Database();
        $db->execute("
            UPDATE applications
            SET status = :status, reviewed_by = :reviewer, reviewed_at = NOW(), review_notes = :notes, updated_at = NOW()
            WHERE id = :id
        ", [':status' => $status, ':reviewer' => $reviewed_by, ':notes' => $review_notes, ':id' => $id]);
    }

    public static function cancel($id, $user_id): bool {
        $db = new Database();
        $result = $db->execute("
            UPDATE applications
            SET status = 'cancelled', updated_at = NOW()
            WHERE id = :id AND user_id = :user_id AND status = 'pending'
        ", [':id' => $id, ':user_id' => $user_id]);
        return (bool)$result;
    }
}
