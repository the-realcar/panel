<?php

class Assignment {
    public static function create(array $data) {
        $db = new Database();

        $query = "
            INSERT INTO assignments (
                dispatcher_id,
                user_id,
                vehicle_id,
                line_id,
                brigade_id,
                schedule_id,
                assignment_date,
                start_time,
                end_time,
                status,
                notes
            ) VALUES (
                :dispatcher_id,
                :user_id,
                :vehicle_id,
                :line_id,
                :brigade_id,
                :schedule_id,
                :assignment_date,
                :start_time,
                :end_time,
                :status,
                :notes
            )
            RETURNING id
        ";

        $result = $db->queryOne($query, [
            ':dispatcher_id' => $data['dispatcher_id'] ?? null,
            ':user_id' => $data['user_id'],
            ':vehicle_id' => $data['vehicle_id'] ?? null,
            ':line_id' => $data['line_id'] ?? null,
            ':brigade_id' => $data['brigade_id'] ?? null,
            ':schedule_id' => $data['schedule_id'] ?? null,
            ':assignment_date' => $data['assignment_date'],
            ':start_time' => $data['start_time'],
            ':end_time' => $data['end_time'],
            ':status' => $data['status'] ?? 'active',
            ':notes' => $data['notes'] ?? null
        ]);

        return $result ? (int)$result['id'] : null;
    }
}
