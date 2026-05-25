<?php include __DIR__ . '/../layouts/header.php'; ?>

<style>
.dispatcher-dashboard {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.dispatcher-dashboard .dashboard-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}

.dispatcher-dashboard .dashboard-subtitle {
    margin: 0.35rem 0 0;
    color: var(--text-muted);
}

.dispatcher-dashboard .dispatcher-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
}

.dispatcher-dashboard .stat-card {
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    gap: 1rem;
    min-height: 132px;
    margin-bottom: 0;
}

.dispatcher-dashboard .stat-card::after {
    content: '';
    position: absolute;
    inset: auto -24px -24px auto;
    width: 96px;
    height: 96px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.08);
}

.dispatcher-dashboard .stat-icon {
    position: relative;
    z-index: 1;
    width: 56px;
    height: 56px;
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    background: rgba(37, 99, 235, 0.12);
    flex: 0 0 56px;
}

.dispatcher-dashboard .stat-content {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.dispatcher-dashboard .stat-label {
    font-size: 0.95rem;
    color: var(--text-muted);
}

.dispatcher-dashboard .stat-value {
    font-size: 2rem;
    line-height: 1;
    font-weight: 700;
}

.dispatcher-dashboard .stat-success { border-left-color: var(--success); }
.dispatcher-dashboard .stat-success .stat-icon { background: rgba(16, 185, 129, 0.12); }
.dispatcher-dashboard .stat-info { border-left-color: var(--info); }
.dispatcher-dashboard .stat-info .stat-icon { background: rgba(14, 165, 233, 0.12); }
.dispatcher-dashboard .stat-warning { border-left-color: var(--warning); }
.dispatcher-dashboard .stat-warning .stat-icon { background: rgba(245, 158, 11, 0.12); }
.dispatcher-dashboard .stat-danger { border-left-color: var(--danger); }
.dispatcher-dashboard .stat-danger .stat-icon { background: rgba(239, 68, 68, 0.12); }

.dispatcher-dashboard .dispatcher-quick-links {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
}

.dispatcher-dashboard .dispatcher-quick-link {
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
    padding: 1rem 1.1rem;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: linear-gradient(180deg, var(--surface) 0%, var(--surface-alt) 100%);
    color: var(--text);
    box-shadow: var(--shadow-sm);
}

.dispatcher-dashboard .dispatcher-quick-link:hover {
    transform: translateY(-1px);
    box-shadow: var(--shadow);
    color: var(--text);
}

.dispatcher-dashboard .dispatcher-quick-link span {
    color: var(--text-muted);
    font-size: 0.92rem;
}

.dispatcher-dashboard .dispatcher-section {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
    padding: 1.25rem;
}

.dispatcher-dashboard .section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

.dispatcher-dashboard .empty-state {
    border: 1px dashed var(--border);
    border-radius: var(--radius);
    padding: 1.25rem;
    text-align: center;
    color: var(--text-muted);
    background: var(--surface-alt);
}

@media (max-width: 768px) {
    .dispatcher-dashboard .header-actions {
        width: 100%;
        flex-wrap: wrap;
    }

    .dispatcher-dashboard .header-actions .btn {
        flex: 1 1 calc(50% - 0.5rem);
    }

    .dispatcher-dashboard .stat-card {
        min-height: auto;
    }
}
</style>

<div class="dashboard-container dispatcher-dashboard">
    <div class="dashboard-header">
        <div>
            <h1><?php echo htmlspecialchars($page_title); ?></h1>
            <p class="dashboard-subtitle">Biezacy podglad floty, incydentow i grafikow na dzisiaj.</p>
        </div>
        <div class="header-actions">
            <a href="/dispatcher/fleet.php" class="btn btn-secondary">Status Floty</a>
            <a href="/dispatcher/messages.php" class="btn btn-secondary">Dyspozycje</a>
            <a href="/dispatcher/assign-schedule.php" class="btn btn-primary">Zarządzaj Grafikami</a>
        </div>
    </div>

    <div class="stats-grid dispatcher-stats-grid">
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

    <div class="dispatcher-quick-links">
        <a href="/dispatcher/fleet.php?status=sprawny" class="dispatcher-quick-link">
            <strong>Sprawne pojazdy</strong>
            <span>Przejdz do filtrowanego statusu floty</span>
        </a>
        <a href="/dispatcher/messages.php" class="dispatcher-quick-link">
            <strong>Komunikaty dla kierowcow</strong>
            <span>Wyslij dyspozycje pojedynczo albo masowo</span>
        </a>
        <a href="/dispatcher/assign-schedule.php" class="dispatcher-quick-link">
            <strong>Zarzadzaj grafikami</strong>
            <span>Przydziel nowy grafik lub sprawdz obsade</span>
        </a>
    </div>

    <div class="content-section dispatcher-section">
        <div class="section-header">
            <h2>Grafiki na dzisiaj (<?php echo date('d.m.Y'); ?>)</h2>
            <a href="/dispatcher/assign-schedule.php" class="btn btn-outline btn-sm">Nowy przydzial</a>
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
                                    <br><small><?php echo htmlspecialchars($schedule['username']); ?></small>
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
                                    <strong><?php echo htmlspecialchars($schedule['nr_poj']); ?></strong>
                                    <br><small><?php echo htmlspecialchars($schedule['model']); ?></small>
                                    <?php if ($schedule['reg_plate']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($schedule['reg_plate']); ?></small>
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
