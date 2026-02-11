<?php

class LoginLog {
    public static function getRecent($limit = 10) {
        $db = new Database();
        $query = "
            SELECT ll.*, u.username, u.first_name, u.last_name
            FROM login_logs ll
            LEFT JOIN users u ON ll.user_id = u.id
            WHERE ll.success = TRUE
            ORDER BY ll.login_time DESC
            LIMIT :limit
        ";

        return $db->query($query, [':limit' => $limit]);
    }
}
