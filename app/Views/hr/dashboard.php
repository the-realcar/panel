<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>👥 Panel Kadr</h1>
    <a href="/hr/work-hours.php?month=<?php echo urlencode($month); ?>" class="btn btn-primary">Przejdz do ECP</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" class="form-inline">
            <div class="form-group">
                <label for="month">Miesiac:</label>
                <input type="month" id="month" name="month" class="form-control" value="<?php echo e($month); ?>">
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-secondary">Filtruj</button>
            </div>
        </form>
    </div>
</div>

<div class="dashboard-grid">
    <div class="stat-card">
        <div class="stat-card-title">Pracownicy w zestawieniu</div>
        <div class="stat-card-value"><?php echo count($summary); ?></div>
    </div>
    <div class="stat-card" style="border-left-color: var(--success);">
        <div class="stat-card-title">Suma godzin (miesiac)</div>
        <div class="stat-card-value"><?php echo number_format($total_hours, 2, ',', ' '); ?></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Podsumowanie ECP: <?php echo e($month); ?></h2>
    </div>
    <div class="card-body">
        <?php if (empty($summary)): ?>
            <p class="text-muted">Brak danych dla wybranego okresu.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Pracownik</th>
                            <th>Liczba dni</th>
                            <th>Suma godzin</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($summary as $row): ?>
                            <tr>
                                <td data-label="Pracownik">
                                    <?php echo e(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''))); ?>
                                    <br><small class="text-muted"><?php echo e($row['username']); ?></small>
                                </td>
                                <td data-label="Liczba dni"><?php echo (int)$row['days_count']; ?></td>
                                <td data-label="Suma godzin"><strong><?php echo number_format((float)$row['total_hours'], 2, ',', ' '); ?></strong></td>
                                <td data-label="Akcje">
                                    <a class="btn btn-secondary" href="/hr/work-hours.php?month=<?php echo urlencode($month); ?>&user_id=<?php echo (int)$row['user_id']; ?>">
                                        Edytuj ECP
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php View::partial('layouts/footer'); ?>
