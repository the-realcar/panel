<?php

class Schedule {
    public static function getTodaySchedules($user_id, $today) {
        $db = new Database();
        $query = "
            SELECT s.*, 
                   v.vehicle_number, v.model, 
                   l.line_number, l.name as line_name
            FROM schedules s
            LEFT JOIN vehicles v ON s.vehicle_id = v.id
            LEFT JOIN lines l ON s.line_id = l.id
            WHERE s.user_id = :user_id AND s.schedule_date = :today
            ORDER BY s.start_time ASC
        ";

        return $db->query($query, [
            ':user_id' => $user_id,
            ':today' => $today
        ]);
    }

    public static function getUserStats($user_id, $today) {
        $db = new Database();
        $query = "
            SELECT 
                COUNT(CASE WHEN schedule_date = :today THEN 1 END) as today_count,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count,
                COUNT(CASE WHEN schedule_date >= :today THEN 1 END) as upcoming_count
            FROM schedules
            WHERE user_id = :user_id
        ";

        return $db->queryOne($query, [
            ':user_id' => $user_id,
            ':today' => $today
        ]);
    }

    public static function countForUserInRange($user_id, $today, $end_date, $status_filter = 'all') {
        $db = new Database();
        $where = ['s.user_id = :user_id', 's.schedule_date BETWEEN :today AND :end_date'];
        $params = [
            ':user_id' => $user_id,
            ':today' => $today,
            ':end_date' => $end_date
        ];

        if ($status_filter !== 'all') {
            $where[] = 's.status = :status';
            $params[':status'] = $status_filter;
        }

        $where_sql = implode(' AND ', $where);
        $query = "SELECT COUNT(*) as total FROM schedules s WHERE $where_sql";
        $result = $db->queryOne($query, $params);
        return (int)($result['total'] ?? 0);
    }

    public static function listForUserInRange($user_id, $today, $end_date, $status_filter, $limit, $offset) {
        $db = new Database();
        $where = ['s.user_id = :user_id', 's.schedule_date BETWEEN :today AND :end_date'];
        $params = [
            ':user_id' => $user_id,
            ':today' => $today,
            ':end_date' => $end_date
        ];

        if ($status_filter !== 'all') {
            $where[] = 's.status = :status';
            $params[':status'] = $status_filter;
        }

        $where_sql = implode(' AND ', $where);
        $query = "
            SELECT s.*, 
                   v.vehicle_number, v.model, v.registration_plate,
                   l.line_number, l.name as line_name
            FROM schedules s
            LEFT JOIN vehicles v ON s.vehicle_id = v.id
            LEFT JOIN lines l ON s.line_id = l.id
            WHERE $where_sql
            ORDER BY s.schedule_date ASC, s.start_time ASC
            LIMIT :limit OFFSET :offset
        ";

        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        return $db->query($query, $params);
    }

    public static function listForDate($date, $limit = 100, $offset = 0) {
        $db = new Database();
        $query = "
            SELECT s.*,
                   u.first_name, u.last_name, u.employee_id,
                   v.vehicle_number, v.model, v.registration_plate,
                   l.line_number, l.name as line_name,
                   b.brigade_number
            FROM schedules s
            LEFT JOIN users u ON s.user_id = u.id
            LEFT JOIN vehicles v ON s.vehicle_id = v.id
            LEFT JOIN lines l ON s.line_id = l.id
            LEFT JOIN brigades b ON s.brigade_id = b.id
            WHERE s.schedule_date = :date
            ORDER BY s.start_time ASC
            LIMIT :limit OFFSET :offset
        ";

        $params = [
            ':date' => $date,
            ':limit' => $limit,
            ':offset' => $offset
        ];

        return $db->query($query, $params);
    }

    public static function create($data) {
        $db = new Database();
        $query = "
            INSERT INTO schedules (user_id, vehicle_id, line_id, brigade_id, schedule_date, 
                                   start_time, end_time, status, notes)
            VALUES (:user_id, :vehicle_id, :line_id, :brigade_id, :schedule_date,
                    :start_time, :end_time, :status, :notes)
            RETURNING id
        ";

        $params = [
            ':user_id' => $data['user_id'],
            ':vehicle_id' => $data['vehicle_id'],
            ':line_id' => $data['line_id'],
            ':brigade_id' => $data['brigade_id'] ?? null,
            ':schedule_date' => $data['schedule_date'],
            ':start_time' => $data['start_time'],
            ':end_time' => $data['end_time'],
            ':status' => $data['status'] ?? 'scheduled',
            ':notes' => $data['notes'] ?? null
        ];

        $result = $db->queryOne($query, $params);
        return $result ? $result['id'] : null;
    }
}
