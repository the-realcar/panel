<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1><?php echo htmlspecialchars($page_title); ?></h1>
        <div class="header-actions">
            <a href="/dispatcher/fleet.php" class="btn btn-secondary">Status Floty</a>
            <a href="/dispatcher/assign-schedule.php" class="btn btn-primary">Przydziel Grafik</a>
        </div>
    </div>

    <!-- Statystyki -->
    <div class="stats-grid">
        <div class="stat-card stat-success">
            <div class="stat-icon">🚍</div>
            <div class="stat-content">
                <div class="stat-label">Pojazdy dostępne</div>
                <div class="stat-value"><?php echo (int)$stats['vehicles_available']; ?></div>
            </div>
        </div>

        <div class="stat-card stat-info">
            <div class="stat-icon">🚌</div>
            <div class="stat-content">
                <div class="stat-label">Pojazdy w trasie</div>
                <div class="stat-value"><?php echo (int)$stats['vehicles_in_use']; ?></div>
            </div>
        </div>

        <div class="stat-card stat-warning">
            <div class="stat-icon">🔧</div>
            <div class="stat-content">
                <div class="stat-label">W konserwacji</div>
                <div class="stat-value"><?php echo (int)$stats['vehicles_maintenance']; ?></div>
            </div>
        </div>

        <div class="stat-card stat-danger">
            <div class="stat-icon">⚠️</div>
            <div class="stat-content">
                <div class="stat-label">Niesprawne</div>
                <div class="stat-value"><?php echo (int)$stats['vehicles_broken']; ?></div>
            </div>
        </div>

        <div class="stat-card stat-warning">
            <div class="stat-icon">📋</div>
            <div class="stat-content">
                <div class="stat-label">Otwarte incydenty</div>
                <div class="stat-value"><?php echo (int)$stats['open_incidents']; ?></div>
            </div>
        </div>

        <div class="stat-card stat-info">
            <div class="stat-icon">⏳</div>
            <div class="stat-content">
                <div class="stat-label">W trakcie</div>
                <div class="stat-value"><?php echo (int)$stats['in_progress_incidents']; ?></div>
            </div>
        </div>
    </div>

    <!-- Dzisiejsze grafiki -->
    <div class="content-section">
        <div class="section-header">
            <h2>Grafiki na dzisiaj (<?php echo date('d.m.Y'); ?>)</h2>
        </div>

        <?php if (empty($schedules_today)): ?>
            <div class="empty-state">
                <p>Brak grafików na dzisiaj.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Godzina</th>
                            <th>Kierowca</th>
                            <th>Linia</th>
                            <th>Brygada</th>
                            <th>Pojazd</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules_today as $schedule): ?>
                            <tr>
                                <td>
                                    <?php echo date('H:i', strtotime($schedule['start_time'])); ?> - 
                                    <?php echo date('H:i', strtotime($schedule['end_time'])); ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($schedule['first_name'] . ' ' . $schedule['last_name']); ?></strong>
                                    <br><small><?php echo htmlspecialchars($schedule['employee_id']); ?></small>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($schedule['line_number']); ?></strong>
                                    <?php if ($schedule['line_name']): ?>
                                        <br><small><?php echo htmlspecialchars($schedule['line_name']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($schedule['brigade_number']): ?>
                                        <span class="badge badge-info"><?php echo htmlspecialchars($schedule['brigade_number']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($schedule['vehicle_number']); ?></strong>
                                    <br><small><?php echo htmlspecialchars($schedule['model']); ?></small>
                                    <?php if ($schedule['registration_plate']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($schedule['registration_plate']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $status_labels = [
                                        'scheduled' => ['label' => 'Zaplanowany', 'class' => 'badge-info'],
                                        'in_progress' => ['label' => 'W trakcie', 'class' => 'badge-primary'],
                                        'completed' => ['label' => 'Zakończony', 'class' => 'badge-success'],
                                        'cancelled' => ['label' => 'Anulowany', 'class' => 'badge-danger']
                                    ];
                                    $status = $schedule['status'] ?? 'scheduled';
                                    $status_info = $status_labels[$status] ?? ['label' => $status, 'class' => 'badge-secondary'];
                                    ?>
                                    <span class="badge <?php echo $status_info['class']; ?>">
                                        <?php echo htmlspecialchars($status_info['label']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
