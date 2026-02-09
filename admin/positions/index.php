<?php
/**
 * Positions List
 * Panel Pracowniczy Firma KOT
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/RBAC.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

$rbac = new RBAC();
$rbac->requirePermission('positions', 'read');

$db = new Database();

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = ITEMS_PER_PAGE;
$offset = ($page - 1) * $per_page;

// Get total count
$count_query = "SELECT COUNT(*) as total FROM positions";
$count_result = $db->queryOne($count_query);
$total_items = $count_result['total'];
$total_pages = ceil($total_items / $per_page);

// Get positions with current count
$query = "
    SELECT p.*, 
           d.name as department_name,
           COUNT(up.id) as current_count
    FROM positions p
    LEFT JOIN departments d ON p.department_id = d.id
    LEFT JOIN user_positions up ON p.id = up.position_id
    GROUP BY p.id, d.name
    ORDER BY p.name ASC
    LIMIT :limit OFFSET :offset
";
$positions = $db->query($query, [
    ':limit' => $per_page,
    ':offset' => $offset
]);

$page_title = 'Zarządzanie stanowiskami';
include __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h1>💼 Zarządzanie stanowiskami</h1>
    <?php if ($rbac->hasPermission('positions', 'create')): ?>
        <a href="/admin/positions/create.php" class="btn btn-primary">➕ Dodaj stanowisko</a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($positions)): ?>
            <p class="text-muted">Brak stanowisk do wyświetlenia.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nazwa</th>
                            <th>Dział</th>
                            <th>Limit</th>
                            <th>Obecny stan</th>
                            <th>Opis</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($positions as $position): ?>
                        <tr>
                            <td data-label="ID"><?php echo $position['id']; ?></td>
                            <td data-label="Nazwa"><strong><?php echo e($position['name']); ?></strong></td>
                            <td data-label="Dział"><?php echo e($position['department_name'] ?? '-'); ?></td>
                            <td data-label="Limit">
                                <?php if ($position['max_count']): ?>
                                    <?php echo $position['max_count']; ?>
                                <?php else: ?>
                                    <span class="text-muted">Bez limitu</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Obecny stan">
                                <?php 
                                $current = $position['current_count'];
                                $max = $position['max_count'];
                                ?>
                                <?php echo $current; ?>
                                <?php if ($max): ?>
                                    / <?php echo $max; ?>
                                    <div class="progress-bar" style="margin-top: 5px;">
                                        <div class="progress-fill" style="width: <?php echo min(100, ($current / $max) * 100); ?>%; background-color: <?php echo $current >= $max ? 'var(--danger)' : 'var(--success)'; ?>;"></div>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td data-label="Opis">
                                <?php echo e(truncate($position['description'] ?? '', 50)); ?>
                            </td>
                            <td data-label="Akcje">
                                <?php if ($rbac->hasPermission('positions', 'update')): ?>
                                    <a href="/admin/positions/edit.php?id=<?php echo $position['id']; ?>" 
                                       class="btn btn-sm btn-secondary">✏️ Edytuj</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($total_pages > 1): ?>
                <?php echo pagination($page, $total_pages, '/admin/positions/index.php'); ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
