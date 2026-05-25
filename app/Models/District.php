<?php

class District {
    public static function isAvailable(): bool {
        $db = new Database();
        return $db->tableExists('districts');
    }

    public static function listAll(): array {
        if (!self::isAvailable()) {
            return [];
        }

        $db = new Database();
        $query = '
            SELECT d.*, c.name AS city_name
            FROM districts d
            INNER JOIN cities c ON c.id = d.city_id
            ORDER BY d.id ASC
        ';

        return $db->query($query);
    }

    public static function find($id): ?array {
        if (!self::isAvailable()) {
            return null;
        }

        $db = new Database();
        $row = $db->queryOne('SELECT * FROM districts WHERE id = :id', [':id' => $id]);
        return $row ?: null;
    }

    public static function existsByNameAndCity($name, $city_id, $exclude_id = null): bool {
        if (!self::isAvailable()) {
            return false;
        }

        $db = new Database();
        $query = 'SELECT COUNT(*) AS count FROM districts WHERE city_id = :city_id AND LOWER(name) = LOWER(:name)';
        $params = [
            ':city_id' => $city_id,
            ':name' => $name
        ];

        if ($exclude_id !== null) {
            $query .= ' AND id != :id';
            $params[':id'] = $exclude_id;
        }

        $result = $db->queryOne($query, $params);
        return (int)($result['count'] ?? 0) > 0;
    }

    public static function create(array $data): ?int {
        if (!self::isAvailable()) {
            throw new RuntimeException('Tabela districts nie jest dostępna w tej bazie danych.');
        }

        $db = new Database();
        $result = $db->queryOne(
            'INSERT INTO districts (city_id, name, active) VALUES (:city_id, :name, :active) RETURNING id',
            [
                ':city_id' => $data['city_id'],
                ':name' => $data['name'],
                ':active' => $data['active'] ?? true
            ]
        );

        return $result ? (int)$result['id'] : null;
    }

    public static function update($id, array $data): void {
        if (!self::isAvailable()) {
            throw new RuntimeException('Tabela districts nie jest dostępna w tej bazie danych.');
        }

        $db = new Database();
        $db->execute(
            'UPDATE districts SET city_id = :city_id, name = :name, active = :active WHERE id = :id',
            [
                ':id' => $id,
                ':city_id' => $data['city_id'],
                ':name' => $data['name'],
                ':active' => $data['active'] ?? true
            ]
        );
    }

    public static function delete($id): void {
        if (!self::isAvailable()) {
            throw new RuntimeException('Tabela districts nie jest dostępna w tej bazie danych.');
        }

        $db = new Database();
        $db->execute('DELETE FROM districts WHERE id = :id', [':id' => $id]);
    }
}