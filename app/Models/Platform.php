<?php

class Platform {
    public static function countByStop($stop_id = null, $active_only = false) {
        $db = new Database();
        $where_parts = [];
        $params = [];

        if ($stop_id !== null) {
            $where_parts[] = 'stop_id = :stop_id';
            $params[':stop_id'] = $stop_id;
        }
        
        if ($active_only) {
            $where_parts[] = 'active = TRUE';
        }

        $where = $where_parts ? 'WHERE ' . implode(' AND ', $where_parts) : '';
        
        $query = "SELECT COUNT(*) as total FROM platforms $where";
        $result = $db->queryOne($query, $params);
        return (int)($result['total'] ?? 0);
    }

    public static function listByStop($stop_id, $active_only = false) {
        $db = new Database();
        $where = 'WHERE stop_id = :stop_id';
        if ($active_only) {
            $where .= ' AND active = TRUE';
        }
        
        $query = "
            SELECT p.*, s.name as stop_name, s.stop_id as stop_code
            FROM platforms p
            INNER JOIN stops s ON p.stop_id = s.id
            $where
            ORDER BY p.platform_number ASC
        ";
        
        return $db->query($query, [':stop_id' => $stop_id]);
    }

    public static function listAll($limit = 50, $offset = 0) {
        $db = new Database();
        $query = "
            SELECT p.*, s.name as stop_name, s.stop_id as stop_code
            FROM platforms p
            INNER JOIN stops s ON p.stop_id = s.id
            ORDER BY s.name ASC, p.platform_number ASC
            LIMIT :limit OFFSET :offset
        ";
        
        return $db->query($query, [
            ':limit' => $limit,
            ':offset' => $offset
        ]);
    }

    public static function find($id) {
        $db = new Database();
        $query = "
            SELECT p.*, s.name as stop_name, s.stop_id as stop_code
            FROM platforms p
            INNER JOIN stops s ON p.stop_id = s.id
            WHERE p.id = :id
        ";
        return $db->queryOne($query, [':id' => $id]);
    }

    public static function exists($stop_id, $platform_number, $exclude_id = null) {
        $db = new Database();
        $query = "SELECT COUNT(*) as count FROM platforms WHERE stop_id = :stop_id AND platform_number = :platform_number";
        $params = [
            ':stop_id' => $stop_id,
            ':platform_number' => $platform_number
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
            INSERT INTO platforms (
                stop_id, platform_number, platform_type, description, active
            ) VALUES (
                :stop_id, :platform_number, :platform_type, :description, :active
            )
        ";

        return $db->execute($query, [
            ':stop_id' => $data['stop_id'],
            ':platform_number' => $data['platform_number'],
            ':platform_type' => $data['platform_type'] ?? 'regular',
            ':description' => $data['description'] ?? null,
            ':active' => $data['active'] ?? true
        ]);
    }

    public static function update($id, array $data) {
        $db = new Database();
        $query = "
            UPDATE platforms SET
                stop_id = :stop_id,
                platform_number = :platform_number,
                platform_type = :platform_type,
                description = :description,
                active = :active
            WHERE id = :id
        ";

        return $db->execute($query, [
            ':id' => $id,
            ':stop_id' => $data['stop_id'],
            ':platform_number' => $data['platform_number'],
            ':platform_type' => $data['platform_type'] ?? 'regular',
            ':description' => $data['description'] ?? null,
            ':active' => $data['active'] ?? true
        ]);
    }

    public static function delete($id) {
        $db = new Database();
        $query = "DELETE FROM platforms WHERE id = :id";
        return $db->execute($query, [':id' => $id]);
    }

    public static function isUsedInRoutes($id) {
        $db = new Database();
        $query = "SELECT COUNT(*) as count FROM route_stops WHERE platform_id = :id";
        $result = $db->queryOne($query, [':id' => $id]);
        return (int)($result['count'] ?? 0) > 0;
    }
}
