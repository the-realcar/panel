<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

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
                                <?php echo e($schedule['nr_poj'] ?? 'Brak'); ?><br>
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
                                <?php echo e($incident['nr_poj'] ?? 'Brak'); ?>
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
        <a href="/driver/report-incident.php" class="btn btn-primary">
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
                <a href="/driver/route-card.php" class="btn btn-primary btn-block">
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
                <a href="/driver/schedule.php" class="btn btn-secondary btn-block">
                    Zobacz grafik
                </a>
            </div>
        </div>
    </div>
</div>

<?php View::partial('layouts/footer'); ?>
