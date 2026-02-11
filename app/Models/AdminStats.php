<?php

class AdminStats {
    public static function getStats() {
        $db = new Database();
        $query = "
            SELECT 
                (SELECT COUNT(*) FROM users WHERE active = TRUE) as total_users,
                (SELECT COUNT(*) FROM vehicles) as total_vehicles,
                (SELECT COUNT(*) FROM vehicles WHERE status = 'available') as available_vehicles,
                (SELECT COUNT(*) FROM vehicles WHERE status = 'in_use') as in_use_vehicles,
                (SELECT COUNT(*) FROM vehicles WHERE status = 'maintenance') as maintenance_vehicles,
                (SELECT COUNT(*) FROM vehicles WHERE status = 'broken') as broken_vehicles,
                (SELECT COUNT(*) FROM lines WHERE active = TRUE) as total_lines,
                (SELECT COUNT(*) FROM incidents) as total_incidents,
                (SELECT COUNT(*) FROM incidents WHERE status = 'open') as open_incidents,
                (SELECT COUNT(*) FROM incidents WHERE status = 'in_progress') as in_progress_incidents,
                (SELECT COUNT(*) FROM incidents WHERE status = 'resolved') as resolved_incidents
        ";

        return $db->queryOne($query);
    }
}
