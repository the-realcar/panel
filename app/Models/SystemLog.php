<?php

class SystemLog {
    public static function supportsErrorLogs(): bool {
        $db = new Database();
        return $db->tableExists('error_logs');
    }

    public static function listLoginLogs(array $filters = [], int $limit = 100): array {
        $db = new Database();
        $where = [];
        $params = [':limit' => $limit];

        if (!empty($filters['username'])) {
            $where[] = 'u.username ILIKE :username';
            $params[':username'] = '%' . $filters['username'] . '%';
        }

        if (!empty($filters['date_from'])) {
            $where[] = 'll.login_time >= :date_from';
            $params[':date_from'] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'll.login_time <= :date_to';
            $params[':date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        if (isset($filters['success']) && $filters['success'] !== '') {
            $where[] = 'll.success = :success';
            $params[':success'] = $filters['success'] === '1' ? 'true' : 'false';
        }

        $where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        return $db->query(
            "
            SELECT ll.*, u.username, u.first_name, u.last_name
            FROM login_logs ll
            LEFT JOIN users u ON u.id = ll.user_id
            $where_sql
            ORDER BY ll.login_time DESC
            LIMIT :limit
            ",
            $params
        );
    }

    public static function listAuditLogs(array $filters = [], int $limit = 200): array {
        $db = new Database();
        $where = [];
        $params = [':limit' => $limit];

        if (!empty($filters['username'])) {
            $where[] = 'u.username ILIKE :username';
            $params[':username'] = '%' . $filters['username'] . '%';
        }

        if (!empty($filters['action'])) {
            $where[] = 'al.action ILIKE :action';
            $params[':action'] = '%' . $filters['action'] . '%';
        }

        if (!empty($filters['date_from'])) {
            $where[] = 'al.created_at >= :date_from';
            $params[':date_from'] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'al.created_at <= :date_to';
            $params[':date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        $where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        return $db->query(
            "
            SELECT al.*, u.username, u.first_name, u.last_name
            FROM audit_logs al
            LEFT JOIN users u ON u.id = al.user_id
            $where_sql
            ORDER BY al.created_at DESC
            LIMIT :limit
            ",
            $params
        );
    }

    public static function listErrorLogs(array $filters = [], int $limit = 200): array {
        if (!self::supportsErrorLogs()) {
            return [];
        }

        $db = new Database();
        $where = [];
        $params = [':limit' => $limit];

        if (!empty($filters['date_from'])) {
            $where[] = 'el.created_at >= :date_from';
            $params[':date_from'] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $where[] = 'el.created_at <= :date_to';
            $params[':date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        if (!empty($filters['action'])) {
            $where[] = '(el.error_type ILIKE :error_filter OR el.message ILIKE :error_filter)';
            $params[':error_filter'] = '%' . $filters['action'] . '%';
        }

        $where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        return $db->query(
            "
            SELECT el.*
            FROM error_logs el
            $where_sql
            ORDER BY el.created_at DESC
            LIMIT :limit
            ",
            $params
        );
    }

    public static function readErrorLogTail(int $lines = 120): array {
        $path = BASE_PATH . '/logs/error.log';
        if (!is_file($path)) {
            return [];
        }

        $contents = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($contents) || empty($contents)) {
            return [];
        }

        return array_slice($contents, -1 * $lines);
    }
}
