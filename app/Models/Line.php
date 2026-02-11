<?php

class Line {
    public static function countByType($type = '') {
        $db = new Database();
        $where = '';
        $params = [];

        if ($type) {
            $where = 'WHERE line_type = :type';
            $params[':type'] = $type;
        }

        $query = "SELECT COUNT(*) as total FROM lines $where";
        $result = $db->queryOne($query, $params);
        return (int)($result['total'] ?? 0);
    }

    public static function listByType($type = '', $limit = 20, $offset = 0) {
        $db = new Database();
        $where = '';
        $params = [];

        if ($type) {
            $where = 'WHERE line_type = :type';
            $params[':type'] = $type;
        }

        $query = "
            SELECT * FROM lines
            $where
            ORDER BY line_number ASC
            LIMIT :limit OFFSET :offset
        ";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        return $db->query($query, $params);
    }

    public static function find($id) {
        $db = new Database();
        $query = "SELECT * FROM lines WHERE id = :id";
        return $db->queryOne($query, [':id' => $id]);
    }

    public static function existsByNumber($line_number, $exclude_id = null) {
        $db = new Database();
        $query = "SELECT COUNT(*) as count FROM lines WHERE line_number = :line_number";
        $params = [':line_number' => $line_number];

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
            INSERT INTO lines (
                line_number, name, route_description, line_type, active
            ) VALUES (
                :line_number, :name, :route_description, :line_type, :active
            )
        ";

        return $db->execute($query, [
            ':line_number' => $data['line_number'],
            ':name' => $data['name'],
            ':route_description' => $data['route_description'],
            ':line_type' => $data['line_type'],
            ':active' => $data['active']
        ]);
    }

    public static function update($id, array $data) {
        $db = new Database();
        $query = "
            UPDATE lines SET
                line_number = :line_number,
                name = :name,
                route_description = :route_description,
                line_type = :line_type,
                active = :active,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ";

        return $db->execute($query, [
            ':line_number' => $data['line_number'],
            ':name' => $data['name'],
            ':route_description' => $data['route_description'],
            ':line_type' => $data['line_type'],
            ':active' => $data['active'],
            ':id' => $id
        ]);
    }

    public static function delete($id) {
        $db = new Database();
        $query = "DELETE FROM lines WHERE id = :id";
        return $db->execute($query, [':id' => $id]);
    }

    public static function listActive() {
        $db = new Database();
        $query = "SELECT id, line_number, name FROM lines WHERE active = TRUE ORDER BY line_number";
        return $db->query($query);
    }
}
