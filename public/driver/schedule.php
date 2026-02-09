<?php
/**
 * Driver Schedule Page
 * Panel Pracowniczy Firma KOT
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/RBAC.php';
require_once __DIR__ . '/../../includes/functions.php';

// Require login
requireLogin();

// Check if user has driver role
$rbac = new RBAC();
if (!$rbac->hasRole('Kierowca') && !$rbac->isAdmin()) {
    setFlashMessage('error', 'Brak dostępu do panelu kierowcy.');
    header('Location: /public/index.php');
    exit;
}

$db = new Database();
$user_id = getCurrentUserId();

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Filter by status
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$allowed_statuses = ['all', 'scheduled', 'completed', 'cancelled'];
if (!in_array($status_filter, $allowed_statuses)) {
    $status_filter = 'all';
}

// Build query
$where_clauses = ['s.user_id = :user_id'];
$params = [':user_id' => $user_id];

// Date range: next 30 days
$today = date('Y-m-d');
$end_date = date('Y-m-d', strtotime('+30 days'));
$where_clauses[] = 's.schedule_date BETWEEN :today AND :end_date';
$params[':today'] = $today;
$params[':end_date'] = $end_date;

// Status filter
if ($status_filter !== 'all') {
    $where_clauses[] = 's.status = :status';
    $params[':status'] = $status_filter;
}

$where_sql = implode(' AND ', $where_clauses);

// Count total records
$count_query = "SELECT COUNT(*) as total FROM schedules s WHERE $where_sql";
$count_result = $db->queryOne($count_query, $params);
$total_records = $count_result['total'];
$total_pages = ceil($total_records / $per_page);

// Get schedules
$schedules_query = "
    SELECT s.*, 
           v.vehicle_number, v.model, v.registration_plate,
           l.line_number, l.name as line_name
    FROM schedules s
    LEFT JOIN vehicles v ON s.vehicle_id = v.id
    LEFT JOIN lines l ON s.line_id = l.id
    WHERE $where_sql
    ORDER BY s.schedule_date ASC, s.start_time ASC
    LIMIT :limit OFFSET :offset
";
$params[':limit'] = $per_page;
$params[':offset'] = $offset;
$schedules = $db->query($schedules_query, $params);

$page_title = 'Grafik Pracy';
include __DIR__ . '/../../includes/header.php';
?>

<h1>📅 Grafik Pracy</h1>
<p class="text-muted">Twój grafik pracy na najbliższe 30 dni</p>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Filtruj grafik</h2>
    </div>
    <div class="card-body">
        <form method="GET" action="" class="form-inline">
            <div class="form-group">
                <label for="status">Status:</label>
                <select name="status" id="status" class="form-control" onchange="this.form.submit()">
                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Wszystkie</option>
                    <option value="scheduled" <?php echo $status_filter === 'scheduled' ? 'selected' : ''; ?>>Zaplanowane</option>
                    <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Wykonane</option>
                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Anulowane</option>
                </select>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Harmonogram zmian</h2>
        <div class="text-muted">
            Wyświetlono <?php echo count($schedules); ?> z <?php echo $total_records; ?> zmian
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($schedules)): ?>
            <p class="text-muted">Brak zaplanowanych zmian spełniających wybrane kryteria.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Godzina rozpoczęcia</th>
                            <th>Godzina zakończenia</th>
                            <th>Pojazd</th>
                            <th>Linia</th>
                            <th>Status</th>
                            <th>Uwagi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules as $schedule): ?>
                        <tr>
                            <td data-label="Data">
                                <?php echo formatDate($schedule['schedule_date'], 'd.m.Y'); ?>
                                <br><small class="text-muted"><?php echo formatDate($schedule['schedule_date'], 'l'); ?></small>
                            </td>
                            <td data-label="Godzina rozpoczęcia">
                                <?php echo formatTime($schedule['start_time']); ?>
                            </td>
                            <td data-label="Godzina zakończenia">
                                <?php echo formatTime($schedule['end_time']); ?>
                            </td>
                            <td data-label="Pojazd">
                                <?php if ($schedule['vehicle_number']): ?>
                                    <strong><?php echo e($schedule['vehicle_number']); ?></strong>
                                    <?php if ($schedule['registration_plate']): ?>
                                        <br><small class="text-muted"><?php echo e($schedule['registration_plate']); ?></small>
                                    <?php endif; ?>
                                    <?php if ($schedule['model']): ?>
                                        <br><small class="text-muted"><?php echo e($schedule['model']); ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">Brak</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Linia">
                                <?php if ($schedule['line_number']): ?>
                                    <strong><?php echo e($schedule['line_number']); ?></strong>
                                    <?php if ($schedule['line_name']): ?>
                                        <br><small class="text-muted"><?php echo e($schedule['line_name']); ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">Brak</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Status">
                                <?php echo getStatusBadge($schedule['status']); ?>
                            </td>
                            <td data-label="Uwagi">
                                <?php echo $schedule['notes'] ? e($schedule['notes']) : '-'; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if ($total_pages > 1): ?>
    <div class="card-footer">
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&status=<?php echo e($status_filter); ?>" class="btn btn-secondary">
                    &laquo; Poprzednia
                </a>
            <?php endif; ?>
            
            <span class="pagination-info">
                Strona <?php echo $page; ?> z <?php echo $total_pages; ?>
            </span>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>&status=<?php echo e($status_filter); ?>" class="btn btn-secondary">
                    Następna &raquo;
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body">
        <a href="/public/driver/dashboard.php" class="btn btn-secondary">
            &larr; Powrót do panelu
        </a>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
