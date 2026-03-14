<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
    <h1>📄 Raport miesieczny pracownika</h1>
    <div style="display: flex; gap: 0.5rem;">
        <a href="/hr/work-hours.php?month=<?php echo urlencode($month); ?>&user_id=<?php echo (int)$user['id']; ?>" class="btn btn-secondary">← Wroc do ECP</a>
        <a href="/hr/export-report.php?format=csv&month=<?php echo urlencode($month); ?>&user_id=<?php echo (int)$user['id']; ?>" class="btn btn-secondary">Eksport CSV</a>
        <a href="/hr/export-report.php?format=pdf&month=<?php echo urlencode($month); ?>&user_id=<?php echo (int)$user['id']; ?>" class="btn btn-secondary">Eksport PDF</a>
        <button class="btn btn-primary" onclick="window.print()">Drukuj / Zapisz PDF</button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <p><strong>Pracownik:</strong> <?php echo e(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))); ?> (<?php echo e($user['username']); ?>)</p>
        <p><strong>Okres raportu:</strong> <?php echo e($month); ?></p>
        <p><strong>Wygenerowano:</strong> <?php echo e(formatDateTime($generated_at, 'd.m.Y H:i')); ?></p>
    </div>
</div>

<div class="dashboard-grid">
    <div class="stat-card">
        <div class="stat-card-title">Godziny (ECP)</div>
        <div class="stat-card-value"><?php echo number_format((float)($work_hours['total_hours'] ?? 0), 2, ',', ' '); ?></div>
    </div>
    <div class="stat-card" style="border-left-color: var(--info);">
        <div class="stat-card-title">Liczba dni pracy</div>
        <div class="stat-card-value"><?php echo (int)($work_hours['days_count'] ?? 0); ?></div>
    </div>
    <div class="stat-card" style="border-left-color: var(--success);">
        <div class="stat-card-title">Liczba sluzb</div>
        <div class="stat-card-value"><?php echo (int)($schedule_stats['shifts_count'] ?? 0); ?></div>
    </div>
    <div class="stat-card" style="border-left-color: var(--warning);">
        <div class="stat-card-title">Karty drogowe</div>
        <div class="stat-card-value"><?php echo (int)($route_stats['route_cards_count'] ?? 0); ?></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Podsumowanie operacyjne</h2>
    </div>
    <div class="card-body">
        <ul>
            <li>Wykonane sluzby: <strong><?php echo (int)($schedule_stats['completed_shifts'] ?? 0); ?></strong></li>
            <li>Przewiezieni pasazerowie (z kart drogowych): <strong><?php echo (int)($route_stats['passengers_total'] ?? 0); ?></strong></li>
            <li>Zgloszone incydenty: <strong><?php echo (int)($incident_stats['incidents_count'] ?? 0); ?></strong></li>
        </ul>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Szczegoly ECP</h2>
    </div>
    <div class="card-body">
        <?php if (empty($entries)): ?>
            <p class="text-muted">Brak wpisow ECP dla wybranego miesiaca.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Godziny</th>
                            <th>Uwagi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entries as $entry): ?>
                            <tr>
                                <td data-label="Data"><?php echo formatDate($entry['work_date'], 'd.m.Y'); ?></td>
                                <td data-label="Godziny"><?php echo number_format((float)$entry['hours_worked'], 2, ',', ' '); ?></td>
                                <td data-label="Uwagi"><?php echo e($entry['notes'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
@media print {
    .header, .nav, .btn, .form-actions { display: none !important; }
    .main-content { padding: 0 !important; }
    .card { break-inside: avoid; box-shadow: none !important; border: 1px solid #ddd; }
}
</style>

<?php View::partial('layouts/footer'); ?>
