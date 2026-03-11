<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>⚠️ Zarzadzanie zgloszeniami</h1>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="form-inline">
            <label for="status">Filtruj po statusie:</label>
            <select name="status" id="status" class="form-control" onchange="this.form.submit()">
                <option value="">Wszystkie</option>
                <option value="open" <?php echo $status_filter === 'open' ? 'selected' : ''; ?>>Otwarte</option>
                <option value="in_progress" <?php echo $status_filter === 'in_progress' ? 'selected' : ''; ?>>W trakcie</option>
                <option value="resolved" <?php echo $status_filter === 'resolved' ? 'selected' : ''; ?>>Rozwiazane</option>
                <option value="closed" <?php echo $status_filter === 'closed' ? 'selected' : ''; ?>>Zamkniete</option>
            </select>
        </form>
    </div>
    <div class="card-body">
        <?php if (empty($incidents)): ?>
            <p class="text-muted">Brak zgloszen do wyswietlenia.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Data</th>
                            <th>Typ</th>
                            <th>Priorytet</th>
                            <th>Tytul</th>
                            <th>Pojazd</th>
                            <th>Zglaszajacy</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($incidents as $incident): ?>
                        <tr>
                            <td data-label="ID"><?php echo (int)$incident['id']; ?></td>
                            <td data-label="Data"><?php echo formatDateTime($incident['incident_date'], 'd.m.Y H:i'); ?></td>
                            <td data-label="Typ"><?php echo e($incident['incident_type']); ?></td>
                            <td data-label="Priorytet"><?php echo getSeverityBadge($incident['severity']); ?></td>
                            <td data-label="Tytul"><?php echo e(truncate($incident['title'], 60)); ?></td>
                            <td data-label="Pojazd"><?php echo e($incident['nr_poj'] ?? '-'); ?></td>
                            <td data-label="Zglaszajacy"><?php echo e($incident['reporter_name'] ?? 'Nieznany'); ?></td>
                            <td data-label="Status"><?php echo getStatusBadge($incident['status']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <?php echo pagination($page, $total_pages, '/admin/incidents/index.php' . ($status_filter ? '?status=' . urlencode($status_filter) . '&' : '?')); ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php View::partial('layouts/footer'); ?>
