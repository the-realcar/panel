<?php

class Company {
    public static function listAll() {
        $db = new Database();
        return $db->query("
            SELECT *
            FROM companies
            WHERE active = TRUE
            ORDER BY name ASC
        ");
    }

    public static function listActive() {
        return self::listAll();
    }

    public static function find($id) {
        $db = new Database();
        return $db->queryOne("SELECT * FROM companies WHERE id = :id", [':id' => $id]);
    }

    public static function existsByName($name, $exclude_id = null) {
        $db = new Database();
        $query = "SELECT COUNT(*) as count FROM companies WHERE name = :name";
        $params = [':name' => $name];

        if ($exclude_id) {
            $query .= " AND id != :id";
            $params[':id'] = $exclude_id;
        }

        $row = $db->queryOne($query, $params);
        return (int)($row['count'] ?? 0) > 0;
    }
}
