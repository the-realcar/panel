<?php

class Position {
    private static function hasSortOrderColumn() {
        $db = new Database();
        return $db->columnExists('positions', 'sort_order');
    }

    public static function countAll() {
        $db = new Database();
        $query = "SELECT COUNT(*) as total FROM positions";
        $result = $db->queryOne($query);
        return (int)($result['total'] ?? 0);
    }

    public static function listWithCounts($limit = 20, $offset = 0) {
        $db = new Database();
        $query = "
            SELECT p.*, 
                   d.name as department_name,
                   COUNT(up.id) as current_count
            FROM positions p
            LEFT JOIN departments d ON p.department_id = d.id
            LEFT JOIN user_positions up ON p.id = up.position_id AND up.active = TRUE
            GROUP BY p.id, d.name
            ORDER BY p.id ASC
            LIMIT :limit OFFSET :offset
        ";

        return $db->query($query, [
            ':limit' => $limit,
            ':offset' => $offset
        ]);
    }

    public static function listStructureByDepartment() {
        $db = new Database();
        $order_by = self::hasSortOrderColumn()
            ? 'd.name ASC NULLS LAST, p.sort_order ASC NULLS LAST, p.name ASC'
            : 'd.name ASC NULLS LAST, p.name ASC';

        $query = "
            SELECT p.*, d.name as department_name, COUNT(up.id) as current_count
            FROM positions p
            LEFT JOIN departments d ON p.department_id = d.id
            LEFT JOIN user_positions up ON p.id = up.position_id AND up.active = TRUE
            GROUP BY p.id, d.name
            ORDER BY " . $order_by . "
        ";

        return $db->query($query);
    }

    public static function listAssignedUsers($position_id) {
        $db = new Database();
        $query = "
            SELECT u.id, u.username, u.first_name, u.last_name, u.active
            FROM user_positions up
            INNER JOIN users u ON u.id = up.user_id
            WHERE up.position_id = :position_id
            ORDER BY u.last_name ASC NULLS LAST, u.first_name ASC NULLS LAST, u.username ASC
        ";

        return $db->query($query, [':position_id' => $position_id]);
    }

    public static function find($id) {
        $db = new Database();
        $query = "SELECT * FROM positions WHERE id = :id";
        return $db->queryOne($query, [':id' => $id]);
    }

    public static function existsByName($name, $exclude_id = null) {
        $db = new Database();
        $query = "SELECT COUNT(*) as count FROM positions WHERE name = :name";
        $params = [':name' => $name];

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
            INSERT INTO positions (
                name, department_id, max_count, description, active
            ) VALUES (
                :name, :department_id, :max_count, :description, :active
            )
        ";

        $db->execute($query, [
            ':name' => $data['name'],
            ':department_id' => $data['department_id'],
            ':max_count' => $data['max_count'],
            ':description' => $data['description'],
            ':active' => $data['active']
        ]);
        return $db->lastInsertId('positions_id_seq');
    }

    public static function update($id, array $data) {
        $db = new Database();
        $query = "
            UPDATE positions SET
                name = :name,
                department_id = :department_id,
                max_count = :max_count,
                description = :description,
                active = :active,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ";

        return $db->execute($query, [
            ':name' => $data['name'],
            ':department_id' => $data['department_id'],
            ':max_count' => $data['max_count'],
            ':description' => $data['description'],
            ':active' => $data['active'],
            ':id' => $id
        ]);
    }

    public static function delete($id) {
        $db = new Database();
        return $db->execute('DELETE FROM positions WHERE id = :id', [':id' => $id]);
    }

    public static function listActive() {
        $db = new Database();
        $query = "SELECT id, name FROM positions WHERE active = TRUE ORDER BY name ASC";
        return $db->query($query);
    }

    public static function getUserPositions($user_id) {
        $db = new Database();
        $query = "
            SELECT up.id as assignment_id, p.id as position_id, p.name
            FROM user_positions up
            INNER JOIN positions p ON up.position_id = p.id
            WHERE up.user_id = :user_id
            ORDER BY p.id ASC
        ";

        return $db->query($query, [':user_id' => $user_id]);
    }

    public static function assignToUser($user_id, $position_id) {
        $db = new Database();
        $query = "INSERT INTO user_positions (user_id, position_id) VALUES (:user_id, :position_id)";
        return $db->execute($query, [
            ':user_id' => $user_id,
            ':position_id' => $position_id
        ]);
    }

    public static function removeFromUser($assignment_id, $user_id) {
        $db = new Database();
        $query = "DELETE FROM user_positions WHERE id = :id AND user_id = :user_id";
        return $db->execute($query, [
            ':id' => $assignment_id,
            ':user_id' => $user_id
        ]);
    }

    public static function assignmentExists($user_id, $position_id) {
        $db = new Database();
        $query = "SELECT COUNT(*) as count FROM user_positions WHERE user_id = :user_id AND position_id = :position_id";
        $result = $db->queryOne($query, [
            ':user_id' => $user_id,
            ':position_id' => $position_id
        ]);

        return (int)($result['count'] ?? 0) > 0;
    }

    public static function hasAssignments($position_id) {
        $db = new Database();
        $result = $db->queryOne(
            'SELECT COUNT(*) as count FROM user_positions WHERE position_id = :position_id',
            [':position_id' => $position_id]
        );

        return (int)($result['count'] ?? 0) > 0;
    }

    public static function reorderWithinDepartment($position_id, $direction) {
        if (!self::hasSortOrderColumn()) {
            throw new RuntimeException('Kolumna sort_order nie jest dostepna w tabeli positions.');
        }

        $db = new Database();
        $position = $db->queryOne(
            'SELECT id, department_id, sort_order FROM positions WHERE id = :id',
            [':id' => $position_id]
        );

        if (!$position) {
            return false;
        }

        $current_order = (int)($position['sort_order'] ?? 0);
        $department_id = $position['department_id'];

        $operator = $direction === 'up' ? '<' : '>';
        $order = $direction === 'up' ? 'DESC' : 'ASC';

        $neighbor = $db->queryOne(
            'SELECT id, sort_order
             FROM positions
             WHERE ((department_id IS NULL AND :department_id IS NULL) OR department_id = :department_id)
               AND sort_order ' . $operator . ' :sort_order
             ORDER BY sort_order ' . $order . '
             LIMIT 1',
            [
                ':department_id' => $department_id,
                ':sort_order' => $current_order
            ]
        );

        if (!$neighbor) {
            return false;
        }

        $db->beginTransaction();
        try {
            $db->execute('UPDATE positions SET sort_order = -1 WHERE id = :id', [':id' => $position_id]);
            $db->execute('UPDATE positions SET sort_order = :sort_order WHERE id = :id', [
                ':sort_order' => $current_order,
                ':id' => $neighbor['id']
            ]);
            $db->execute('UPDATE positions SET sort_order = :sort_order WHERE id = :id', [
                ':sort_order' => (int)$neighbor['sort_order'],
                ':id' => $position_id
            ]);
            $db->commit();
            return true;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollback();
            }
            throw $e;
        }
    }
}
