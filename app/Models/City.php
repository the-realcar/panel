<?php

class City {
    public static function listAll(): array {
        $db = new Database();
        return $db->query("SELECT * FROM cities ORDER BY name ASC");
    }

    public static function listActive(): array {
        $db = new Database();
        return $db->query("SELECT * FROM cities WHERE active = TRUE ORDER BY name ASC");
    }

    public static function find($id): ?array {
        $db = new Database();
        $row = $db->queryOne("SELECT * FROM cities WHERE id = :id", [':id' => $id]);
        return $row ?: null;
    }

    public static function existsByName($name, $exclude_id = null): bool {
        $db = new Database();
        $q = "SELECT COUNT(*) as c FROM cities WHERE name = :name";
        $p = [':name' => $name];
        if ($exclude_id) { $q .= " AND id != :id"; $p[':id'] = $exclude_id; }
        $result = $db->queryOne($q, $p);
        return (int)($result['c'] ?? 0) > 0;
    }

    public static function create(array $data): ?int {
        $db = new Database();
        $result = $db->queryOne(
            "INSERT INTO cities (name, active) VALUES (:name, :active) RETURNING id",
            [':name' => $data['name'], ':active' => isset($data['active']) ? 'true' : 'false']
        );
        return $result ? (int)$result['id'] : null;
    }

    public static function update($id, array $data): void {
        $db = new Database();
        $db->execute(
            "UPDATE cities SET name = :name, active = :active WHERE id = :id",
            [':name' => $data['name'], ':active' => isset($data['active']) ? 'true' : 'false', ':id' => $id]
        );
    }

    public static function delete($id): void {
        $db = new Database();
        $db->execute("DELETE FROM cities WHERE id = :id", [':id' => $id]);
    }

    public static function getStopsCount($city_id): int {
        $db = new Database();
        $result = $db->queryOne("SELECT COUNT(*) as c FROM stops WHERE city_id = :id", [':id' => $city_id]);
        return (int)($result['c'] ?? 0);
    }
}
