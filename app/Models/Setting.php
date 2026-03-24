<?php

class Setting {
    public static function isAvailable(): bool {
        $db = new Database();
        return $db->tableExists('settings');
    }

    public static function listAll(): array {
        if (!self::isAvailable()) {
            return [];
        }

        $db = new Database();
        $query = "SELECT key, value, description, updated_by, created_at, updated_at FROM settings ORDER BY key ASC";
        return $db->query($query);
    }

    public static function getMany(array $keys): array {
        if (empty($keys) || !self::isAvailable()) {
            return [];
        }

        $db = new Database();
        $placeholders = [];
        $params = [];

        foreach (array_values($keys) as $index => $key) {
            $placeholder = ':k' . $index;
            $placeholders[] = $placeholder;
            $params[$placeholder] = $key;
        }

        $query = "SELECT key, value FROM settings WHERE key IN (" . implode(', ', $placeholders) . ')';
        $rows = $db->query($query, $params);

        $result = [];
        foreach ($rows as $row) {
            $result[$row['key']] = $row['value'];
        }

        return $result;
    }

    public static function setMany(array $values, ?int $updated_by = null): void {
        if (empty($values)) {
            return;
        }

        if (!self::isAvailable()) {
            throw new RuntimeException('Tabela ustawień nie jest dostępna w tej bazie danych.');
        }

        $db = new Database();

        foreach ($values as $key => $value) {
            $db->execute(
                "
                INSERT INTO settings (key, value, updated_by)
                VALUES (:key, :value, :updated_by)
                ON CONFLICT (key) DO UPDATE
                SET value = EXCLUDED.value,
                    updated_by = EXCLUDED.updated_by,
                    updated_at = CURRENT_TIMESTAMP
                ",
                [
                    ':key' => $key,
                    ':value' => $value,
                    ':updated_by' => $updated_by
                ]
            );
        }
    }
}
