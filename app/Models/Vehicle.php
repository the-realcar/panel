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
            ORDER BY nr_poj ASC
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

    public static function existsByNumber($nr_poj, $exclude_id = null) {
        $db = new Database();
        $query = "SELECT COUNT(*) as count FROM vehicles WHERE nr_poj = :nr_poj";
        $params = [':nr_poj' => $nr_poj];

        if ($exclude_id) {
            $query .= " AND id != :id";
            $params[':id'] = $exclude_id;
        }

        $result = $db->queryOne($query, $params);
        return (int)($result['count'] ?? 0) > 0;
    }

    public static function existsByPlate($reg_plate, $exclude_id = null) {
        $db = new Database();
        $query = "SELECT COUNT(*) as count FROM vehicles WHERE reg_plate = :reg_plate";
        $params = [':reg_plate' => $reg_plate];

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
                nr_poj, reg_plate, vehicle_type, model, rok_prod, pojemnosc, status,
                marka, pulpit, engine, gearbox, typ_napedu, norma_spalania, klimatyzacja,
                zajezdnia, przewoznik, opiekun_1, opiekun_2, dodatkowe_informacje
            ) VALUES (
                :nr_poj, :reg_plate, :vehicle_type, :model, :rok_prod, :pojemnosc, :status,
                :marka, :pulpit, :engine, :gearbox, :typ_napedu, :norma_spalania, :klimatyzacja,
                :zajezdnia, :przewoznik, :opiekun_1, :opiekun_2, :dodatkowe_informacje
            )
        ";

        return $db->execute($query, [
            ':nr_poj' => $data['nr_poj'],
            ':reg_plate' => $data['reg_plate'],
            ':vehicle_type' => $data['vehicle_type'],
            ':model' => $data['model'],
            ':rok_prod' => $data['rok_prod'],
            ':pojemnosc' => $data['pojemnosc'],
            ':status' => $data['status'],
            ':marka' => $data['marka'] ?? null,
            ':pulpit' => $data['pulpit'] ?? null,
            ':engine' => $data['engine'] ?? null,
            ':gearbox' => $data['gearbox'] ?? null,
            ':typ_napedu' => $data['typ_napedu'] ?? null,
            ':norma_spalania' => $data['norma_spalania'] ?? null,
            ':klimatyzacja' => $data['klimatyzacja'] ?? false,
            ':zajezdnia' => $data['zajezdnia'] ?? null,
            ':przewoznik' => $data['przewoznik'] ?? null,
            ':opiekun_1' => $data['opiekun_1'] ?? null,
            ':opiekun_2' => $data['opiekun_2'] ?? null,
            ':dodatkowe_informacje' => $data['dodatkowe_informacje'] ?? null
        ]);
    }

    public static function update($id, array $data) {
        $db = new Database();
        $query = "
            UPDATE vehicles SET
                nr_poj = :nr_poj,
                reg_plate = :reg_plate,
                vehicle_type = :vehicle_type,
                model = :model,
                rok_prod = :rok_prod,
                pojemnosc = :pojemnosc,
                status = :status,
                marka = :marka,
                pulpit = :pulpit,
                engine = :engine,
                gearbox = :gearbox,
                typ_napedu = :typ_napedu,
                norma_spalania = :norma_spalania,
                klimatyzacja = :klimatyzacja,
                zajezdnia = :zajezdnia,
                przewoznik = :przewoznik,
                opiekun_1 = :opiekun_1,
                opiekun_2 = :opiekun_2,
                dodatkowe_informacje = :dodatkowe_informacje,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ";

        return $db->execute($query, [
            ':nr_poj' => $data['nr_poj'],
            ':reg_plate' => $data['reg_plate'],
            ':vehicle_type' => $data['vehicle_type'],
            ':model' => $data['model'],
            ':rok_prod' => $data['rok_prod'],
            ':pojemnosc' => $data['pojemnosc'],
            ':status' => $data['status'],
            ':marka' => $data['marka'] ?? null,
            ':pulpit' => $data['pulpit'] ?? null,
            ':engine' => $data['engine'] ?? null,
            ':gearbox' => $data['gearbox'] ?? null,
            ':typ_napedu' => $data['typ_napedu'] ?? null,
            ':norma_spalania' => $data['norma_spalania'] ?? null,
            ':klimatyzacja' => $data['klimatyzacja'] ?? false,
            ':zajezdnia' => $data['zajezdnia'] ?? null,
            ':przewoznik' => $data['przewoznik'] ?? null,
            ':opiekun_1' => $data['opiekun_1'] ?? null,
            ':opiekun_2' => $data['opiekun_2'] ?? null,
            ':dodatkowe_informacje' => $data['dodatkowe_informacje'] ?? null,
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
            SELECT id, nr_poj, model, reg_plate
            FROM vehicles
            WHERE status = 'sprawny'
            ORDER BY nr_poj
        ";

        return $db->query($query);
    }

    public static function listAll() {
        $db = new Database();
        $query = "SELECT id, nr_poj, model, reg_plate FROM vehicles ORDER BY nr_poj";
        return $db->query($query);
    }
}
