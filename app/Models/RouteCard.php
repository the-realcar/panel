<?php

class RouteCard {
    public static function create(array $data) {
        $db = new Database();
        $query = "
            INSERT INTO route_cards 
            (user_id, vehicle_id, line_id, route_date, start_time, end_time, 
             start_km, end_km, fuel_start, fuel_end, passengers_count, notes, status)
            VALUES 
            (:user_id, :vehicle_id, :line_id, :route_date, :start_time, :end_time,
             :start_km, :end_km, :fuel_start, :fuel_end, :passengers_count, :notes, 'completed')
        ";

        return $db->execute($query, [
            ':user_id' => $data['user_id'],
            ':vehicle_id' => $data['vehicle_id'],
            ':line_id' => $data['line_id'],
            ':route_date' => $data['route_date'],
            ':start_time' => $data['start_time'],
            ':end_time' => $data['end_time'],
            ':start_km' => $data['start_km'],
            ':end_km' => $data['end_km'],
            ':fuel_start' => $data['fuel_start'],
            ':fuel_end' => $data['fuel_end'],
            ':passengers_count' => $data['passengers_count'],
            ':notes' => $data['notes']
        ]);
    }

    public static function getRecentByUser($user_id, $limit = 10) {
        $db = new Database();
        $query = "
            SELECT rc.*, 
                   v.vehicle_number, v.model,
                   l.line_number, l.name as line_name
            FROM route_cards rc
            LEFT JOIN vehicles v ON rc.vehicle_id = v.id
            LEFT JOIN lines l ON rc.line_id = l.id
            WHERE rc.user_id = :user_id
            ORDER BY rc.route_date DESC, rc.created_at DESC
            LIMIT :limit
        ";

        return $db->query($query, [
            ':user_id' => $user_id,
            ':limit' => $limit
        ]);
    }
}
