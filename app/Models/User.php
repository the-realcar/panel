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

    public static function findByUsername($username) {
        $db = new Database();
        $query = "SELECT * FROM users WHERE username = :username";
        return $db->queryOne($query, [':username' => $username]);
    }

    public static function findByEmail($email) {
        $db = new Database();
        $query = "SELECT * FROM users WHERE email = :email";
        return $db->queryOne($query, [':email' => $email]);
    }

    public static function findByProviderId($provider, $provider_id) {
        $db = new Database();
        $column = null;

        if ($provider === 'discord') {
            $column = 'discord_id';
        } elseif ($provider === 'roblox') {
            $column = 'roblox_id';
        }

        if (!$column || $provider_id === '' || $provider_id === null) {
            return false;
        }

        $query = "SELECT * FROM users WHERE {$column} = :provider_id";
        return $db->queryOne($query, [':provider_id' => $provider_id]);
    }

    public static function create(array $data) {
        $db = new Database();
        $query = "INSERT INTO users (username, email, password_hash, first_name, last_name, active, discord_id, roblox_id)
                  VALUES (:username, :email, :password_hash, :first_name, :last_name, :active, :discord_id, :roblox_id)";

        $db->execute($query, [
            ':username' => $data['username'],
            ':email' => $data['email'],
            ':password_hash' => $data['password_hash'],
            ':first_name' => $data['first_name'],
            ':last_name' => $data['last_name'],
            ':active' => $data['active'] ? 'true' : 'false',
            ':discord_id' => $data['discord_id'],
            ':roblox_id' => $data['roblox_id']
        ]);

        return $db->lastInsertId('users_id_seq');
    }

    public static function update($id, array $data) {
        $db = new Database();
        $query = "UPDATE users
                  SET username = :username,
                      email = :email,
                      first_name = :first_name,
                      last_name = :last_name,
                      active = :active,
                      discord_id = :discord_id,
                      roblox_id = :roblox_id,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";

        return $db->execute($query, [
            ':username' => $data['username'],
            ':email' => $data['email'],
            ':first_name' => $data['first_name'],
            ':last_name' => $data['last_name'],
            ':active' => $data['active'] ? 'true' : 'false',
            ':discord_id' => $data['discord_id'],
            ':roblox_id' => $data['roblox_id'],
            ':id' => $id
        ]);
    }

    public static function updatePassword($id, $password_hash) {
        $db = new Database();
        $query = "UPDATE users SET password_hash = :password_hash, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        return $db->execute($query, [
            ':password_hash' => $password_hash,
            ':id' => $id
        ]);
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
