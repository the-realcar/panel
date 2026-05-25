<?php

class Dispatch {
    public static function isAvailable(): bool {
        $db = new Database();
        return $db->tableExists('dispatches');
    }

    public static function create(array $data): int {
        if (!self::isAvailable()) {
            throw new RuntimeException('Tabela dyspozycji nie jest dostępna w tej bazie danych.');
        }

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

    public static function createForRecipients(int $sender_id, array $recipient_ids, string $message): int {
        if (!self::isAvailable()) {
            throw new RuntimeException('Tabela dyspozycji nie jest dostępna w tej bazie danych.');
        }

        $recipient_ids = array_values(array_unique(array_map('intval', $recipient_ids)));
        $recipient_ids = array_filter($recipient_ids, static function ($recipient_id) {
            return $recipient_id > 0;
        });

        if (empty($recipient_ids)) {
            throw new InvalidArgumentException('Brak odbiorcow komunikatu.');
        }

        $db = new Database();
        $created = 0;

        try {
            $db->beginTransaction();

            foreach ($recipient_ids as $recipient_id) {
                $db->queryOne(
                    "
                    INSERT INTO dispatches (sender_id, recipient_id, message)
                    VALUES (:sender_id, :recipient_id, :message)
                    RETURNING id
                    ",
                    [
                        ':sender_id' => $sender_id,
                        ':recipient_id' => $recipient_id,
                        ':message' => $message
                    ]
                );
                $created++;
            }

            $db->commit();
        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollback();
            }

            throw $e;
        }

        return $created;
    }

    public static function listForRecipient(int $recipient_id, int $limit = 20): array {
        if (!self::isAvailable()) {
            return [];
        }

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
        if (!self::isAvailable()) {
            return [];
        }

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
        if (!self::isAvailable()) {
            return 0;
        }

        $db = new Database();
        $result = $db->queryOne(
            "SELECT COUNT(*) AS total FROM dispatches WHERE recipient_id = :recipient_id AND read_at IS NULL",
            [':recipient_id' => $recipient_id]
        );

        return (int)($result['total'] ?? 0);
    }

    public static function markAllReadForRecipient(int $recipient_id): void {
        if (!self::isAvailable()) {
            return;
        }

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

    public static function deleteById(int $dispatch_id, int $sender_id): void {
        if (!self::isAvailable()) {
            throw new RuntimeException('Tabela dyspozycji nie jest dostępna.');
        }

        $db = new Database();
        $db->execute(
            "
            DELETE FROM dispatches
            WHERE id = :id AND sender_id = :sender_id
            ",
            [
                ':id' => $dispatch_id,
                ':sender_id' => $sender_id
            ]
        );
    }

    public static function updateById(int $dispatch_id, int $sender_id, string $message): void {
        if (!self::isAvailable()) {
            throw new RuntimeException('Tabela dyspozycji nie jest dostępna.');
        }

        $db = new Database();
        $db->execute(
            "
            UPDATE dispatches
            SET message = :message
            WHERE id = :id AND sender_id = :sender_id
            ",
            [
                ':message' => $message,
                ':id' => $dispatch_id,
                ':sender_id' => $sender_id
            ]
        );
    }
}
