<?php
/**
 * Database Class
 * PDO Wrapper for PostgreSQL
 * Panel Pracowniczy Firma KOT
 */

class Database {
    private $pdo;
    
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
            return $stmt->fetchAll();
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
            return $stmt->fetch();
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
