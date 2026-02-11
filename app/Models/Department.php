<?php

class Department {
    public static function listActive() {
        $db = new Database();
        $query = "SELECT id, name FROM departments WHERE active = TRUE ORDER BY name ASC";
        return $db->query($query);
    }
}
