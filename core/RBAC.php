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

    private function getRoleHierarchy() {
        return [
            'Admin IT' => ['Administrator IT'],
            'Administrator IT' => ['Administrator'],
            'Administrator' => ['Zarząd', 'Nadzór Ruchu', 'Kontrole', 'Kadry', 'Transport', 'Zajezdnia'],
            'Zarząd' => ['Dyspozytor'],
            'Dyspozytor' => ['Kierowca'],
            'Transport' => ['Kierowca']
        ];
    }

    private function expandRoleNames(array $role_names) {
        $hierarchy = $this->getRoleHierarchy();
        $expanded = [];
        $stack = array_values(array_filter($role_names));

        while (!empty($stack)) {
            $role = array_pop($stack);
            if (isset($expanded[$role])) {
                continue;
            }

            $expanded[$role] = true;

            foreach ($hierarchy[$role] ?? [] as $inherited_role) {
                if (!isset($expanded[$inherited_role])) {
                    $stack[] = $inherited_role;
                }
            }
        }

        return array_keys($expanded);
    }

    private function getDirectRoleNames($user_id = null) {
        if ($user_id === null) {
            return $_SESSION['roles'] ?? [];
        }

        $sql = "SELECT r.name
                FROM user_roles ur
                INNER JOIN roles r ON ur.role_id = r.id
                WHERE ur.user_id = :user_id";

        $rows = $this->db->query($sql, [':user_id' => $user_id]);
        return array_values(array_unique(array_map(function ($row) {
            return $row['name'];
        }, $rows)));
    }

    private function getEffectiveRoleNames($user_id = null) {
        return $this->expandRoleNames($this->getDirectRoleNames($user_id));
    }

    private function getPermissionsForRoles(array $role_names) {
        if (empty($role_names)) {
            return [];
        }

        $placeholders = [];
        $params = [];

        foreach (array_values($role_names) as $index => $role_name) {
            $placeholder = ':role_' . $index;
            $placeholders[] = $placeholder;
            $params[$placeholder] = $role_name;
        }

        $sql = 'SELECT permissions FROM roles WHERE name IN (' . implode(', ', $placeholders) . ')';
        $roles = $this->db->query($sql, $params);

        $all_permissions = [];
        foreach ($roles as $role) {
            if (empty($role['permissions'])) {
                continue;
            }

            $permissions = json_decode($role['permissions'], true);
            if (!is_array($permissions)) {
                continue;
            }

            foreach ($permissions as $resource => $actions) {
                if (!isset($all_permissions[$resource])) {
                    $all_permissions[$resource] = [];
                }

                $all_permissions[$resource] = array_values(array_unique(array_merge(
                    $all_permissions[$resource],
                    is_array($actions) ? $actions : []
                )));
            }
        }

        return $all_permissions;
    }

    private function denyAccess(string $message = 'Brak uprawnien.') {
        http_response_code(403);

        echo '<!doctype html>';
        echo '<html lang="pl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
        echo '<title>403 - Brak dostepu</title>';
        echo '<style>body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;margin:0;background:#f6f7fb;color:#111}'
           . '.wrap{max-width:680px;margin:6rem auto;padding:1.5rem}'
           . '.card{background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:1.5rem}'
           . 'h1{margin:0 0 .75rem 0;font-size:1.4rem}'
           . 'p{margin:.5rem 0;color:#4b5563}'
           . 'a{display:inline-block;margin-top:1rem;color:#0b63ce;text-decoration:none}'
           . 'a:hover{text-decoration:underline}</style>';
        echo '</head><body><div class="wrap"><div class="card">';
        echo '<h1>403 - Brak dostepu</h1>';
        echo '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
        echo '<a href="/index.php">Wroc do strony glownej</a>';
        echo '</div></div></body></html>';
        exit;
    }
    
    /**
     * Check if user has role
     * 
     * @param string $role_name
     * @param int|null $user_id If null, uses current user
     * @return bool
     */
    public function hasRole($role_name, $user_id = null) {
        return in_array($role_name, $this->getEffectiveRoleNames($user_id), true);
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
        $permissions = $this->getPermissionsForRoles($this->getEffectiveRoleNames($user_id));
        return isset($permissions[$resource]) && in_array($action, $permissions[$resource], true);
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
        return $this->getEffectiveRoleNames($user_id);
    }
    
    /**
     * Get user permissions
     * 
     * @param int|null $user_id If null, uses current user
     * @return array
     */
    public function getUserPermissions($user_id = null) {
        return $this->getPermissionsForRoles($this->getEffectiveRoleNames($user_id));
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
            $this->denyAccess('Nie masz uprawnien do wykonania tej operacji.');
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
            $this->denyAccess('Nie masz uprawnien do dostepu do tej strony.');
        }
    }
    
    /**
     * Check if current user is administrator
     * 
     * @return bool
     */
    public function isAdmin() {
        return $this->hasAnyRole(['Administrator', 'Administrator IT', 'Admin IT']);
    }
}
