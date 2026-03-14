<?php

class WorkHour {
    public static function listDriverUsers(): array {
        $db = new Database();
        $query = "
            SELECT DISTINCT u.id, u.username, u.first_name, u.last_name
            FROM users u
            INNER JOIN user_roles ur ON ur.user_id = u.id
            INNER JOIN roles r ON r.id = ur.role_id
            WHERE u.active = TRUE
              AND r.name IN ('Kierowca', 'Transport')
            ORDER BY u.last_name ASC NULLS LAST, u.first_name ASC NULLS LAST, u.username ASC
        ";

        return $db->query($query);
    }

    public static function listMonthlySummary(string $month): array {
        $db = new Database();
        $query = "
            SELECT
                u.id AS user_id,
                u.username,
                u.first_name,
                u.last_name,
                COUNT(wh.id) AS days_count,
                COALESCE(SUM(wh.hours_worked), 0) AS total_hours
            FROM users u
            INNER JOIN user_roles ur ON ur.user_id = u.id
            INNER JOIN roles r ON r.id = ur.role_id
            LEFT JOIN work_hours wh
                ON wh.user_id = u.id
               AND TO_CHAR(wh.work_date, 'YYYY-MM') = :month
            WHERE u.active = TRUE
              AND r.name IN ('Kierowca', 'Transport')
            GROUP BY u.id, u.username, u.first_name, u.last_name
            ORDER BY u.last_name ASC NULLS LAST, u.first_name ASC NULLS LAST, u.username ASC
        ";

        return $db->query($query, [':month' => $month]);
    }

    public static function listEntriesForUserMonth(int $user_id, string $month): array {
        $db = new Database();
        $query = "
            SELECT wh.*, u.username AS updated_by_username
            FROM work_hours wh
            LEFT JOIN users u ON u.id = wh.updated_by
            WHERE wh.user_id = :user_id
              AND TO_CHAR(wh.work_date, 'YYYY-MM') = :month
            ORDER BY wh.work_date DESC
        ";

        return $db->query($query, [
            ':user_id' => $user_id,
            ':month' => $month
        ]);
    }

    public static function getMonthlyTotalForUser(int $user_id, string $month): float {
        $db = new Database();
        $result = $db->queryOne(
            "
            SELECT COALESCE(SUM(hours_worked), 0) AS total
            FROM work_hours
            WHERE user_id = :user_id
              AND TO_CHAR(work_date, 'YYYY-MM') = :month
            ",
            [
                ':user_id' => $user_id,
                ':month' => $month
            ]
        );

        return (float)($result['total'] ?? 0);
    }

    public static function upsertEntry(array $data): int {
        $db = new Database();
        $result = $db->queryOne(
            "
            INSERT INTO work_hours (user_id, work_date, hours_worked, notes, source, updated_by)
            VALUES (:user_id, :work_date, :hours_worked, :notes, :source, :updated_by)
            ON CONFLICT (user_id, work_date) DO UPDATE
            SET hours_worked = EXCLUDED.hours_worked,
                notes = EXCLUDED.notes,
                source = EXCLUDED.source,
                updated_by = EXCLUDED.updated_by,
                updated_at = CURRENT_TIMESTAMP
            RETURNING id
            ",
            [
                ':user_id' => $data['user_id'],
                ':work_date' => $data['work_date'],
                ':hours_worked' => $data['hours_worked'],
                ':notes' => $data['notes'] ?? null,
                ':source' => $data['source'] ?? 'manual',
                ':updated_by' => $data['updated_by'] ?? null
            ]
        );

        return (int)$result['id'];
    }

    public static function findById(int $id) {
        $db = new Database();
        return $db->queryOne("SELECT * FROM work_hours WHERE id = :id", [':id' => $id]);
    }

    public static function deleteById(int $id): bool {
        $db = new Database();
        return $db->execute("DELETE FROM work_hours WHERE id = :id", [':id' => $id]);
    }
}
