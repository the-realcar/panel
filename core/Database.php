<?php
/**
 * Database Class
 * PDO Wrapper for PostgreSQL
 * Panel Pracowniczy Firma KOT
 */

class Database {
    private $pdo;

    private function normalizeResultEncoding($value) {
        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = $this->normalizeResultEncoding($item);
            }
            return $value;
        }

        if (!is_string($value) || $value === '') {
            return $value;
        }

        static $replacements = null;

        if ($replacements === null) {
            $replacements = [
                hex2bin('c384e280a6') => hex2bin('c485'),
                hex2bin('d384e280a6') => hex2bin('c485'),
                hex2bin('c384e280a1') => hex2bin('c487'),
                hex2bin('d384e280a1') => hex2bin('c487'),
                hex2bin('c384e284a2') => hex2bin('c499'),
                hex2bin('d384e284a2') => hex2bin('c499'),
                hex2bin('c385e2809a') => hex2bin('c582'),
                hex2bin('d385e2809a') => hex2bin('c582'),
                hex2bin('c385e2809e') => hex2bin('c584'),
                hex2bin('d385e2809e') => hex2bin('c584'),
                hex2bin('c383c2b3') => hex2bin('c3b3'),
                hex2bin('d383c2b3') => hex2bin('c3b3'),
                hex2bin('c385e280ba') => hex2bin('c59b'),
                hex2bin('d385e280ba') => hex2bin('c59b'),
                hex2bin('c385c2ba') => hex2bin('c5ba'),
                hex2bin('d385c2ba') => hex2bin('c5ba'),
                hex2bin('c385c2bc') => hex2bin('c5bc'),
                hex2bin('d385c2bc') => hex2bin('c5bc'),
                hex2bin('c384e2809e') => hex2bin('c484'),
                hex2bin('d384e2809e') => hex2bin('c484'),
                hex2bin('c384e280a0') => hex2bin('c486'),
                hex2bin('d384e280a0') => hex2bin('c486'),
                hex2bin('c384cb9c') => hex2bin('c498'),
                hex2bin('d384cb9c') => hex2bin('c498'),
                hex2bin('c385c692') => hex2bin('c583'),
                hex2bin('d385c692') => hex2bin('c583'),
                hex2bin('c383e2809c') => hex2bin('c393'),
                hex2bin('d383e2809c') => hex2bin('c393'),
                hex2bin('c385c5a1') => hex2bin('c59a'),
                hex2bin('d385c5a1') => hex2bin('c59a'),
                hex2bin('c385c2b9') => hex2bin('c5b9'),
                hex2bin('d385c2b9') => hex2bin('c5b9'),
                hex2bin('c385c2bb') => hex2bin('c5bb')
                ,hex2bin('d385c2bb') => hex2bin('c5bb')
            ];
        }

        return str_replace(array_keys($replacements), array_values($replacements), $value);
    }
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->pdo = getDatabaseConnection();
    }
    
    /**
     * Get PDO instance
     * 
     * @return PDO
     */
    public function getConnection() {
        return $this->pdo;
    }
    
    /**
     * Execute query and return all results
     * 
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $this->normalizeResultEncoding($stmt->fetchAll());
        } catch (PDOException $e) {
            error_log('Database query error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Execute query and return single row
     * 
     * @param string $sql
     * @param array $params
     * @return array|false
     */
    public function queryOne($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $this->normalizeResultEncoding($stmt->fetch());
        } catch (PDOException $e) {
            error_log('Database query error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Execute insert/update/delete query
     * 
     * @param string $sql
     * @param array $params
     * @return bool
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('Database execute error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get last insert ID
     * 
     * @param string $sequence Sequence name (PostgreSQL specific)
     * @return int
     */
    public function lastInsertId($sequence = null) {
        return $this->pdo->lastInsertId($sequence);
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->pdo->rollBack();
    }
    
    /**
     * Check if in transaction
     * 
     * @return bool
     */
    public function inTransaction() {
        return $this->pdo->inTransaction();
    }
}
