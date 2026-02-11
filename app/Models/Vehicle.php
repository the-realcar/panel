<?php

class Vehicle {
    public static function countByStatus($status = '') {
        $db = new Database();
        $where = '';
        $params = [];

        if ($status) {
            $where = 'WHERE status = :status';
            $params[':status'] = $status;
        }

        $query = "SELECT COUNT(*) as total FROM vehicles $where";
        $result = $db->queryOne($query, $params);
        return (int)($result['total'] ?? 0);
    }

    public static function listByStatus($status = '', $limit = 20, $offset = 0) {
        $db = new Database();
        $where = '';
        $params = [];

        if ($status) {
            $where = 'WHERE status = :status';
            $params[':status'] = $status;
        }

        $query = "
            SELECT * FROM vehicles
            $where
            ORDER BY vehicle_number ASC
            LIMIT :limit OFFSET :offset
        ";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        return $db->query($query, $params);
    }

    public static function find($id) {
        $db = new Database();
        $query = "SELECT * FROM vehicles WHERE id = :id";
        return $db->queryOne($query, [':id' => $id]);
    }

    public static function existsByNumber($vehicle_number, $exclude_id = null) {
        $db = new Database();
        $query = "SELECT COUNT(*) as count FROM vehicles WHERE vehicle_number = :vehicle_number";
        $params = [':vehicle_number' => $vehicle_number];

        if ($exclude_id) {
            $query .= " AND id != :id";
            $params[':id'] = $exclude_id;
        }

        $result = $db->queryOne($query, $params);
        return (int)($result['count'] ?? 0) > 0;
    }

    public static function existsByPlate($registration_plate, $exclude_id = null) {
        $db = new Database();
        $query = "SELECT COUNT(*) as count FROM vehicles WHERE registration_plate = :registration_plate";
        $params = [':registration_plate' => $registration_plate];

        if ($exclude_id) {
            $query .= " AND id != :id";
            $params[':id'] = $exclude_id;
        }

        $result = $db->queryOne($query, $params);
        return (int)($result['count'] ?? 0) > 0;
    }

    public static function create(array $data) {
        $db = new Database();
        $query = "
            INSERT INTO vehicles (
                vehicle_number, registration_plate, vehicle_type, model, 
                manufacture_year, capacity, status, last_inspection
            ) VALUES (
                :vehicle_number, :registration_plate, :vehicle_type, :model,
                :manufacture_year, :capacity, :status, :last_inspection
            )
        ";

        return $db->execute($query, [
            ':vehicle_number' => $data['vehicle_number'],
            ':registration_plate' => $data['registration_plate'],
            ':vehicle_type' => $data['vehicle_type'],
            ':model' => $data['model'],
            ':manufacture_year' => $data['manufacture_year'],
            ':capacity' => $data['capacity'],
            ':status' => $data['status'],
            ':last_inspection' => $data['last_inspection']
        ]);
    }

    public static function update($id, array $data) {
        $db = new Database();
        $query = "
            UPDATE vehicles SET
                vehicle_number = :vehicle_number,
                registration_plate = :registration_plate,
                vehicle_type = :vehicle_type,
                model = :model,
                manufacture_year = :manufacture_year,
                capacity = :capacity,
                status = :status,
                last_inspection = :last_inspection,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ";

        return $db->execute($query, [
            ':vehicle_number' => $data['vehicle_number'],
            ':registration_plate' => $data['registration_plate'],
            ':vehicle_type' => $data['vehicle_type'],
            ':model' => $data['model'],
            ':manufacture_year' => $data['manufacture_year'],
            ':capacity' => $data['capacity'],
            ':status' => $data['status'],
            ':last_inspection' => $data['last_inspection'],
            ':id' => $id
        ]);
    }

    public static function delete($id) {
        $db = new Database();
        $query = "DELETE FROM vehicles WHERE id = :id";
        return $db->execute($query, [':id' => $id]);
    }

    public static function listNotBroken() {
        $db = new Database();
        $query = "
            SELECT id, vehicle_number, model, registration_plate
            FROM vehicles
            WHERE status != 'broken'
            ORDER BY vehicle_number
        ";

        return $db->query($query);
    }

    public static function listAll() {
        $db = new Database();
        $query = "SELECT id, vehicle_number, model, registration_plate FROM vehicles ORDER BY vehicle_number";
        return $db->query($query);
    }
}
