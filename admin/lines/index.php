<?php
/**
 * Lines List
 * Panel Pracowniczy Firma KOT
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/RBAC.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

$rbac = new RBAC();
$rbac->requirePermission('lines', 'read');

$db = new Database();

// Get filter
$type_filter = $_GET['type'] ?? '';

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = ITEMS_PER_PAGE;
$offset = ($page - 1) * $per_page;

// Build query
$where_clause = '';
$params = [];

if ($type_filter) {
    $where_clause = 'WHERE line_type = :type';
    $params[':type'] = $type_filter;
}

// Get total count
$count_query = "SELECT COUNT(*) as total FROM lines $where_clause";
$count_result = $db->queryOne($count_query, $params);
$total_items = $count_result['total'];
$total_pages = ceil($total_items / $per_page);

// Get lines
$params[':limit'] = $per_page;
$params[':offset'] = $offset;

$query = "
    SELECT * FROM lines
    $where_clause
    ORDER BY line_number ASC
    LIMIT :limit OFFSET :offset
";
$lines = $db->query($query, $params);

$page_title = 'Zarządzanie liniami';
include __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h1>🚏 Zarządzanie liniami</h1>
    <?php if ($rbac->hasPermission('lines', 'create')): ?>
        <a href="/admin/lines/create.php" class="btn btn-primary">➕ Dodaj linię</a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="form-inline">
            <label for="type">Filtruj po typie:</label>
            <select name="type" id="type" class="form-control" onchange="this.form.submit()">
                <option value="">Wszystkie</option>
                <option value="bus" <?php echo $type_filter === 'bus' ? 'selected' : ''; ?>>Autobus</option>
                <option value="tram" <?php echo $type_filter === 'tram' ? 'selected' : ''; ?>>Tramwaj</option>
                <option value="metro" <?php echo $type_filter === 'metro' ? 'selected' : ''; ?>>Metro</option>
            </select>
        </form>
    </div>
    <div class="card-body">
        <?php if (empty($lines)): ?>
            <p class="text-muted">Brak linii do wyświetlenia.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Numer linii</th>
                            <th>Nazwa</th>
                            <th>Typ</th>
                            <th>Status</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lines as $line): ?>
                        <tr>
                            <td data-label="ID"><?php echo $line['id']; ?></td>
                            <td data-label="Numer linii"><strong><?php echo e($line['line_number']); ?></strong></td>
                            <td data-label="Nazwa"><?php echo e($line['name']); ?></td>
                            <td data-label="Typ"><?php echo e($line['line_type']); ?></td>
                            <td data-label="Status">
                                <?php if ($line['active']): ?>
                                    <span class="badge badge-success">Aktywna</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Nieaktywna</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Akcje">
                                <div class="btn-group">
                                    <?php if ($rbac->hasPermission('lines', 'update')): ?>
                                        <a href="/admin/lines/edit.php?id=<?php echo $line['id']; ?>" 
                                           class="btn btn-sm btn-secondary">✏️ Edytuj</a>
                                    <?php endif; ?>
                                    <?php if ($rbac->hasPermission('lines', 'delete')): ?>
                                        <a href="/admin/lines/delete.php?id=<?php echo $line['id']; ?>&csrf_token=<?php echo generateCsrfToken(); ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Czy na pewno chcesz usunąć tę linię?');">🗑️ Usuń</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($total_pages > 1): ?>
                <?php echo pagination($page, $total_pages, '/admin/lines/index.php' . ($type_filter ? '?type=' . urlencode($type_filter) . '&' : '?')); ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
