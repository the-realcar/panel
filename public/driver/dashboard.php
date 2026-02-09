<?php
/**
 * Driver Dashboard
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

// Get today's schedules
$today = date('Y-m-d');
$schedules_query = "
    SELECT s.*, 
           v.vehicle_number, v.model, 
           l.line_number, l.name as line_name
    FROM schedules s
    LEFT JOIN vehicles v ON s.vehicle_id = v.id
    LEFT JOIN lines l ON s.line_id = l.id
    WHERE s.user_id = :user_id AND s.schedule_date = :today
    ORDER BY s.start_time ASC
";
$today_schedules = $db->query($schedules_query, [
    ':user_id' => $user_id,
    ':today' => $today
]);

// Get statistics
$stats_query = "
    SELECT 
        COUNT(CASE WHEN schedule_date = :today THEN 1 END) as today_count,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count,
        COUNT(CASE WHEN schedule_date >= :today THEN 1 END) as upcoming_count
    FROM schedules
    WHERE user_id = :user_id
";
$stats = $db->queryOne($stats_query, [
    ':user_id' => $user_id,
    ':today' => $today
]);

// Get recent incidents
$incidents_query = "
    SELECT i.*, v.vehicle_number
    FROM incidents i
    LEFT JOIN vehicles v ON i.vehicle_id = v.id
    WHERE i.reported_by = :user_id
    ORDER BY i.incident_date DESC
    LIMIT 5
";
$recent_incidents = $db->query($incidents_query, [':user_id' => $user_id]);

$page_title = 'Panel Kierowcy';
include __DIR__ . '/../../includes/header.php';
?>

<h1>👋 Witaj, <?php echo e(getCurrentUsername()); ?>!</h1>

<div class="dashboard-grid">
    <div class="stat-card">
        <div class="stat-card-title">Dzisiejsze zmiany</div>
        <div class="stat-card-value"><?php echo $stats['today_count'] ?? 0; ?></div>
    </div>
    
    <div class="stat-card" style="border-left-color: var(--success);">
        <div class="stat-card-title">Wykonane zmiany</div>
        <div class="stat-card-value"><?php echo $stats['completed_count'] ?? 0; ?></div>
    </div>
    
    <div class="stat-card" style="border-left-color: var(--info);">
        <div class="stat-card-title">Nadchodzące zmiany</div>
        <div class="stat-card-value"><?php echo $stats['upcoming_count'] ?? 0; ?></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">📅 Dzisiejszy grafik (<?php echo formatDate($today, 'd.m.Y'); ?>)</h2>
    </div>
    <div class="card-body">
        <?php if (empty($today_schedules)): ?>
            <p class="text-muted">Brak zaplanowanych zmian na dzisiaj.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Godzina</th>
                            <th>Pojazd</th>
                            <th>Linia</th>
                            <th>Status</th>
                            <th>Uwagi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($today_schedules as $schedule): ?>
                        <tr>
                            <td data-label="Godzina">
                                <?php echo formatTime($schedule['start_time']); ?> - 
                                <?php echo formatTime($schedule['end_time']); ?>
                            </td>
                            <td data-label="Pojazd">
                                <?php echo e($schedule['vehicle_number'] ?? 'Brak'); ?><br>
                                <small class="text-muted"><?php echo e($schedule['model'] ?? ''); ?></small>
                            </td>
                            <td data-label="Linia">
                                <strong><?php echo e($schedule['line_number'] ?? 'Brak'); ?></strong><br>
                                <small class="text-muted"><?php echo e($schedule['line_name'] ?? ''); ?></small>
                            </td>
                            <td data-label="Status">
                                <?php echo getStatusBadge($schedule['status']); ?>
                            </td>
                            <td data-label="Uwagi">
                                <?php echo e($schedule['notes'] ?? '-'); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">⚠️ Ostatnie zgłoszenia</h2>
    </div>
    <div class="card-body">
        <?php if (empty($recent_incidents)): ?>
            <p class="text-muted">Brak zgłoszeń.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Pojazd</th>
                            <th>Typ</th>
                            <th>Tytuł</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_incidents as $incident): ?>
                        <tr>
                            <td data-label="Data">
                                <?php echo formatDateTime($incident['incident_date'], 'd.m.Y H:i'); ?>
                            </td>
                            <td data-label="Pojazd">
                                <?php echo e($incident['vehicle_number'] ?? 'Brak'); ?>
                            </td>
                            <td data-label="Typ">
                                <?php echo e($incident['incident_type']); ?>
                            </td>
                            <td data-label="Tytuł">
                                <?php echo e($incident['title']); ?>
                            </td>
                            <td data-label="Status">
                                <?php echo getStatusBadge($incident['status']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-footer">
        <a href="/public/driver/report-incident.php" class="btn btn-primary">
            Zgłoś awarię
        </a>
    </div>
</div>

<div class="row">
    <div class="col col-12 col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">📝 Karta drogowa</h3>
            </div>
            <div class="card-body">
                <p>Wypełnij kartę drogową po każdej zmianie.</p>
                <a href="/public/driver/route-card.php" class="btn btn-primary btn-block">
                    Wypełnij kartę
                </a>
            </div>
        </div>
    </div>
    
    <div class="col col-12 col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">📅 Pełny grafik</h3>
            </div>
            <div class="card-body">
                <p>Zobacz swój pełny grafik pracy na najbliższe dni.</p>
                <a href="/public/driver/schedule.php" class="btn btn-secondary btn-block">
                    Zobacz grafik
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
