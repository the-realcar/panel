<?php

class RouteVariant {
    public static function countByLine($line_id = null, $active_only = false) {
        $db = new Database();
        $where_parts = [];
        $params = [];

        if ($line_id !== null) {
            $where_parts[] = 'rv.line_id = :line_id';
            $params[':line_id'] = $line_id;
        }
        
        if ($active_only) {
            $where_parts[] = 'rv.is_active = TRUE';
        }

        $where = $where_parts ? 'WHERE ' . implode(' AND ', $where_parts) : '';
        
        $query = "SELECT COUNT(*) as total FROM route_variants rv $where";
        $result = $db->queryOne($query, $params);
        return (int)($result['total'] ?? 0);
    }

    public static function listByLine($line_id, $active_only = false) {
        $db = new Database();
        $where = 'WHERE rv.line_id = :line_id';
        if ($active_only) {
            $where .= ' AND rv.is_active = TRUE';
        }
        
        $query = "
            SELECT rv.*, l.line_number, l.name as line_name,
                   (SELECT COUNT(*) FROM route_stops WHERE route_variant_id = rv.id) as stops_count
            FROM route_variants rv
            INNER JOIN lines l ON rv.line_id = l.id
            $where
            ORDER BY rv.id ASC
        ";
        
        return $db->query($query, [':line_id' => $line_id]);
    }

    public static function listAll($limit = 50, $offset = 0, $active_only = false) {
        $db = new Database();
        $where = $active_only ? 'WHERE rv.is_active = TRUE' : '';
        
        $query = "
            SELECT rv.*, l.line_number, l.name as line_name,
                   (SELECT COUNT(*) FROM route_stops WHERE route_variant_id = rv.id) as stops_count
            FROM route_variants rv
            INNER JOIN lines l ON rv.line_id = l.id
            $where
            ORDER BY l.line_number ASC, rv.id ASC
            LIMIT :limit OFFSET :offset
        ";
        
        return $db->query($query, [
            ':limit' => $limit,
            ':offset' => $offset
        ]);
    }

    public static function find($id) {
        $db = new Database();
        $query = "
            SELECT rv.*, l.line_number, l.name as line_name
            FROM route_variants rv
            INNER JOIN lines l ON rv.line_id = l.id
            WHERE rv.id = :id
        ";
        return $db->queryOne($query, [':id' => $id]);
    }

    public static function create(array $data) {
        $db = new Database();
        $query = "
            INSERT INTO route_variants (
                line_id, variant_name, variant_type, direction, is_active
            ) VALUES (
                :line_id, :variant_name, :variant_type, :direction, :is_active
            )
        ";

        return $db->execute($query, [
            ':line_id' => $data['line_id'],
            ':variant_name' => $data['variant_name'],
            ':variant_type' => $data['variant_type'] ?? 'normal',
            ':direction' => $data['direction'] ?? null,
            ':is_active' => $data['is_active'] ?? true
        ]);
    }

    public static function update($id, array $data) {
        $db = new Database();
        $query = "
            UPDATE route_variants SET
                line_id = :line_id,
                variant_name = :variant_name,
                variant_type = :variant_type,
                direction = :direction,
                is_active = :is_active
            WHERE id = :id
        ";

        return $db->execute($query, [
            ':id' => $id,
            ':line_id' => $data['line_id'],
            ':variant_name' => $data['variant_name'],
            ':variant_type' => $data['variant_type'] ?? 'normal',
            ':direction' => $data['direction'] ?? null,
            ':is_active' => $data['is_active'] ?? true
        ]);
    }

    public static function delete($id) {
        $db = new Database();
        $query = "DELETE FROM route_variants WHERE id = :id";
        return $db->execute($query, [':id' => $id]);
    }

    public static function getStops($variant_id) {
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
}
