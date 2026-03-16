<?php

class User {
    public static function countByStatus($status_filter = '') {
        $db = new Database();
        $where = '';

        if ($status_filter === 'active') {
            $where = 'WHERE u.active = TRUE AND COALESCE(u.archived, FALSE) = FALSE';
        } elseif ($status_filter === 'inactive') {
            $where = 'WHERE u.active = FALSE AND COALESCE(u.archived, FALSE) = FALSE';
        } elseif ($status_filter === 'archived') {
            $where = 'WHERE COALESCE(u.archived, FALSE) = TRUE';
        }

        $query = "SELECT COUNT(*) as total FROM users u $where";
        $result = $db->queryOne($query);
        return (int)($result['total'] ?? 0);
    }

    public static function listWithRolesPositions($status_filter = '', $limit = 20, $offset = 0) {
        $db = new Database();
        $where = '';

        if ($status_filter === 'active') {
            $where = 'WHERE u.active = TRUE AND COALESCE(u.archived, FALSE) = FALSE';
        } elseif ($status_filter === 'inactive') {
            $where = 'WHERE u.active = FALSE AND COALESCE(u.archived, FALSE) = FALSE';
        } elseif ($status_filter === 'archived') {
            $where = 'WHERE COALESCE(u.archived, FALSE) = TRUE';
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

    public static function listByRole($role_name) {
        $db = new Database();
        $query = "
            SELECT DISTINCT u.id, u.username, u.email, u.first_name, u.last_name
            FROM users u
            INNER JOIN user_roles ur ON ur.user_id = u.id
            INNER JOIN roles r ON r.id = ur.role_id
            WHERE u.active = TRUE
                            AND COALESCE(u.archived, FALSE) = FALSE
              AND r.name = :role_name
            ORDER BY u.last_name ASC NULLS LAST, u.first_name ASC NULLS LAST, u.username ASC
        ";

        return $db->query($query, [':role_name' => $role_name]);
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
        $query = "INSERT INTO users (username, email, password_hash, first_name, last_name, hired_date, archived, active, discord_id, roblox_id)
                  VALUES (:username, :email, :password_hash, :first_name, :last_name, :hired_date, :archived, :active, :discord_id, :roblox_id)";

        $db->execute($query, [
            ':username' => $data['username'],
            ':email' => $data['email'],
            ':password_hash' => $data['password_hash'],
            ':first_name' => $data['first_name'],
            ':last_name' => $data['last_name'],
            ':hired_date' => $data['hired_date'] !== '' ? $data['hired_date'] : null,
            ':archived' => !empty($data['archived']) ? 'true' : 'false',
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
                      hired_date = :hired_date,
                      archived = :archived,
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
            ':hired_date' => $data['hired_date'] !== '' ? $data['hired_date'] : null,
            ':archived' => !empty($data['archived']) ? 'true' : 'false',
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

    public static function updateArchiveState($id, $archived) {
        $db = new Database();
        $query = "UPDATE users
                  SET archived = :archived,
                      active = CASE WHEN :archived = 'true' THEN FALSE ELSE active END,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE id = :id";

        return $db->execute($query, [
            ':archived' => $archived ? 'true' : 'false',
            ':id' => $id
        ]);
    }

    public static function syncRolesFromPositions($user_id) {
        $db = new Database();

        try {
            $db->beginTransaction();

            $deleteQuery = "DELETE FROM user_roles WHERE user_id = :user_id";
            $db->execute($deleteQuery, [':user_id' => $user_id]);

            $insertQuery = "
                INSERT INTO user_roles (user_id, role_id, assigned_date)
                SELECT DISTINCT :user_id, rpm.role_id, CURRENT_TIMESTAMP
                FROM user_positions up
                INNER JOIN role_position_mapping rpm ON rpm.position_id = up.position_id
                WHERE up.user_id = :user_id
                  AND (up.active = TRUE OR up.active IS NULL)
            ";
            $db->execute($insertQuery, [':user_id' => $user_id]);

            $db->commit();

            self::refreshSessionAuthorization($user_id);
            return true;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollback();
            }

            throw $e;
        }
    }

    public static function refreshSessionAuthorizationForUser($user_id) {
        self::refreshSessionAuthorization($user_id);
    }

    private static function refreshSessionAuthorization($user_id) {
        if (!function_exists('getCurrentUserId') || (int)getCurrentUserId() !== (int)$user_id) {
            return;
        }

        $db = new Database();
        $query = "
            SELECT r.name, r.permissions
            FROM user_roles ur
            INNER JOIN roles r ON ur.role_id = r.id
            WHERE ur.user_id = :user_id
        ";

        $roles = $db->query($query, [':user_id' => $user_id]);

        $_SESSION['roles'] = [];
        $_SESSION['permissions'] = [];

        foreach ($roles as $role) {
            $_SESSION['roles'][] = $role['name'];

            if (!empty($role['permissions'])) {
                $permissions = json_decode($role['permissions'], true);
                if (is_array($permissions)) {
                    foreach ($permissions as $resource => $actions) {
                        if (!isset($_SESSION['permissions'][$resource])) {
                            $_SESSION['permissions'][$resource] = [];
                        }

                        $_SESSION['permissions'][$resource] = array_values(array_unique(array_merge(
                            $_SESSION['permissions'][$resource],
                            $actions
                        )));
                    }
                }
            }
        }
    }
}
