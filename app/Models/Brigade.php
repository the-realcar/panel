<?php

class Brigade {
    public static function normalizeBrigadeNumber($brigade_number) {
        $brigade_number = trim((string)$brigade_number);
        if (strpos($brigade_number, '/') !== false) {
            $parts = explode('/', $brigade_number);
            $brigade_number = trim((string)end($parts));
        }

        return $brigade_number;
    }

    public static function countByLine($line_id = null, $active_only = false) {
        $db = new Database();
        $where_parts = [];
        $params = [];

        if ($line_id !== null) {
            $where_parts[] = 'b.line_id = :line_id';
            $params[':line_id'] = $line_id;
        }
        
        if ($active_only) {
            $where_parts[] = 'b.active = TRUE';
        }

        $where = $where_parts ? 'WHERE ' . implode(' AND ', $where_parts) : '';
        
        $query = "SELECT COUNT(*) as total FROM brigades b $where";
        $result = $db->queryOne($query, $params);
        return (int)($result['total'] ?? 0);
    }

    public static function listByLine($line_id, $active_only = false) {
        $db = new Database();
        $where = 'WHERE b.line_id = :line_id';
        if ($active_only) {
            $where .= ' AND b.active = TRUE';
        }
        
        $query = "
            SELECT b.*, l.line_number, l.name as line_name
            FROM brigades b
            INNER JOIN lines l ON b.line_id = l.id
            $where
            ORDER BY b.brigade_number ASC
        ";
        
        return $db->query($query, [':line_id' => $line_id]);
    }

    public static function listAll($limit = 50, $offset = 0, $active_only = false, $line_id = null) {
        $db = new Database();
        $where_parts = [];
        $params = [
            ':limit' => $limit,
            ':offset' => $offset
        ];

        if ($active_only) {
            $where_parts[] = 'b.active = TRUE';
        }

        if ($line_id !== null) {
            $where_parts[] = 'b.line_id = :line_id';
            $params[':line_id'] = $line_id;
        }

        $where = $where_parts ? 'WHERE ' . implode(' AND ', $where_parts) : '';
        
        $query = "
            SELECT b.*, l.line_number, l.name as line_name
            FROM brigades b
            INNER JOIN lines l ON b.line_id = l.id
            $where
            ORDER BY l.line_number ASC, b.brigade_number ASC
            LIMIT :limit OFFSET :offset
        ";
        
        return $db->query($query, $params);
    }

    public static function listActive() {
        $db = new Database();
        $query = "
            SELECT b.*, l.line_number, l.name as line_name
            FROM brigades b
            INNER JOIN lines l ON b.line_id = l.id
            WHERE b.active = TRUE
            ORDER BY l.line_number ASC, b.brigade_number ASC
        ";
        return $db->query($query);
    }

    public static function find($id) {
        $db = new Database();
        $query = "
            SELECT b.*, l.line_number, l.name as line_name
            FROM brigades b
            INNER JOIN lines l ON b.line_id = l.id
            WHERE b.id = :id
        ";
        return $db->queryOne($query, [':id' => $id]);
    }

    public static function exists($line_id, $brigade_number, $exclude_id = null) {
        $db = new Database();
        $query = "SELECT COUNT(*) as count FROM brigades WHERE line_id = :line_id AND brigade_number = :brigade_number";
        $params = [
            ':line_id' => $line_id,
            ':brigade_number' => $brigade_number
        ];

        if ($exclude_id) {
            $query .= " AND id != :id";
            $params[':id'] = $exclude_id;
        }

        $result = $db->queryOne($query, $params);
        return (int)($result['count'] ?? 0) > 0;
    }

    public static function create(array $data) {
        $db = new Database();
        $query = "
            INSERT INTO brigades (
                line_id, brigade_number, is_peak, peak_type, shift_start, shift_end, default_vehicle_type, description, active
            ) VALUES (
                :line_id, :brigade_number, :is_peak, :peak_type, :shift_start, :shift_end, :default_vehicle_type, :description, :active
            )
        ";

        return $db->execute($query, [
            ':line_id' => $data['line_id'],
            ':brigade_number' => self::normalizeBrigadeNumber($data['brigade_number']),
            ':is_peak' => $data['is_peak'] ?? false,
            ':peak_type' => $data['peak_type'] ?? null,
            ':shift_start' => !empty($data['shift_start']) ? $data['shift_start'] : null,
            ':shift_end' => !empty($data['shift_end']) ? $data['shift_end'] : null,
            ':default_vehicle_type' => $data['default_vehicle_type'] ?? null,
            ':description' => $data['description'] ?? null,
            ':active' => $data['active'] ?? true
        ]);
    }

    public static function update($id, array $data) {
        $db = new Database();
        $query = "
            UPDATE brigades SET
                line_id = :line_id,
                brigade_number = :brigade_number,
                is_peak = :is_peak,
                peak_type = :peak_type,
                shift_start = :shift_start,
                shift_end = :shift_end,
                default_vehicle_type = :default_vehicle_type,
                description = :description,
                active = :active
            WHERE id = :id
        ";

        return $db->execute($query, [
            ':id' => $id,
            ':line_id' => $data['line_id'],
            ':brigade_number' => self::normalizeBrigadeNumber($data['brigade_number']),
            ':is_peak' => $data['is_peak'] ?? false,
            ':peak_type' => $data['peak_type'] ?? null,
            ':shift_start' => !empty($data['shift_start']) ? $data['shift_start'] : null,
            ':shift_end' => !empty($data['shift_end']) ? $data['shift_end'] : null,
            ':default_vehicle_type' => $data['default_vehicle_type'] ?? null,
            ':description' => $data['description'] ?? null,
            ':active' => $data['active'] ?? true
        ]);
    }

    public static function delete($id) {
        $db = new Database();
        $query = "DELETE FROM brigades WHERE id = :id";
        return $db->execute($query, [':id' => $id]);
    }

    public static function isUsedInSchedules($id) {
        $db = new Database();
        $query = "SELECT COUNT(*) as count FROM schedules WHERE brigade_id = :id";
        $result = $db->queryOne($query, [':id' => $id]);
        return (int)($result['count'] ?? 0) > 0;
    }
}
