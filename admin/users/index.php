<?php
/**
 * Users List
 * Panel Pracowniczy Firma KOT
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/RBAC.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

$rbac = new RBAC();
$rbac->requirePermission('users', 'read');

$db = new Database();

// Get filter
$status_filter = $_GET['status'] ?? '';

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = ITEMS_PER_PAGE;
$offset = ($page - 1) * $per_page;

// Build query
$where_clause = '';
$params = [];

if ($status_filter === 'active') {
    $where_clause = 'WHERE u.active = TRUE';
} elseif ($status_filter === 'inactive') {
    $where_clause = 'WHERE u.active = FALSE';
}

// Get total count
$count_query = "SELECT COUNT(*) as total FROM users u $where_clause";
$count_result = $db->queryOne($count_query, $params);
$total_items = $count_result['total'];
$total_pages = ceil($total_items / $per_page);

// Get users with roles and positions
$params[':limit'] = $per_page;
$params[':offset'] = $offset;

$query = "
    SELECT u.*,
           STRING_AGG(DISTINCT r.name, ', ') as roles,
           STRING_AGG(DISTINCT p.name, ', ') as positions
    FROM users u
    LEFT JOIN user_roles ur ON u.id = ur.user_id
    LEFT JOIN roles r ON ur.role_id = r.id
    LEFT JOIN user_positions up ON u.id = up.user_id
    LEFT JOIN positions p ON up.position_id = p.id
    $where_clause
    GROUP BY u.id
    ORDER BY u.username ASC
    LIMIT :limit OFFSET :offset
";
$users = $db->query($query, $params);

$page_title = 'Zarządzanie użytkownikami';
include __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h1>👥 Zarządzanie użytkownikami</h1>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="form-inline">
            <label for="status">Filtruj po statusie:</label>
            <select name="status" id="status" class="form-control" onchange="this.form.submit()">
                <option value="">Wszyscy</option>
                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Aktywni</option>
                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Nieaktywni</option>
            </select>
        </form>
    </div>
    <div class="card-body">
        <?php if (empty($users)): ?>
            <p class="text-muted">Brak użytkowników do wyświetlenia.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Użytkownik</th>
                            <th>Email</th>
                            <th>Imię i nazwisko</th>
                            <th>Role</th>
                            <th>Stanowiska</th>
                            <th>Status</th>
                            <th>Ostatnie logowanie</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td data-label="ID"><?php echo $user['id']; ?></td>
                            <td data-label="Użytkownik"><strong><?php echo e($user['username']); ?></strong></td>
                            <td data-label="Email"><?php echo e($user['email']); ?></td>
                            <td data-label="Imię i nazwisko">
                                <?php echo e(getFullName($user['first_name'], $user['last_name'])); ?>
                            </td>
                            <td data-label="Role">
                                <?php if ($user['roles']): ?>
                                    <small><?php echo e($user['roles']); ?></small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Stanowiska">
                                <?php if ($user['positions']): ?>
                                    <small><?php echo e($user['positions']); ?></small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Status">
                                <?php if ($user['active']): ?>
                                    <span class="badge badge-success">Aktywny</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Nieaktywny</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Ostatnie logowanie">
                                <?php if ($user['last_login']): ?>
                                    <small><?php echo formatDateTime($user['last_login'], 'd.m.Y H:i'); ?></small>
                                <?php else: ?>
                                    <span class="text-muted">Nigdy</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Akcje">
                                <div class="btn-group">
                                    <?php if ($rbac->hasPermission('users', 'update')): ?>
                                        <a href="/admin/users/assign-position.php?user_id=<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-secondary">📋 Stanowiska</a>
                                    <?php endif; ?>
                                    <?php if ($rbac->hasPermission('users', 'update') && $user['id'] != getCurrentUserId()): ?>
                                        <?php if ($user['active']): ?>
                                            <a href="/admin/users/toggle-status.php?id=<?php echo $user['id']; ?>&action=deactivate&csrf_token=<?php echo generateCsrfToken(); ?>" 
                                               class="btn btn-sm btn-warning"
                                               onclick="return confirm('Czy na pewno chcesz dezaktywować tego użytkownika?');">
                                                🔒 Dezaktywuj
                                            </a>
                                        <?php else: ?>
                                            <a href="/admin/users/toggle-status.php?id=<?php echo $user['id']; ?>&action=activate&csrf_token=<?php echo generateCsrfToken(); ?>" 
                                               class="btn btn-sm btn-success">
                                                ✅ Aktywuj
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($total_pages > 1): ?>
                <?php echo pagination($page, $total_pages, '/admin/users/index.php' . ($status_filter ? '?status=' . urlencode($status_filter) . '&' : '?')); ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
