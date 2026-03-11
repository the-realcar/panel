<?php

class Role {
    public static function getPermissionDefinition() {
        return [
            'users' => ['label' => 'Użytkownicy', 'actions' => ['read', 'create', 'update', 'delete']],
            'vehicles' => ['label' => 'Pojazdy', 'actions' => ['read', 'create', 'update', 'delete']],
            'lines' => ['label' => 'Linie', 'actions' => ['read', 'create', 'update', 'delete']],
            'positions' => ['label' => 'Stanowiska', 'actions' => ['read', 'create', 'update', 'delete']],
            'stops' => ['label' => 'Przystanki', 'actions' => ['read', 'create', 'update', 'delete']],
            'platforms' => ['label' => 'Platformy', 'actions' => ['read', 'create', 'update', 'delete']],
            'brigades' => ['label' => 'Brygady', 'actions' => ['read', 'create', 'update', 'delete']],
            'route_variants' => ['label' => 'Warianty tras', 'actions' => ['read', 'create', 'update', 'delete']],
            'incidents' => ['label' => 'Zgłoszenia', 'actions' => ['read', 'create', 'update', 'delete', 'resolve']],
            'schedules' => ['label' => 'Grafiki', 'actions' => ['read', 'create', 'update', 'delete']],
            'route_cards' => ['label' => 'Karty drogowe', 'actions' => ['read', 'create', 'update']],
            'reports' => ['label' => 'Raporty', 'actions' => ['read', 'create']]
        ];
    }

    public static function listAll() {
        $db = new Database();
        $query = "SELECT id, name, description, permissions, created_at FROM roles ORDER BY name ASC";
        return $db->query($query);
    }

    public static function find($id) {
        $db = new Database();
        $query = "SELECT id, name, description, permissions, created_at FROM roles WHERE id = :id";
        return $db->queryOne($query, [':id' => $id]);
    }

    public static function existsByName($name, $exclude_id = null) {
        $db = new Database();
        $query = "SELECT COUNT(*) as count FROM roles WHERE name = :name";
        $params = [':name' => $name];

        if ($exclude_id !== null) {
            $query .= " AND id != :id";
            $params[':id'] = $exclude_id;
        }

        $result = $db->queryOne($query, $params);
        return (int)($result['count'] ?? 0) > 0;
    }

    public static function update($id, array $data) {
        $db = new Database();
        $query = "
            UPDATE roles
            SET name = :name,
                description = :description,
                permissions = CAST(:permissions AS JSONB)
            WHERE id = :id
        ";

        return $db->execute($query, [
            ':id' => $id,
            ':name' => $data['name'],
            ':description' => $data['description'] !== '' ? $data['description'] : null,
            ':permissions' => $data['permissions']
        ]);
    }

    public static function getUserRoles($user_id) {
        $db = new Database();
        $query = "
            SELECT ur.id as assignment_id, r.id as role_id, r.name, r.description
            FROM user_roles ur
            INNER JOIN roles r ON r.id = ur.role_id
            WHERE ur.user_id = :user_id
            ORDER BY r.name ASC
        ";

        return $db->query($query, [':user_id' => $user_id]);
    }

    public static function assignmentExists($user_id, $role_id) {
        $db = new Database();
        $query = "SELECT COUNT(*) as count FROM user_roles WHERE user_id = :user_id AND role_id = :role_id";
        $result = $db->queryOne($query, [
            ':user_id' => $user_id,
            ':role_id' => $role_id
        ]);

        return (int)($result['count'] ?? 0) > 0;
    }

    public static function assignToUser($user_id, $role_id) {
        $db = new Database();
        $query = "INSERT INTO user_roles (user_id, role_id, assigned_date) VALUES (:user_id, :role_id, CURRENT_TIMESTAMP)";
        return $db->execute($query, [
            ':user_id' => $user_id,
            ':role_id' => $role_id
        ]);
    }

    public static function removeFromUser($assignment_id, $user_id) {
        $db = new Database();
        $query = "DELETE FROM user_roles WHERE id = :id AND user_id = :user_id";
        return $db->execute($query, [
            ':id' => $assignment_id,
            ':user_id' => $user_id
        ]);
    }
}
