<?php

class Brigade {
    public static function supportsDepartures(): bool {
        $db = new Database();
        return $db->tableExists('brigade_departures');
    }

    private static function departureSummarySelect() {
        if (!self::supportsDepartures()) {
            return "
                NULL AS departures_summary,
                0 AS departures_count
            ";
        }

        return "
            (
                SELECT STRING_AGG(
                    TO_CHAR(bd.departure_time, 'HH24:MI') || ' (' || bd.direction || ')',
                    ', '
                    ORDER BY bd.departure_time ASC, bd.id ASC
                )
                FROM brigade_departures bd
                WHERE bd.brigade_id = b.id
            ) AS departures_summary,
            (
                SELECT COUNT(*)
                FROM brigade_departures bd
                WHERE bd.brigade_id = b.id
            ) AS departures_count
        ";
    }

    public static function normalizeBrigadeNumber($brigade_number) {
        $brigade_number = trim((string)$brigade_number);
        if (strpos($brigade_number, '/') !== false) {
            $parts = explode('/', $brigade_number);
            $brigade_number = trim((string)end($parts));
        }

        return $brigade_number;
    }

    public static function countByLine($line_id = null, $active_only = false) {
        $db = new Database();
        $where_parts = [];
        $params = [];

        if ($line_id !== null) {
            $where_parts[] = 'b.line_id = :line_id';
            $params[':line_id'] = $line_id;
        }
        
        if ($active_only) {
            $where_parts[] = 'b.active = TRUE';
        }

        $where = $where_parts ? 'WHERE ' . implode(' AND ', $where_parts) : '';
        
        $query = "SELECT COUNT(*) as total FROM brigades b $where";
        $result = $db->queryOne($query, $params);
        return (int)($result['total'] ?? 0);
    }

    public static function listByLine($line_id, $active_only = false) {
        $db = new Database();
        $where = 'WHERE b.line_id = :line_id';
        if ($active_only) {
            $where .= ' AND b.active = TRUE';
        }
        
        $query = "
            SELECT b.*, l.line_number, l.name as line_name,
                   " . self::departureSummarySelect() . "
            FROM brigades b
            INNER JOIN lines l ON b.line_id = l.id
            $where
            ORDER BY b.brigade_number ASC
        ";
        
        return $db->query($query, [':line_id' => $line_id]);
    }

    public static function listAll($limit = 50, $offset = 0, $active_only = false, $line_id = null) {
        $db = new Database();
        $where_parts = [];
        $params = [
            ':limit' => $limit,
            ':offset' => $offset
        ];

        if ($active_only) {
            $where_parts[] = 'b.active = TRUE';
        }

        if ($line_id !== null) {
            $where_parts[] = 'b.line_id = :line_id';
            $params[':line_id'] = $line_id;
        }

        $where = $where_parts ? 'WHERE ' . implode(' AND ', $where_parts) : '';
        
        $query = "
            SELECT b.*, l.line_number, l.name as line_name,
                   " . self::departureSummarySelect() . "
            FROM brigades b
            INNER JOIN lines l ON b.line_id = l.id
            $where
            ORDER BY l.line_number ASC, b.brigade_number ASC
            LIMIT :limit OFFSET :offset
        ";
        
        return $db->query($query, $params);
    }

    public static function listActive() {
        $db = new Database();
        $query = "
            SELECT b.*, l.line_number, l.name as line_name,
                   " . self::departureSummarySelect() . "
            FROM brigades b
            INNER JOIN lines l ON b.line_id = l.id
            WHERE b.active = TRUE
            ORDER BY l.line_number ASC, b.brigade_number ASC
        ";
        return $db->query($query);
    }

    public static function find($id) {
        $db = new Database();
        $query = "
            SELECT b.*, l.line_number, l.name as line_name,
                   " . self::departureSummarySelect() . "
            FROM brigades b
            INNER JOIN lines l ON b.line_id = l.id
            WHERE b.id = :id
        ";
        return $db->queryOne($query, [':id' => $id]);
    }

    public static function exists($line_id, $brigade_number, $exclude_id = null) {
        $db = new Database();
        $query = "SELECT COUNT(*) as count FROM brigades WHERE line_id = :line_id AND brigade_number = :brigade_number";
        $params = [
            ':line_id' => $line_id,
            ':brigade_number' => $brigade_number
        ];

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
            INSERT INTO brigades (
                line_id, brigade_number, is_peak, peak_type, shift_a_start, shift_a_end, shift_b_start, shift_b_end,
                shift_a_first_stop, shift_a_last_stop, shift_a_capacity, shift_b_first_stop, shift_b_last_stop, shift_b_capacity,
                default_vehicle_type, przewoznik, description, active
            ) VALUES (
                :line_id, :brigade_number, :is_peak, :peak_type, :shift_a_start, :shift_a_end, :shift_b_start, :shift_b_end,
                :shift_a_first_stop, :shift_a_last_stop, :shift_a_capacity, :shift_b_first_stop, :shift_b_last_stop, :shift_b_capacity,
                :default_vehicle_type, :przewoznik, :description, :active
            )
        ";

        $db->execute($query, [
            ':line_id' => $data['line_id'],
            ':brigade_number' => self::normalizeBrigadeNumber($data['brigade_number']),
            ':is_peak' => $data['is_peak'] ?? false,
            ':peak_type' => $data['peak_type'] ?? null,
            ':shift_a_start' => !empty($data['shift_a_start']) ? $data['shift_a_start'] : null,
            ':shift_a_end'   => !empty($data['shift_a_end'])   ? $data['shift_a_end']   : null,
            ':shift_b_start' => !empty($data['shift_b_start']) ? $data['shift_b_start'] : null,
            ':shift_b_end'   => !empty($data['shift_b_end'])   ? $data['shift_b_end']   : null,
            ':shift_a_first_stop' => !empty($data['shift_a_first_stop']) ? $data['shift_a_first_stop'] : null,
            ':shift_a_last_stop'  => !empty($data['shift_a_last_stop'])  ? $data['shift_a_last_stop']  : null,
            ':shift_a_capacity'   => !empty($data['shift_a_capacity'])   ? $data['shift_a_capacity']   : null,
            ':shift_b_first_stop' => !empty($data['shift_b_first_stop']) ? $data['shift_b_first_stop'] : null,
            ':shift_b_last_stop'  => !empty($data['shift_b_last_stop'])  ? $data['shift_b_last_stop']  : null,
            ':shift_b_capacity'   => !empty($data['shift_b_capacity'])   ? $data['shift_b_capacity']   : null,
            ':default_vehicle_type' => $data['default_vehicle_type'] ?? null,
            ':przewoznik' => !empty($data['przewoznik']) ? $data['przewoznik'] : null,
            ':description' => $data['description'] ?? null,
            ':active' => $data['active'] ?? true
        ]);
        return $db->lastInsertId('brigades_id_seq');
    }

