<?php

class RouteCard {
    public static function create(array $data) {
        $db = new Database();
        $query = "
            INSERT INTO route_cards
            (user_id, vehicle_id, line_id, route_date, start_time, end_time,
             passengers_count, notes, status)
            VALUES
            (:user_id, :vehicle_id, :line_id, :route_date, :start_time, :end_time,
             :passengers_count, :notes, 'completed')
            RETURNING id
        ";

        $result = $db->queryOne($query, [
            ':user_id'          => $data['user_id'],
            ':vehicle_id'       => $data['vehicle_id'],
            ':line_id'          => $data['line_id'],
            ':route_date'       => $data['route_date'],
            ':start_time'       => $data['start_time'],
            ':end_time'         => $data['end_time'],
            ':passengers_count' => $data['passengers_count'],
            ':notes'            => $data['notes']
        ]);

        return $result ? (int)$result['id'] : null;
    }

    public static function createTrips($route_card_id, array $trips) {
        if (empty($trips)) return;

        $db = new Database();
        $query = "
            INSERT INTO route_card_trips (route_card_id, route_variant_id, trips_count)
            VALUES (:route_card_id, :route_variant_id, :trips_count)
        ";

        foreach ($trips as $variant_id => $count) {
            $count = max(0, (int)$count);
            if ($count > 0) {
                $db->execute($query, [
                    ':route_card_id'    => $route_card_id,
                    ':route_variant_id' => (int)$variant_id,
                    ':trips_count'      => $count
                ]);
            }
        }
    }

    public static function getRecentByUser($user_id, $limit = 10) {
        $db = new Database();
        $query = "
            SELECT rc.*,
                   v.nr_poj, v.model,
                   l.line_number, l.name as line_name,
                   COALESCE(
                       (SELECT SUM(trips_count) FROM route_card_trips WHERE route_card_id = rc.id),
                       0
                   ) as total_trips
            FROM route_cards rc
            LEFT JOIN vehicles v ON rc.vehicle_id = v.id
            LEFT JOIN lines l ON rc.line_id = l.id
            WHERE rc.user_id = :user_id
            ORDER BY rc.route_date DESC, rc.created_at DESC
            LIMIT :limit
        ";

        return $db->query($query, [
            ':user_id' => $user_id,
            ':limit'   => $limit
        ]);
    }
}
