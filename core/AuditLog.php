<?php
/**
 * AuditLog — rejestrowanie akcji użytkowników w tabeli audit_logs
 * Panel Pracowniczy Firma KOT
 */

class AuditLog {
    /**
     * Zapisz wpis audytowy.
     *
     * @param string     $action     Nazwa akcji, np. 'vehicle.create', 'user.delete'
     * @param string     $table_name Nazwa tabeli, której dotyczy akcja
     * @param int|null   $record_id  ID rekordu
     * @param array|null $old_values Stare wartości (np. przed edycją)
     * @param array|null $new_values Nowe wartości (np. po edycji)
     */
    public static function log(
        string $action,
        string $table_name = '',
        ?int   $record_id  = null,
        ?array $old_values = null,
        ?array $new_values = null
    ): void {
        try {
            $db = new Database();

            $user_id    = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;

            $sql = "INSERT INTO audit_logs
                        (user_id, action, table_name, record_id, old_values, new_values, ip_address)
                    VALUES
                        (:user_id, :action, :table_name, :record_id, :old_values, :new_values, :ip_address)";

            $db->execute($sql, [
                ':user_id'    => $user_id,
                ':action'     => $action,
                ':table_name' => $table_name ?: null,
                ':record_id'  => $record_id,
                ':old_values' => $old_values !== null ? json_encode($old_values, JSON_UNESCAPED_UNICODE) : null,
                ':new_values' => $new_values !== null ? json_encode($new_values, JSON_UNESCAPED_UNICODE) : null,
                ':ip_address' => $ip_address,
            ]);
        } catch (Exception $e) {
            // Nie przerywaj głównego przepływu aplikacji z powodu błędu audytu
            error_log('AuditLog error: ' . $e->getMessage());
        }
    }
}
