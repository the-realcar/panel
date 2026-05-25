<?php

class Vehicle {
    private static function hasNormaSpalinColumn() {
        $db = new Database();
        return $db->columnExists('vehicles', 'norma_spalin');
    }

    private static function hasNormaSpalaniaColumn() {
        $db = new Database();
        return $db->columnExists('vehicles', 'norma_spalania');
    }

    private static function hasNotesColumn() {
        $db = new Database();
        return $db->columnExists('vehicles', 'notes');
    }

    private static function vehicleSelectSql() {
        $norma_select = 'NULL::VARCHAR AS norma_spalin';
        if (self::hasNormaSpalinColumn() && self::hasNormaSpalaniaColumn()) {
            $norma_select = 'COALESCE(v.norma_spalin, v.norma_spalania) AS norma_spalin';
        } elseif (self::hasNormaSpalinColumn()) {
            $norma_select = 'v.norma_spalin AS norma_spalin';
        } elseif (self::hasNormaSpalaniaColumn()) {
            $norma_select = 'v.norma_spalania AS norma_spalin';
        }

        $notes_select = self::hasNotesColumn()
            ? 'COALESCE(v.notes, v.dodatkowe_informacje) AS notes'
            : 'v.dodatkowe_informacje AS notes';

        return 'v.*, ' . $norma_select . ', ' . $notes_select;
    }

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
            SELECT " . self::vehicleSelectSql() . "
            FROM vehicles v
            $where
            ORDER BY v.id ASC
            LIMIT :limit OFFSET :offset
        ";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        return $db->query($query, $params);
    }

    public static function find($id) {
        $db = new Database();
        $query = "SELECT " . self::vehicleSelectSql() . " FROM vehicles v WHERE v.id = :id";
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

        $columns = [
            'nr_poj', 'reg_plate', 'vehicle_type', 'model', 'rok_prod', 'pojemnosc', 'status',
            'marka', 'pulpit', 'engine', 'gearbox', 'typ_napedu', 'klimatyzacja',
            'zajezdnia', 'przewoznik', 'opiekun_1', 'opiekun_2', 'dodatkowe_informacje'
        ];

        $params = [
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
            ':klimatyzacja' => $data['klimatyzacja'] ?? false,
            ':zajezdnia' => $data['zajezdnia'] ?? null,
            ':przewoznik' => $data['przewoznik'] ?? null,
            ':opiekun_1' => $data['opiekun_1'] ?? null,
            ':opiekun_2' => $data['opiekun_2'] ?? null,
            ':dodatkowe_informacje' => $data['notes'] ?? ($data['dodatkowe_informacje'] ?? null)
        ];

        if (self::hasNormaSpalinColumn()) {
            $columns[] = 'norma_spalin';
            $params[':norma_spalin'] = $data['norma_spalin'] ?? null;
        }

        if (self::hasNormaSpalaniaColumn()) {
            $columns[] = 'norma_spalania';
            $params[':norma_spalania'] = $data['norma_spalin'] ?? ($data['norma_spalania'] ?? null);
        }

        if (self::hasNotesColumn()) {
            $columns[] = 'notes';
            $params[':notes'] = $data['notes'] ?? ($data['dodatkowe_informacje'] ?? null);
        }

        $placeholders = array_map(static function ($column) {
            return ':' . $column;
        }, $columns);

        $query = "
            INSERT INTO vehicles (" . implode(', ', $columns) . ")
            VALUES (" . implode(', ', $placeholders) . ")
        ";

        $db->execute($query, $params);
        return $db->lastInsertId('vehicles_id_seq');
    }

    public static function update($id, array $data) {
        $db = new Database();
        $set_parts = [
            'nr_poj = :nr_poj',
            'reg_plate = :reg_plate',
            'vehicle_type = :vehicle_type',
            'model = :model',
            'rok_prod = :rok_prod',
            'pojemnosc = :pojemnosc',
            'status = :status',
            'marka = :marka',
            'pulpit = :pulpit',
            'engine = :engine',
            'gearbox = :gearbox',
            'typ_napedu = :typ_napedu',
            'klimatyzacja = :klimatyzacja',
            'zajezdnia = :zajezdnia',
            'przewoznik = :przewoznik',
            'opiekun_1 = :opiekun_1',
            'opiekun_2 = :opiekun_2',
            'dodatkowe_informacje = :dodatkowe_informacje',
            'updated_at = CURRENT_TIMESTAMP'
        ];

        if (self::hasNormaSpalinColumn()) {
            $set_parts[] = 'norma_spalin = :norma_spalin';
        }

        if (self::hasNormaSpalaniaColumn()) {
            $set_parts[] = 'norma_spalania = :norma_spalania';
        }

        if (self::hasNotesColumn()) {
            $set_parts[] = 'notes = :notes';
        }

        $query = "
            UPDATE vehicles SET
                " . implode(', ', $set_parts) . "
            WHERE id = :id
        ";

        $params = [
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
            ':klimatyzacja' => $data['klimatyzacja'] ?? false,
            ':zajezdnia' => $data['zajezdnia'] ?? null,
            ':przewoznik' => $data['przewoznik'] ?? null,
            ':opiekun_1' => $data['opiekun_1'] ?? null,
            ':opiekun_2' => $data['opiekun_2'] ?? null,
            ':dodatkowe_informacje' => $data['notes'] ?? ($data['dodatkowe_informacje'] ?? null),
            ':id' => $id
        ];

        if (self::hasNormaSpalinColumn()) {
            $params[':norma_spalin'] = $data['norma_spalin'] ?? null;
        }

        if (self::hasNormaSpalaniaColumn()) {
            $params[':norma_spalania'] = $data['norma_spalin'] ?? ($data['norma_spalania'] ?? null);
        }

        if (self::hasNotesColumn()) {
            $params[':notes'] = $data['notes'] ?? ($data['dodatkowe_informacje'] ?? null);
        }

        return $db->execute($query, $params);
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
            WHERE status != 'odstawiony'
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
