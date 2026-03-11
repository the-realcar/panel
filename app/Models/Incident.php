<?php

class Incident {
    public static function create(array $data) {
        $db = new Database();
        $query = "
            INSERT INTO incidents 
            (reported_by, vehicle_id, incident_type, severity, title, description, incident_date, status)
            VALUES 
            (:reported_by, :vehicle_id, :incident_type, :severity, :title, :description, :incident_date, 'open')
        ";

        return $db->execute($query, [
            ':reported_by' => $data['reported_by'],
            ':vehicle_id' => $data['vehicle_id'],
            ':incident_type' => $data['incident_type'],
            ':severity' => $data['severity'],
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':incident_date' => $data['incident_date']
        ]);
    }

    public static function getRecentByUser($user_id, $limit = 10) {
        $db = new Database();
        $query = "
            SELECT i.*, v.nr_poj, v.model
            FROM incidents i
            LEFT JOIN vehicles v ON i.vehicle_id = v.id
            WHERE i.reported_by = :user_id
            ORDER BY i.incident_date DESC, i.created_at DESC
            LIMIT :limit
        ";

        return $db->query($query, [
            ':user_id' => $user_id,
            ':limit' => $limit
        ]);
    }

    public static function getRecentForAdmin($limit = 10) {
        $db = new Database();
        $query = "
            SELECT i.*, 
                   v.nr_poj,
                   u.username as reporter_name
            FROM incidents i
            LEFT JOIN vehicles v ON i.vehicle_id = v.id
            LEFT JOIN users u ON i.reported_by = u.id
            ORDER BY i.incident_date DESC
            LIMIT :limit
        ";

        return $db->query($query, [':limit' => $limit]);
    }

    public static function countAll($status_filter = '') {
        $db = new Database();
        $where = '';
        $params = [];

        if ($status_filter !== '') {
            $where = 'WHERE i.status = :status';
            $params[':status'] = $status_filter;
        }

        $query = "SELECT COUNT(*) as total FROM incidents i $where";
        $result = $db->queryOne($query, $params);
        return (int)($result['total'] ?? 0);
    }

    public static function listForAdmin($status_filter = '', $limit = 20, $offset = 0) {
        $db = new Database();
        $where = '';
        $params = [
            ':limit' => $limit,
            ':offset' => $offset
        ];

        if ($status_filter !== '') {
            $where = 'WHERE i.status = :status';
            $params[':status'] = $status_filter;
        }

        $query = "
            SELECT i.*,
                   v.nr_poj,
                   v.model,
                   u.username as reporter_name,
                   ru.username as resolver_name
            FROM incidents i
            LEFT JOIN vehicles v ON i.vehicle_id = v.id
            LEFT JOIN users u ON i.reported_by = u.id
            LEFT JOIN users ru ON i.resolved_by = ru.id
            $where
            ORDER BY i.incident_date DESC, i.created_at DESC
            LIMIT :limit OFFSET :offset
        ";

        return $db->query($query, $params);
    }

    public static function countByStatus($status) {
        $db = new Database();
        $query = "SELECT COUNT(*) as total FROM incidents WHERE status = :status";
        $result = $db->queryOne($query, [':status' => $status]);
        return (int)($result['total'] ?? 0);
    }
}
