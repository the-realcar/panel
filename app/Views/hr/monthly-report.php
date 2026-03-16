<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
    <h1>📄 Raport miesieczny: stanowiska i spolki</h1>
    <div style="display: flex; gap: 0.5rem;">
        <a href="/hr/work-hours.php?month=<?php echo urlencode($month); ?>" class="btn btn-secondary">← Wroc do ECP</a>
        <a href="/hr/export-report.php?format=csv&month=<?php echo urlencode($month); ?>" class="btn btn-secondary">Eksport CSV</a>
        <a href="/hr/export-report.php?format=pdf&month=<?php echo urlencode($month); ?>" class="btn btn-secondary">Eksport PDF</a>
        <button class="btn btn-primary" onclick="window.print()">Drukuj / Zapisz PDF</button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <p><strong>Okres raportu:</strong> <?php echo e($month); ?></p>
        <p><strong>Wygenerowano:</strong> <?php echo e(formatDateTime($generated_at, 'd.m.Y H:i')); ?></p>
    </div>
</div>

<div class="dashboard-grid">
    <div class="stat-card">
        <div class="stat-card-title">Aktywni pracownicy</div>
        <div class="stat-card-value"><?php echo (int)($totals['active_people'] ?? 0); ?></div>
    </div>
    <div class="stat-card" style="border-left-color: var(--info);">
        <div class="stat-card-title">Wykonane sluzby</div>
        <div class="stat-card-value"><?php echo (int)($totals['completed_shifts'] ?? 0); ?></div>
    </div>
    <div class="stat-card" style="border-left-color: var(--success);">
        <div class="stat-card-title">Wykonane kursy</div>
        <div class="stat-card-value"><?php echo (int)($totals['executed_courses'] ?? 0); ?></div>
    </div>
    <div class="stat-card" style="border-left-color: var(--warning);">
        <div class="stat-card-title">Obsluzone przystanki</div>
        <div class="stat-card-value"><?php echo (int)($totals['served_stops'] ?? 0); ?></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Podsumowanie miesieczne</h2>
    </div>
    <div class="card-body">
        <ul>
            <li>Karty drogowe: <strong><?php echo (int)($totals['route_cards_count'] ?? 0); ?></strong></li>
            <li>Przewiezieni pasazerowie: <strong><?php echo (int)($totals['passengers_total'] ?? 0); ?></strong></li>
            <li>Zgloszone incydenty: <strong><?php echo (int)($totals['incidents_count'] ?? 0); ?></strong></li>
        </ul>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Szczegoly per stanowisko i spolka</h2>
    </div>
    <div class="card-body">
        <?php if (empty($rows)): ?>
            <p class="text-muted">Brak danych dla wybranego miesiaca.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Spolka</th>
                            <th>Stanowisko</th>
                            <th>Aktywni pracownicy</th>
                            <th>Wykonane sluzby</th>
                            <th>Karty drogowe</th>
                            <th>Wykonane kursy</th>
                            <th>Obsluzone przystanki</th>
                            <th>Pasazerowie</th>
                            <th>Incydenty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td data-label="Spolka"><?php echo e($row['company_name'] ?? 'Nieprzypisano'); ?></td>
                                <td data-label="Stanowisko"><?php echo e($row['position_name'] ?? '-'); ?></td>
                                <td data-label="Aktywni pracownicy"><?php echo (int)($row['active_people'] ?? 0); ?></td>
                                <td data-label="Wykonane sluzby"><?php echo (int)($row['completed_shifts'] ?? 0); ?></td>
                                <td data-label="Karty drogowe"><?php echo (int)($row['route_cards_count'] ?? 0); ?></td>
                                <td data-label="Wykonane kursy"><?php echo (int)($row['executed_courses'] ?? 0); ?></td>
                                <td data-label="Obsluzone przystanki"><?php echo (int)($row['served_stops'] ?? 0); ?></td>
                                <td data-label="Pasazerowie"><?php echo (int)($row['passengers_total'] ?? 0); ?></td>
                                <td data-label="Incydenty"><?php echo (int)($row['incidents_count'] ?? 0); ?></td>
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
