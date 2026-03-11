<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>📅 Przegląd Grafików</h1>
    <a href="/dispatcher/assign-schedule.php" class="btn btn-primary">➕ Przydziel grafik</a>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="form-inline" style="gap:1rem; flex-wrap:wrap;">
            <div class="form-group" style="margin-bottom:0;">
                <label for="date_from">Od:</label>
                <input type="date" name="date_from" id="date_from" class="form-control"
                       value="<?php echo e($date_from); ?>">
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label for="date_to">Do:</label>
                <input type="date" name="date_to" id="date_to" class="form-control"
                       value="<?php echo e($date_to); ?>">
            </div>
            <button type="submit" class="btn btn-secondary">🔍 Filtruj</button>
        </form>
    </div>
    <div class="card-body">
        <?php if (empty($schedules)): ?>
            <p class="text-muted">Brak grafików do wyświetlenia w wybranym okresie.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kierowca</th>
                            <th>Data</th>
                            <th>Godziny</th>
                            <th>Linia</th>
                            <th>Brygada</th>
                            <th>Pojazd</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schedules as $s): ?>
                        <tr>
                            <td><?php echo (int)$s['id']; ?></td>
                            <td>
                                <strong><?php echo e(trim(($s['first_name'] ?? '') . ' ' . ($s['last_name'] ?? '')) ?: $s['username']); ?></strong>
                            </td>
                            <td><?php echo e(date('d.m.Y', strtotime($s['schedule_date']))); ?></td>
                            <td><?php echo e(substr($s['start_time'], 0, 5) . ' – ' . substr($s['end_time'], 0, 5)); ?></td>
                            <td>
                                <?php if ($s['line_number']): ?>
                                    <strong><?php echo e($s['line_number']); ?></strong>
                                    <?php echo $s['line_name'] ? ' ' . e($s['line_name']) : ''; ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td><?php echo $s['brigade_number'] ? e($s['brigade_number']) : '—'; ?></td>
                            <td>
                                <?php if ($s['nr_poj']): ?>
                                    <?php echo e($s['nr_poj']); ?>
                                    <?php echo $s['model'] ? ' (' . e($s['model']) . ')' : ''; ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td><?php echo getStatusBadge($s['status']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <?php
                $base = '/dispatcher/schedules.php?date_from=' . urlencode($date_from) . '&date_to=' . urlencode($date_to) . '&';
                echo pagination($page, $total_pages, $base);
                ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php View::partial('layouts/footer'); ?>