    public static function update($id, array $data) {
        $db = new Database();
        $query = "
            UPDATE brigades SET
                line_id = :line_id,
                brigade_number = :brigade_number,
                is_peak = :is_peak,
                peak_type = :peak_type,
                shift_a_start = :shift_a_start,
                shift_a_end = :shift_a_end,
                shift_b_start = :shift_b_start,
                shift_b_end = :shift_b_end,
                shift_a_first_stop = :shift_a_first_stop,
                shift_a_last_stop = :shift_a_last_stop,
                shift_a_capacity = :shift_a_capacity,
                shift_b_first_stop = :shift_b_first_stop,
                shift_b_last_stop = :shift_b_last_stop,
                shift_b_capacity = :shift_b_capacity,
                default_vehicle_type = :default_vehicle_type,
                przewoznik = :przewoznik,
                description = :description,
                active = :active
            WHERE id = :id
        ";

        return $db->execute($query, [
            ':id' => $id,
            ':line_id' => $data['line_id'],
            ':brigade_number' => self::normalizeBrigadeNumber($data['brigade_number']),
            ':is_peak' => $data['is_peak'] ?? false,
            ':peak_type' => $data['peak_type'] ?? null,
            ':shift_a_start' => !empty($data['shift_a_start']) ? $data['shift_a_start'] : null,
            ':shift_a_end'   => !empty($data['shift_a_end'])   ? $data['shift_a_end']   : null,
            ':shift_b_start' => !empty($data['shift_b_start']) ? $data['shift_b_start'] : null,
            ':shift_b_end'   => !empty($data['shift_b_end'])   ? $data['shift_b_end']   : null,
            ':shift_a_first_stop' => !empty($data['shift_a_first_stop']) ? $data['shift_a_first_stop'] : null,
            ':shift_a_last_stop'  => !empty($data['shift_a_last_stop'])  ? $data['shift_a_last_stop']  : null,
            ':shift_a_capacity'   => !empty($data['shift_a_capacity'])   ? $data['shift_a_capacity']   : null,
            ':shift_b_first_stop' => !empty($data['shift_b_first_stop']) ? $data['shift_b_first_stop'] : null,
            ':shift_b_last_stop'  => !empty($data['shift_b_last_stop'])  ? $data['shift_b_last_stop']  : null,
            ':shift_b_capacity'   => !empty($data['shift_b_capacity'])   ? $data['shift_b_capacity']   : null,
            ':default_vehicle_type' => $data['default_vehicle_type'] ?? null,
            ':przewoznik' => !empty($data['przewoznik']) ? $data['przewoznik'] : null,
            ':description' => $data['description'] ?? null,
            ':active' => $data['active'] ?? true
        ]);
    }

    public static function delete($id) {
        $db = new Database();
        $query = "DELETE FROM brigades WHERE id = :id";
        return $db->execute($query, [':id' => $id]);
    }

    public static function isUsedInSchedules($id) {
        $db = new Database();
        $query = "SELECT COUNT(*) as count FROM schedules WHERE brigade_id = :id";
        $result = $db->queryOne($query, [':id' => $id]);
        return (int)($result['count'] ?? 0) > 0;
    }

    public static function listDepartures($brigade_id) {
        if (!self::supportsDepartures()) {
            return [];
        }

        $db = new Database();
        $query = '
            SELECT id, brigade_id, departure_time, direction
            FROM brigade_departures
            WHERE brigade_id = :brigade_id
            ORDER BY departure_time ASC, id ASC
        ';

        return $db->query($query, [':brigade_id' => $brigade_id]);
    }

    public static function replaceDepartures($brigade_id, array $departures) {
        if (!self::supportsDepartures()) {
            throw new RuntimeException('Tabela odjazdów brygad nie jest dostępna w tej bazie danych.');
        }

        $db = new Database();
        $db->beginTransaction();

        try {
            $db->execute('DELETE FROM brigade_departures WHERE brigade_id = :brigade_id', [':brigade_id' => $brigade_id]);

            if (!empty($departures)) {
                $insert_query = '
                    INSERT INTO brigade_departures (brigade_id, departure_time, direction)
                    VALUES (:brigade_id, :departure_time, :direction)
                ';

                foreach ($departures as $departure) {
                    $db->execute($insert_query, [
                        ':brigade_id' => $brigade_id,
                        ':departure_time' => $departure['departure_time'],
                        ':direction' => $departure['direction']
                    ]);
                }
            }

            $db->commit();
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollback();
            }
            throw $e;
        }
    }
}
