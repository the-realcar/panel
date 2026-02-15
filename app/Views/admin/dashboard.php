<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<h1>📊 Panel Administracyjny</h1>

<div class="dashboard-grid">
    <div class="stat-card">
        <div class="stat-card-title">👥 Użytkownicy</div>
        <div class="stat-card-value"><?php echo $stats['total_users'] ?? 0; ?></div>
    </div>

    <div class="stat-card" style="border-left-color: var(--info);">
        <div class="stat-card-title">🚌 Pojazdy</div>
        <div class="stat-card-value"><?php echo $stats['total_vehicles'] ?? 0; ?></div>
    </div>

    <div class="stat-card" style="border-left-color: var(--warning);">
        <div class="stat-card-title">🚏 Linie</div>
        <div class="stat-card-value"><?php echo $stats['total_lines'] ?? 0; ?></div>
    </div>

    <div class="stat-card" style="border-left-color: var(--danger);">
        <div class="stat-card-title">⚠️ Zgłoszenia</div>
        <div class="stat-card-value"><?php echo $stats['total_incidents'] ?? 0; ?></div>
    </div>
</div>

<div class="row">
    <div class="col col-12 col-lg-6">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">🚌 Status pojazdów</h2>
            </div>
            <div class="card-body">
                <div class="stat-list">
                    <div class="stat-item">
                        <span>Dostępne</span>
                        <span class="badge badge-success"><?php echo $stats['available_vehicles'] ?? 0; ?></span>
                    </div>
                    <div class="stat-item">
                        <span>W użyciu</span>
                        <span class="badge badge-primary"><?php echo $stats['in_use_vehicles'] ?? 0; ?></span>
                    </div>
                    <div class="stat-item">
                        <span>W serwisie</span>
                        <span class="badge badge-warning"><?php echo $stats['maintenance_vehicles'] ?? 0; ?></span>
                    </div>
                    <div class="stat-item">
                        <span>Awaria</span>
                        <span class="badge badge-danger"><?php echo $stats['broken_vehicles'] ?? 0; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col col-12 col-lg-6">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">⚠️ Status zgłoszeń</h2>
            </div>
            <div class="card-body">
                <div class="stat-list">
                    <div class="stat-item">
                        <span>Otwarte</span>
                        <span class="badge badge-warning"><?php echo $stats['open_incidents'] ?? 0; ?></span>
                    </div>
                    <div class="stat-item">
                        <span>W trakcie</span>
                        <span class="badge badge-primary"><?php echo $stats['in_progress_incidents'] ?? 0; ?></span>
                    </div>
                    <div class="stat-item">
                        <span>Rozwiązane</span>
                        <span class="badge badge-success"><?php echo $stats['resolved_incidents'] ?? 0; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">🔐 Ostatnie logowania</h2>
    </div>
    <div class="card-body">
        <?php if (empty($recent_logins)): ?>
            <p class="text-muted">Brak logowań.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Data i czas</th>
                            <th>Użytkownik</th>
                            <th>Adres IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_logins as $log): ?>
                        <tr>
                            <td data-label="Data i czas">
                                <?php echo formatDateTime($log['login_time'], 'd.m.Y H:i:s'); ?>
                            </td>
                            <td data-label="Użytkownik">
                                <?php echo e($log['username']); ?>
                                <?php if ($log['first_name'] || $log['last_name']): ?>
                                    <br><small class="text-muted">
                                        <?php echo e(getFullName($log['first_name'], $log['last_name'])); ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td data-label="Adres IP">
                                <?php echo e($log['ip_address'] ?? '-'); ?>
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
                            <th>Zgłaszający</th>
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
                                <?php echo e(truncate($incident['title'], 50)); ?>
                            </td>
                            <td data-label="Zgłaszający">
                                <?php echo e($incident['reporter_name'] ?? 'Nieznany'); ?>
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
</div>

<?php View::partial('layouts/footer'); ?>
