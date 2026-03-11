<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>📋 Wnioski pracowników</h1>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="form-inline" style="gap: 0.5rem; flex-wrap: wrap;">
            <label for="status">Status:</label>
            <select name="status" id="status" class="form-control" onchange="this.form.submit()">
                <option value="">Wszystkie</option>
                <option value="pending"  <?php echo $status_filter === 'pending'  ? 'selected' : ''; ?>>Oczekujące</option>
                <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Zatwierdzone</option>
                <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Odrzucone</option>
            </select>

            <label for="type">Typ:</label>
            <select name="type" id="type" class="form-control" onchange="this.form.submit()">
                <option value="">Wszystkie typy</option>
                <?php foreach (Application::allTypes() as $t): ?>
                    <option value="<?php echo $t; ?>" <?php echo $type_filter === $t ? 'selected' : ''; ?>>
                        <?php echo e(Application::typeLabel($t)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <div class="card-body">
        <?php if (empty($applications)): ?>
            <p class="text-muted">Brak wniosków do wyświetlenia.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pracownik</th>
                            <th>Typ</th>
                            <th>Status</th>
                            <th>Szczegóły</th>
                            <th>Data złożenia</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $app): ?>
                        <tr>
                            <td data-label="ID"><?php echo $app['id']; ?></td>
                            <td data-label="Pracownik">
                                <strong><?php echo e($app['username']); ?></strong>
                                <?php if (!empty($app['first_name']) || !empty($app['last_name'])): ?>
                                    <br><small><?php echo e(trim($app['first_name'] . ' ' . $app['last_name'])); ?></small>
                                <?php endif; ?>
                            </td>
                            <td data-label="Typ"><?php echo e(Application::typeLabel($app['type'])); ?></td>
                            <td data-label="Status">
                                <span class="badge <?php echo Application::statusBadgeClass($app['status']); ?>">
                                    <?php echo e(Application::statusLabel($app['status'])); ?>
                                </span>
                            </td>
                            <td data-label="Szczegóły">
                                <?php if ($app['execution_date']): ?>
                                    <?php echo e(date('d.m.Y', strtotime($app['execution_date']))); ?>
                                <?php elseif ($app['date_from']): ?>
                                    <?php echo e(date('d.m.Y', strtotime($app['date_from']))); ?> – <?php echo e(date('d.m.Y', strtotime($app['date_to']))); ?>
                                <?php elseif ($app['vehicle_nr']): ?>
                                    Pojazd: <?php echo e($app['vehicle_nr']); ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td data-label="Data złożenia"><?php echo e(date('d.m.Y H:i', strtotime($app['created_at']))); ?></td>
                            <td data-label="Akcje">
                                <a href="/admin/applications/view.php?id=<?php echo $app['id']; ?>" class="btn btn-sm btn-secondary">
                                    🔍 Szczegóły
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <?php
                $base = '/admin/applications/index.php?';
                if ($status_filter) $base .= 'status=' . urlencode($status_filter) . '&';
                if ($type_filter)   $base .= 'type=' . urlencode($type_filter) . '&';
                echo pagination($page, $total_pages, $base);
                ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php View::partial('layouts/footer'); ?>
