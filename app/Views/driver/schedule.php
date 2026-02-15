<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

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
                                <?php if ($schedule['nr_poj']): ?>
                                    <strong><?php echo e($schedule['nr_poj']); ?></strong>
                                    <?php if ($schedule['reg_plate']): ?>
                                        <br><small class="text-muted"><?php echo e($schedule['reg_plate']); ?></small>
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
        <a href="/driver/dashboard.php" class="btn btn-secondary">
            &larr; Powrót do panelu
        </a>
    </div>
</div>

<?php View::partial('layouts/footer'); ?>
