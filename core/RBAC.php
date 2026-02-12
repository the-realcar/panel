<?php
/**
 * Role-Based Access Control (RBAC) Class
 * Panel Pracowniczy Firma KOT
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Database.php';

class RBAC {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Check if user has role
     * 
     * @param string $role_name
     * @param int|null $user_id If null, uses current user
     * @return bool
     */
    public function hasRole($role_name, $user_id = null) {
        if ($user_id === null) {
            // Check session
            return in_array($role_name, $_SESSION['roles'] ?? []);
        }
        
        $sql = "SELECT COUNT(*) as count
                FROM user_roles ur
                INNER JOIN roles r ON ur.role_id = r.id
                WHERE ur.user_id = :user_id AND r.name = :role_name";
        
        $result = $this->db->queryOne($sql, [
            ':user_id' => $user_id,
            ':role_name' => $role_name
        ]);
        
        return $result && $result['count'] > 0;
    }
    
    /**
     * Check if user has permission
     * 
     * @param string $resource Resource name (e.g., 'vehicles', 'users')
     * @param string $action Action name (e.g., 'read', 'create', 'update', 'delete')
     * @param int|null $user_id If null, uses current user
     * @return bool
     */
    public function hasPermission($resource, $action, $user_id = null) {
        if ($user_id === null) {
            // Check session
            $permissions = $_SESSION['permissions'] ?? [];
            return isset($permissions[$resource]) && in_array($action, $permissions[$resource]);
        }
        
        // Check database
        $sql = "SELECT r.permissions
                FROM user_roles ur
                INNER JOIN roles r ON ur.role_id = r.id
                WHERE ur.user_id = :user_id";
        
        $roles = $this->db->query($sql, [':user_id' => $user_id]);
        
        foreach ($roles as $role) {
            if (!empty($role['permissions'])) {
                $permissions = json_decode($role['permissions'], true);
                if (isset($permissions[$resource]) && in_array($action, $permissions[$resource])) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Check if user has any of the specified roles
     * 
     * @param array $role_names
     * @param int|null $user_id If null, uses current user
     * @return bool
     */
    public function hasAnyRole(array $role_names, $user_id = null) {
        foreach ($role_names as $role) {
            if ($this->hasRole($role, $user_id)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check if user has all of the specified roles
     * 
     * @param array $role_names
     * @param int|null $user_id If null, uses current user
     * @return bool
     */
    public function hasAllRoles(array $role_names, $user_id = null) {
        foreach ($role_names as $role) {
            if (!$this->hasRole($role, $user_id)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Get user roles
     * 
     * @param int|null $user_id If null, uses current user
     * @return array
     */
    public function getUserRoles($user_id = null) {
        if ($user_id === null) {
            return $_SESSION['roles'] ?? [];
        }
        
        $sql = "SELECT r.id, r.name, r.description
                FROM user_roles ur
                INNER JOIN roles r ON ur.role_id = r.id
                WHERE ur.user_id = :user_id";
        
        return $this->db->query($sql, [':user_id' => $user_id]);
    }
    
    /**
     * Get user permissions
     * 
     * @param int|null $user_id If null, uses current user
     * @return array
     */
    public function getUserPermissions($user_id = null) {
        if ($user_id === null) {
            return $_SESSION['permissions'] ?? [];
        }
        
        $sql = "SELECT r.permissions
                FROM user_roles ur
                INNER JOIN roles r ON ur.role_id = r.id
                WHERE ur.user_id = :user_id";
        
        $roles = $this->db->query($sql, [':user_id' => $user_id]);
        
        $all_permissions = [];
        foreach ($roles as $role) {
            if (!empty($role['permissions'])) {
                $permissions = json_decode($role['permissions'], true);
                if (is_array($permissions)) {
                    foreach ($permissions as $resource => $actions) {
                        if (!isset($all_permissions[$resource])) {
                            $all_permissions[$resource] = [];
                        }
                        $all_permissions[$resource] = array_unique(
                            array_merge($all_permissions[$resource], $actions)
                        );
                    }
                }
            }
        }
        
        return $all_permissions;
    }
    
    /**
     * Require permission (redirect if not authorized)
     * 
     * @param string $resource
     * @param string $action
     * @param string $redirect_url URL to redirect to if not authorized
     */
    public function requirePermission($resource, $action, $redirect_url = '/login.php') {
        if (!$this->hasPermission($resource, $action)) {
            setFlashMessage('error', 'Nie masz uprawnień do wykonania tej operacji.');
            header('Location: ' . $redirect_url);
            exit;
        }
    }
    
    /**
     * Require role (redirect if not authorized)
     * 
     * @param string $role_name
     * @param string $redirect_url URL to redirect to if not authorized
     */
    public function requireRole($role_name, $redirect_url = '/login.php') {
        if (!$this->hasRole($role_name)) {
            setFlashMessage('error', 'Nie masz uprawnień do dostępu do tej strony.');
            header('Location: ' . $redirect_url);
            exit;
        }
    }
    
    /**
     * Check if current user is administrator
     * 
     * @return bool
     */
    public function isAdmin() {
        return $this->hasRole('Administrator');
    }
}
