<?php

class Stop {
    public static function countAll($active_only = false) {
        $db = new Database();
        $where = $active_only ? 'WHERE active = TRUE' : '';
        $query = "SELECT COUNT(*) as total FROM stops $where";
        $result = $db->queryOne($query);
        return (int)($result['total'] ?? 0);
    }

    public static function listAll($limit = 20, $offset = 0, $active_only = false) {
        $db = new Database();
        $where = $active_only ? 'WHERE s.active = TRUE' : '';
        $query = "
            SELECT s.*, c.name as city_name
            FROM stops s
            LEFT JOIN cities c ON s.city_id = c.id
            $where
            ORDER BY s.name ASC
            LIMIT :limit OFFSET :offset
        ";
        return $db->query($query, [':limit' => $limit, ':offset' => $offset]);
    }

    public static function listActive() {
        $db = new Database();
        return $db->query("
            SELECT s.*, c.name as city_name
            FROM stops s
            LEFT JOIN cities c ON s.city_id = c.id
            WHERE s.active = TRUE ORDER BY s.name ASC
        ");
    }

    public static function find($id) {
        $db = new Database();
        $query = "
            SELECT s.*, c.name as city_name
            FROM stops s
            LEFT JOIN cities c ON s.city_id = c.id
            WHERE s.id = :id
        ";
        return $db->queryOne($query, [':id' => $id]);
    }

    public static function findByName($name) {
        $db = new Database();
        $query = "
            SELECT s.*, c.name as city_name
            FROM stops s
            LEFT JOIN cities c ON s.city_id = c.id
            WHERE s.name = :name
            LIMIT 1
        ";

        return $db->queryOne($query, [':name' => $name]);
    }

    public static function existsByName($name, $exclude_id = null) {
        $db = new Database();
        $query = "SELECT COUNT(*) as count FROM stops WHERE name = :name";
        $params = [':name' => $name];
        if ($exclude_id) { $query .= " AND id != :id"; $params[':id'] = $exclude_id; }
        $result = $db->queryOne($query, $params);
        return (int)($result['count'] ?? 0) > 0;
    }

    public static function create(array $data) {
        $db = new Database();
        $db = new Database();
        $db->execute("
            INSERT INTO stops (city_id, name, opis, status_nz, active)
            VALUES (:city_id, :name, :opis, :status_nz, :active)
        ", [
            ':city_id'   => $data['city_id'] ? (int)$data['city_id'] : null,
            ':name'      => $data['name'],
            ':opis'      => $data['opis'] ?? null,
            ':status_nz' => $data['status_nz'] ?? false,
            ':active'    => $data['active'] ?? true
        ]);
        return $db->lastInsertId('stops_id_seq');
    }

    public static function update($id, array $data) {
        $db = new Database();
        return $db->execute("
            UPDATE stops SET
                city_id   = :city_id,
                name      = :name,
                opis      = :opis,
                status_nz = :status_nz,
                active    = :active
            WHERE id = :id
        ", [
            ':id'        => $id,
            ':city_id'   => $data['city_id'] ? (int)$data['city_id'] : null,
            ':name'      => $data['name'],
            ':opis'      => $data['opis'] ?? null,
            ':status_nz' => $data['status_nz'] ?? false,
            ':active'    => $data['active'] ?? true
        ]);
    }

    public static function delete($id) {
        $db = new Database();
        return $db->execute("DELETE FROM stops WHERE id = :id", [':id' => $id]);
    }

    public static function getPlatformsCount($stop_id) {
        $db = new Database();
        $result = $db->queryOne("SELECT COUNT(*) as count FROM platforms WHERE stop_id = :stop_id", [':stop_id' => $stop_id]);
        return (int)($result['count'] ?? 0);
    }
}
