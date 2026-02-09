<?php
/**
 * Vehicles List
 * Panel Pracowniczy Firma KOT
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/RBAC.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

$rbac = new RBAC();
$rbac->requirePermission('vehicles', 'read');

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

if ($status_filter) {
    $where_clause = 'WHERE status = :status';
    $params[':status'] = $status_filter;
}

// Get total count
$count_query = "SELECT COUNT(*) as total FROM vehicles $where_clause";
$count_result = $db->queryOne($count_query, $params);
$total_items = $count_result['total'];
$total_pages = ceil($total_items / $per_page);

// Get vehicles
$params[':limit'] = $per_page;
$params[':offset'] = $offset;

$query = "
    SELECT * FROM vehicles
    $where_clause
    ORDER BY vehicle_number ASC
    LIMIT :limit OFFSET :offset
";
$vehicles = $db->query($query, $params);

$page_title = 'Zarządzanie pojazdami';
include __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h1>🚌 Zarządzanie pojazdami</h1>
    <?php if ($rbac->hasPermission('vehicles', 'create')): ?>
        <a href="/admin/vehicles/create.php" class="btn btn-primary">➕ Dodaj pojazd</a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="form-inline">
            <label for="status">Filtruj po statusie:</label>
            <select name="status" id="status" class="form-control" onchange="this.form.submit()">
                <option value="">Wszystkie</option>
                <option value="available" <?php echo $status_filter === 'available' ? 'selected' : ''; ?>>Dostępny</option>
                <option value="in_use" <?php echo $status_filter === 'in_use' ? 'selected' : ''; ?>>W użyciu</option>
                <option value="maintenance" <?php echo $status_filter === 'maintenance' ? 'selected' : ''; ?>>Serwis</option>
                <option value="broken" <?php echo $status_filter === 'broken' ? 'selected' : ''; ?>>Awaria</option>
            </select>
        </form>
    </div>
    <div class="card-body">
        <?php if (empty($vehicles)): ?>
            <p class="text-muted">Brak pojazdów do wyświetlenia.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Numer pojazdu</th>
                            <th>Rejestracja</th>
                            <th>Typ</th>
                            <th>Model</th>
                            <th>Status</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vehicles as $vehicle): ?>
                        <tr>
                            <td data-label="ID"><?php echo $vehicle['id']; ?></td>
                            <td data-label="Numer pojazdu"><strong><?php echo e($vehicle['vehicle_number']); ?></strong></td>
                            <td data-label="Rejestracja"><?php echo e($vehicle['registration_plate'] ?? '-'); ?></td>
                            <td data-label="Typ"><?php echo e($vehicle['vehicle_type']); ?></td>
                            <td data-label="Model"><?php echo e($vehicle['model'] ?? '-'); ?></td>
                            <td data-label="Status"><?php echo getStatusBadge($vehicle['status']); ?></td>
                            <td data-label="Akcje">
                                <div class="btn-group">
                                    <?php if ($rbac->hasPermission('vehicles', 'update')): ?>
                                        <a href="/admin/vehicles/edit.php?id=<?php echo $vehicle['id']; ?>" 
                                           class="btn btn-sm btn-secondary">✏️ Edytuj</a>
                                    <?php endif; ?>
                                    <?php if ($rbac->hasPermission('vehicles', 'delete')): ?>
                                        <a href="/admin/vehicles/delete.php?id=<?php echo $vehicle['id']; ?>&csrf_token=<?php echo generateCsrfToken(); ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Czy na pewno chcesz usunąć ten pojazd?');">🗑️ Usuń</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($total_pages > 1): ?>
                <?php echo pagination($page, $total_pages, '/admin/vehicles/index.php' . ($status_filter ? '?status=' . urlencode($status_filter) . '&' : '?')); ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
