<?php

class Department {
    public static function listAll() {
        $db = new Database();
        $query = "
            SELECT d.*, COUNT(p.id) AS positions_count
            FROM departments d
            LEFT JOIN positions p ON p.department_id = d.id
            GROUP BY d.id
            ORDER BY d.id ASC
        ";
        return $db->query($query);
    }

    public static function listActive() {
        $db = new Database();
        $query = "SELECT id, name FROM departments WHERE active = TRUE ORDER BY id ASC";
        return $db->query($query);
    }

    public static function find($id) {
        $db = new Database();
        return $db->queryOne('SELECT * FROM departments WHERE id = :id', [':id' => $id]);
    }

    public static function existsByName($name, $exclude_id = null) {
        $db = new Database();
        $query = 'SELECT COUNT(*) AS count FROM departments WHERE LOWER(name) = LOWER(:name)';
        $params = [':name' => $name];

        if ($exclude_id !== null) {
            $query .= ' AND id != :id';
            $params[':id'] = $exclude_id;
        }

        $result = $db->queryOne($query, $params);
        return (int)($result['count'] ?? 0) > 0;
    }

    public static function create(array $data) {
        $db = new Database();
        $db->execute(
            'INSERT INTO departments (name, description, active) VALUES (:name, :description, :active)',
            [
                ':name' => $data['name'],
                ':description' => $data['description'] ?? null,
                ':active' => $data['active'] ?? true
            ]
        );

        return $db->lastInsertId('departments_id_seq');
    }

    public static function update($id, array $data) {
        $db = new Database();
        return $db->execute(
            'UPDATE departments SET name = :name, description = :description, active = :active WHERE id = :id',
            [
                ':id' => $id,
                ':name' => $data['name'],
                ':description' => $data['description'] ?? null,
                ':active' => $data['active'] ?? true
            ]
        );
    }

    public static function delete($id) {
        $db = new Database();
        return $db->execute('DELETE FROM departments WHERE id = :id', [':id' => $id]);
    }

    public static function getPositionsCount($id) {
        $db = new Database();
        $result = $db->queryOne('SELECT COUNT(*) AS count FROM positions WHERE department_id = :id', [':id' => $id]);
        return (int)($result['count'] ?? 0);
    }
}
