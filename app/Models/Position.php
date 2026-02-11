<?php

class Position {
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
            LEFT JOIN user_positions up ON p.id = up.position_id
            GROUP BY p.id, d.name
            ORDER BY p.name ASC
            LIMIT :limit OFFSET :offset
        ";

        return $db->query($query, [
            ':limit' => $limit,
            ':offset' => $offset
        ]);
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

        return $db->execute($query, [
            ':name' => $data['name'],
            ':department_id' => $data['department_id'],
            ':max_count' => $data['max_count'],
            ':description' => $data['description'],
            ':active' => $data['active']
        ]);
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
            ORDER BY p.name ASC
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
}
