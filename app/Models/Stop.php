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
        $where = $active_only ? 'WHERE active = TRUE' : '';
        
        $query = "
            SELECT * FROM stops
            $where
            ORDER BY name ASC
            LIMIT :limit OFFSET :offset
        ";
        
        return $db->query($query, [
            ':limit' => $limit,
            ':offset' => $offset
        ]);
    }

    public static function listActive() {
        $db = new Database();
        $query = "SELECT * FROM stops WHERE active = TRUE ORDER BY name ASC";
        return $db->query($query);
    }

    public static function find($id) {
        $db = new Database();
        $query = "SELECT * FROM stops WHERE id = :id";
        return $db->queryOne($query, [':id' => $id]);
    }

    public static function findByStopId($stop_id) {
        $db = new Database();
        $query = "SELECT * FROM stops WHERE stop_id = :stop_id";
        return $db->queryOne($query, [':stop_id' => $stop_id]);
    }

    public static function existsByStopId($stop_id, $exclude_id = null) {
        $db = new Database();
        $query = "SELECT COUNT(*) as count FROM stops WHERE stop_id = :stop_id";
        $params = [':stop_id' => $stop_id];

        if ($exclude_id) {
            $query .= " AND id != :id";
            $params[':id'] = $exclude_id;
        }

        $result = $db->queryOne($query, $params);
        return (int)($result['count'] ?? 0) > 0;
    }

    public static function existsByName($name, $exclude_id = null) {
        $db = new Database();
        $query = "SELECT COUNT(*) as count FROM stops WHERE name = :name";
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
            INSERT INTO stops (
                stop_id, name, location_description, latitude, longitude, active
            ) VALUES (
                :stop_id, :name, :location_description, :latitude, :longitude, :active
            )
        ";

        return $db->execute($query, [
            ':stop_id' => $data['stop_id'],
            ':name' => $data['name'],
            ':location_description' => $data['location_description'] ?? null,
            ':latitude' => $data['latitude'] ?? null,
            ':longitude' => $data['longitude'] ?? null,
            ':active' => $data['active'] ?? true
        ]);
    }

    public static function update($id, array $data) {
        $db = new Database();
        $query = "
            UPDATE stops SET
                stop_id = :stop_id,
                name = :name,
                location_description = :location_description,
                latitude = :latitude,
                longitude = :longitude,
                active = :active
            WHERE id = :id
        ";

        return $db->execute($query, [
            ':id' => $id,
            ':stop_id' => $data['stop_id'],
            ':name' => $data['name'],
            ':location_description' => $data['location_description'] ?? null,
            ':latitude' => $data['latitude'] ?? null,
            ':longitude' => $data['longitude'] ?? null,
            ':active' => $data['active'] ?? true
        ]);
    }

    public static function delete($id) {
        $db = new Database();
        $query = "DELETE FROM stops WHERE id = :id";
        return $db->execute($query, [':id' => $id]);
    }

    public static function getPlatformsCount($stop_id) {
        $db = new Database();
        $query = "SELECT COUNT(*) as count FROM platforms WHERE stop_id = :stop_id";
        $result = $db->queryOne($query, [':stop_id' => $stop_id]);
        return (int)($result['count'] ?? 0);
    }
}
