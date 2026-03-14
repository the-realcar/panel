<?php

class Dispatch {
    public static function create(array $data): int {
        $db = new Database();
        $result = $db->queryOne(
            "
            INSERT INTO dispatches (sender_id, recipient_id, message)
            VALUES (:sender_id, :recipient_id, :message)
            RETURNING id
            ",
            [
                ':sender_id' => $data['sender_id'] ?? null,
                ':recipient_id' => $data['recipient_id'],
                ':message' => $data['message']
            ]
        );

        return (int)$result['id'];
    }

    public static function listForRecipient(int $recipient_id, int $limit = 20): array {
        $db = new Database();
        return $db->query(
            "
            SELECT d.*, u.username AS sender_username, u.first_name AS sender_first_name, u.last_name AS sender_last_name
            FROM dispatches d
            LEFT JOIN users u ON u.id = d.sender_id
            WHERE d.recipient_id = :recipient_id
            ORDER BY d.created_at DESC
            LIMIT :limit
            ",
            [
                ':recipient_id' => $recipient_id,
                ':limit' => $limit
            ]
        );
    }

    public static function listSentBy(int $sender_id, int $limit = 50): array {
        $db = new Database();
        return $db->query(
            "
            SELECT d.*, u.username AS recipient_username, u.first_name AS recipient_first_name, u.last_name AS recipient_last_name
            FROM dispatches d
            INNER JOIN users u ON u.id = d.recipient_id
            WHERE d.sender_id = :sender_id
            ORDER BY d.created_at DESC
            LIMIT :limit
            ",
            [
                ':sender_id' => $sender_id,
                ':limit' => $limit
            ]
        );
    }

    public static function countUnreadForRecipient(int $recipient_id): int {
        $db = new Database();
        $result = $db->queryOne(
            "SELECT COUNT(*) AS total FROM dispatches WHERE recipient_id = :recipient_id AND read_at IS NULL",
            [':recipient_id' => $recipient_id]
        );

        return (int)($result['total'] ?? 0);
    }

    public static function markAllReadForRecipient(int $recipient_id): void {
        $db = new Database();
        $db->execute(
            "
            UPDATE dispatches
            SET read_at = CURRENT_TIMESTAMP
            WHERE recipient_id = :recipient_id
              AND read_at IS NULL
            ",
            [':recipient_id' => $recipient_id]
        );
    }
}
