<?php

class RouteStop {
    public static function listByVariant($variant_id) {
        $db = new Database();
        $query = "
            SELECT rs.*, p.platform_number, p.platform_type,
                   s.name as stop_name, s.stop_id as stop_code
            FROM route_stops rs
            INNER JOIN platforms p ON rs.platform_id = p.id
            INNER JOIN stops s ON p.stop_id = s.id
            WHERE rs.route_variant_id = :variant_id
            ORDER BY rs.stop_sequence ASC
        ";
        return $db->query($query, [':variant_id' => $variant_id]);
    }

    public static function find($id) {
        $db = new Database();
        $query = "
            SELECT rs.*, p.platform_number, p.platform_type,
                   s.name as stop_name, s.stop_id as stop_code
            FROM route_stops rs
            INNER JOIN platforms p ON rs.platform_id = p.id
            INNER JOIN stops s ON p.stop_id = s.id
            WHERE rs.id = :id
        ";
        return $db->queryOne($query, [':id' => $id]);
    }

    public static function exists($variant_id, $platform_id, $exclude_id = null) {
        $db = new Database();
        $query = "SELECT COUNT(*) as count FROM route_stops WHERE route_variant_id = :variant_id AND platform_id = :platform_id";
        $params = [
            ':variant_id' => $variant_id,
            ':platform_id' => $platform_id
        ];

        if ($exclude_id) {
            $query .= " AND id != :id";
            $params[':id'] = $exclude_id;
        }

        $result = $db->queryOne($query, $params);
        return (int)($result['count'] ?? 0) > 0;
    }

    public static function sequenceExists($variant_id, $sequence, $exclude_id = null) {
        $db = new Database();
        $query = "SELECT COUNT(*) as count FROM route_stops WHERE route_variant_id = :variant_id AND stop_sequence = :sequence";
        $params = [
            ':variant_id' => $variant_id,
            ':sequence' => $sequence
        ];

        if ($exclude_id) {
            $query .= " AND id != :id";
            $params[':id'] = $exclude_id;
        }

        $result = $db->queryOne($query, $params);
        return (int)($result['count'] ?? 0) > 0;
    }

    public static function getMaxSequence($variant_id) {
        $db = new Database();
        $query = "SELECT COALESCE(MAX(stop_sequence), 0) as max_seq FROM route_stops WHERE route_variant_id = :variant_id";
        $result = $db->queryOne($query, [':variant_id' => $variant_id]);
        return (int)($result['max_seq'] ?? 0);
    }

    public static function create(array $data) {
        $db = new Database();
        $query = "
            INSERT INTO route_stops (
                route_variant_id, platform_id, stop_sequence, travel_time_minutes, is_timing_point
            ) VALUES (
                :route_variant_id, :platform_id, :stop_sequence, :travel_time_minutes, :is_timing_point
            )
        ";

        return $db->execute($query, [
            ':route_variant_id' => $data['route_variant_id'],
            ':platform_id' => $data['platform_id'],
            ':stop_sequence' => $data['stop_sequence'],
            ':travel_time_minutes' => $data['travel_time_minutes'] ?? null,
            ':is_timing_point' => $data['is_timing_point'] ?? false
        ]);
    }

    public static function update($id, array $data) {
        $db = new Database();
        $query = "
            UPDATE route_stops SET
                platform_id = :platform_id,
                stop_sequence = :stop_sequence,
                travel_time_minutes = :travel_time_minutes,
                is_timing_point = :is_timing_point
            WHERE id = :id
        ";

        return $db->execute($query, [
            ':id' => $id,
            ':platform_id' => $data['platform_id'],
            ':stop_sequence' => $data['stop_sequence'],
            ':travel_time_minutes' => $data['travel_time_minutes'] ?? null,
            ':is_timing_point' => $data['is_timing_point'] ?? false
        ]);
    }

    public static function delete($id) {
        $db = new Database();
        $query = "DELETE FROM route_stops WHERE id = :id";
        return $db->execute($query, [':id' => $id]);
    }

    public static function deleteByVariant($variant_id) {
        $db = new Database();
        $query = "DELETE FROM route_stops WHERE route_variant_id = :variant_id";
        return $db->execute($query, [':variant_id' => $variant_id]);
    }

    public static function reorderSequences($variant_id) {
        $db = new Database();
        // Re-sequence stops to ensure no gaps
        $query = "
            WITH numbered AS (
                SELECT id, ROW_NUMBER() OVER (ORDER BY stop_sequence) as new_seq
                FROM route_stops
                WHERE route_variant_id = :variant_id
            )
            UPDATE route_stops
            SET stop_sequence = numbered.new_seq
            FROM numbered
            WHERE route_stops.id = numbered.id
        ";
        return $db->execute($query, [':variant_id' => $variant_id]);
    }
}
