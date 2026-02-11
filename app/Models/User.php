<?php

class User {
    public static function countByStatus($status_filter = '') {
        $db = new Database();
        $where = '';

        if ($status_filter === 'active') {
            $where = 'WHERE u.active = TRUE';
        } elseif ($status_filter === 'inactive') {
            $where = 'WHERE u.active = FALSE';
        }

        $query = "SELECT COUNT(*) as total FROM users u $where";
        $result = $db->queryOne($query);
        return (int)($result['total'] ?? 0);
    }

    public static function listWithRolesPositions($status_filter = '', $limit = 20, $offset = 0) {
        $db = new Database();
        $where = '';

        if ($status_filter === 'active') {
            $where = 'WHERE u.active = TRUE';
        } elseif ($status_filter === 'inactive') {
            $where = 'WHERE u.active = FALSE';
        }

        $query = "
            SELECT u.*,
                   STRING_AGG(DISTINCT r.name, ', ') as roles,
                   STRING_AGG(DISTINCT p.name, ', ') as positions
            FROM users u
            LEFT JOIN user_roles ur ON u.id = ur.user_id
            LEFT JOIN roles r ON ur.role_id = r.id
            LEFT JOIN user_positions up ON u.id = up.user_id
            LEFT JOIN positions p ON up.position_id = p.id
            $where
            GROUP BY u.id
            ORDER BY u.username ASC
            LIMIT :limit OFFSET :offset
        ";

        return $db->query($query, [
            ':limit' => $limit,
            ':offset' => $offset
        ]);
    }

    public static function find($id) {
        $db = new Database();
        $query = "SELECT * FROM users WHERE id = :id";
        return $db->queryOne($query, [':id' => $id]);
    }

    public static function updateStatus($id, $active) {
        $db = new Database();
        $query = "UPDATE users SET active = :active, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        return $db->execute($query, [
            ':active' => $active ? 'true' : 'false',
            ':id' => $id
        ]);
    }
}
