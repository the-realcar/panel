<?php

class DictionarySetting {
    public static function getValues(string $key, array $fallback = []): array {
        if (!Setting::isAvailable()) {
            return $fallback;
        }

        $stored = Setting::getMany([$key]);
        $raw = trim((string)($stored[$key] ?? ''));
        if ($raw === '') {
            return $fallback;
        }

        $values = preg_split('/\r\n|\n|\r/', $raw);
        $values = array_values(array_filter(array_map('trim', $values), static function ($value) {
            return $value !== '';
        }));

        return empty($values) ? $fallback : array_values(array_unique($values));
    }

    public static function saveValues(string $key, array $values, ?int $updated_by = null): void {
        $normalized = array_values(array_unique(array_filter(array_map('trim', $values), static function ($value) {
            return $value !== '';
        })));

        Setting::setMany([
            $key => implode("\n", $normalized)
        ], $updated_by);
    }
}