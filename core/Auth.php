<?php
/**
 * Authentication Class
 * Panel Pracowniczy Firma KOT
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Login user
     * 
     * @param string $username
     * @param string $password
     * @param string $ip_address
     * @param string $user_agent
     * @return bool
     */
    public function login($username, $password, $ip_address = null, $user_agent = null) {
        try {
            // Get user from database
            $sql = "SELECT * FROM users WHERE username = :username AND active = TRUE";
            $user = $this->db->queryOne($sql, [':username' => $username]);
            
            $success = false;
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['last_activity'] = time();
                
                // Update last login
                $updateSql = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id";
                $this->db->execute($updateSql, [':id' => $user['id']]);
                
                // Load user roles
                $this->loadUserRoles($user['id']);
                
                // Regenerate session ID for security
                regenerateSession();
                
                $success = true;
            }
            
            // Log login attempt
            $this->logLoginAttempt($user['id'] ?? null, $ip_address, $user_agent, $success);
            
            return $success;
            
        } catch (Exception $e) {
            error_log('Login error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Find active user by username
     *
     * @param string $username
     * @return array|false
     */
    public function findActiveUserByUsername($username) {
        try {
            $sql = "SELECT * FROM users WHERE username = :username AND active = TRUE";
            $user = $this->db->queryOne($sql, [':username' => $username]);
            return $user ?: false;
        } catch (Exception $e) {
            error_log('User lookup error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Find active user by OAuth provider ID
     *
     * @param string $provider
     * @param string $provider_id
     * @return array|false
     */
    public function findActiveUserByProviderId($provider, $provider_id) {
        $column = null;
        if ($provider === 'discord') {
            $column = 'discord_id';
        } elseif ($provider === 'roblox') {
            $column = 'roblox_id';
        }

        if (!$column) {
            return false;
        }

        try {
            $sql = "SELECT * FROM users WHERE {$column} = :provider_id AND active = TRUE";
            $user = $this->db->queryOne($sql, [':provider_id' => $provider_id]);
            return $user ?: false;
        } catch (Exception $e) {
            error_log('User lookup error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log in without password (OAuth/SAML)
     *
     * @param array $user
     * @param string|null $ip_address
     * @param string|null $user_agent
     * @return bool
     */
    public function loginWithUser(array $user, $ip_address = null, $user_agent = null) {
        try {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['last_activity'] = time();

            $updateSql = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id";
            $this->db->execute($updateSql, [':id' => $user['id']]);

            $this->loadUserRoles($user['id']);
            regenerateSession();

            $this->logLoginAttempt($user['id'], $ip_address, $user_agent, true);
            return true;
        } catch (Exception $e) {
            error_log('OAuth login error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log an authentication attempt
     *
     * @param int|null $user_id
     * @param string|null $ip_address
     * @param string|null $user_agent
     * @param bool $success
     */
    public function recordLoginAttempt($user_id, $ip_address, $user_agent, $success) {
        $this->logLoginAttempt($user_id, $ip_address, $user_agent, $success);
    }
    
    /**
     * Logout user
     */
    public function logout() {
        session_unset();
        session_destroy();
    }
    
    /**
     * Check if user is authenticated
     * 
     * @return bool
     */
    public function isAuthenticated() {
        return isLoggedIn() && checkSessionTimeout();
    }
    
    /**
     * Load user roles and permissions
     * 
     * @param int $user_id
     */
    private function loadUserRoles($user_id) {
        $sql = "SELECT r.id, r.name, r.permissions 
                FROM roles r
                INNER JOIN user_roles ur ON r.id = ur.role_id
                WHERE ur.user_id = :user_id";
        
        $roles = $this->db->query($sql, [':user_id' => $user_id]);
        
        $_SESSION['roles'] = [];
        $_SESSION['permissions'] = [];
        
        foreach ($roles as $role) {
            $_SESSION['roles'][] = $role['name'];
            
            // Merge permissions
            if (!empty($role['permissions'])) {
                $permissions = json_decode($role['permissions'], true);
                if (is_array($permissions)) {
                    foreach ($permissions as $resource => $actions) {
                        if (!isset($_SESSION['permissions'][$resource])) {
                            $_SESSION['permissions'][$resource] = [];
                        }
                        $_SESSION['permissions'][$resource] = array_unique(
                            array_merge($_SESSION['permissions'][$resource], $actions)
                        );
                    }
                }
            }
        }
    }
    
    /**
     * Log login attempt
     * 
     * @param int|null $user_id
     * @param string|null $ip_address
     * @param string|null $user_agent
     * @param bool $success
     */
    private function logLoginAttempt($user_id, $ip_address, $user_agent, $success) {
        try {
            $sql = "INSERT INTO login_logs (user_id, ip_address, user_agent, success) 
                    VALUES (:user_id, :ip_address, :user_agent, :success)";
            
            $this->db->execute($sql, [
                ':user_id' => $user_id,
                ':ip_address' => $ip_address,
                ':user_agent' => $user_agent,
                ':success' => $success ? 't' : 'f'
            ]);
        } catch (Exception $e) {
            error_log('Failed to log login attempt: ' . $e->getMessage());
        }
    }
    
    /**
     * Reset password request
     * 
     * @param string $email
     * @return bool
     */
    public function requestPasswordReset($email) {
        try {
            // Check if user exists
            $sql = "SELECT id FROM users WHERE email = :email AND active = TRUE";
            $user = $this->db->queryOne($sql, [':email' => $email]);
            
            if (!$user) {
                return false;
            }
            
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Save reset token
            $insertSql = "INSERT INTO password_resets (user_id, token, expires_at) 
                         VALUES (:user_id, :token, :expires_at)";
            
            $this->db->execute($insertSql, [
                ':user_id' => $user['id'],
                ':token' => $token,
                ':expires_at' => $expires
            ]);
            
            // TODO: Send email with reset link
            // For now, just return the token
            $_SESSION['reset_token'] = $token;
            
            return true;
            
        } catch (Exception $e) {
            error_log('Password reset request error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verify reset token
     * 
     * @param string $token
     * @return int|false User ID if valid, false otherwise
     */
    public function verifyResetToken($token) {
        try {
            $sql = "SELECT user_id FROM password_resets 
                    WHERE token = :token 
                    AND expires_at > CURRENT_TIMESTAMP 
                    AND used = FALSE";
            
            $result = $this->db->queryOne($sql, [':token' => $token]);
            
            return $result ? $result['user_id'] : false;
            
        } catch (Exception $e) {
            error_log('Token verification error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Reset password
     * 
     * @param string $token
     * @param string $new_password
     * @return bool
     */
    public function resetPassword($token, $new_password) {
        try {
            $user_id = $this->verifyResetToken($token);
            
            if (!$user_id) {
                return false;
            }
            
            // Hash new password
            $password_hash = password_hash($new_password, PASSWORD_BCRYPT);
            
            $this->db->beginTransaction();
            
            // Update password
            $updateSql = "UPDATE users SET password_hash = :password_hash WHERE id = :id";
            $this->db->execute($updateSql, [
                ':password_hash' => $password_hash,
                ':id' => $user_id
            ]);
            
            // Mark token as used
            $markUsedSql = "UPDATE password_resets SET used = TRUE WHERE token = :token";
            $this->db->execute($markUsedSql, [':token' => $token]);
            
            $this->db->commit();
            
            return true;
            
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            error_log('Password reset error: ' . $e->getMessage());
            return false;
        }
    }
}
