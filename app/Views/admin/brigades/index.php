<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>🚌 Zarządzanie brygadami</h1>
    <a href="/admin/brigades/schedule.php" class="btn btn-secondary">📋 Plan zmian</a>
    <?php if ($rbac->hasPermission('brigades', 'create')): ?>
        <a href="/admin/brigades/create.php" class="btn btn-primary">➕ Dodaj brygadę</a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="form-inline">
            <label for="line">Filtruj po linii:</label>
            <select name="line" id="line" class="form-control" onchange="this.form.submit()">
                <option value="">Wszystkie</option>
                <?php foreach ($lines as $line): ?>
                    <option value="<?php echo $line['id']; ?>" <?php echo $line_filter == $line['id'] ? 'selected' : ''; ?>>
                        <?php echo e($line['line_number'] . ' - ' . $line['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    <div class="card-body">
        <?php if (empty($brigades)): ?>
            <p class="text-muted">Brak brygad do wyświetlenia.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Brygada</th>
                            <th>Typ brygady</th>
                            <th>Godziny pracy</th>
                            <th>Odjazdy i kierunki</th>
                            <th>Domyślny typ taboru</th>
                            <th>Spółka</th>
                            <th>Status</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($brigades as $brigade): ?>
                        <tr>
                            <td data-label="ID"><?php echo $brigade['id']; ?></td>
                            <td data-label="Brygada"><strong><?php echo e($brigade['line_number'] . '/' . $brigade['brigade_number']); ?></strong></td>
                            <td data-label="Typ brygady">
                                <?php if (!empty($brigade['is_peak'])): ?>
                                    <?php if (($brigade['peak_type'] ?? '') === 'single_shift'): ?>
                                        <span class="badge badge-warning">Brygada jednozmianowa</span>
                                    <?php else: ?>
                                        <span class="badge badge-info">Brygada szczytowa</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">Standardowa</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Godziny pracy">
                                <?php
                                    $shiftA = (!empty($brigade['shift_a_start']) && !empty($brigade['shift_a_end']))
                                        ? 'A: ' . substr($brigade['shift_a_start'], 0, 5) . ' – ' . substr($brigade['shift_a_end'], 0, 5)
                                        : null;
                                    $shiftB = (!empty($brigade['shift_b_start']) && !empty($brigade['shift_b_end']))
                                        ? 'B: ' . substr($brigade['shift_b_start'], 0, 5) . ' – ' . substr($brigade['shift_b_end'], 0, 5)
                                        : null;
                                ?>
                                <?php if ($shiftA || $shiftB): ?>
                                    <?php if ($shiftA): ?><div><?php echo e($shiftA); ?></div><?php endif; ?>
                                    <?php if ($shiftB): ?><div><?php echo e($shiftB); ?></div><?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Odjazdy i kierunki">
                                <?php if (!empty($brigade['departures_summary'])): ?>
                                    <?php echo e($brigade['departures_summary']); ?>
                                    <?php if ((int)($brigade['departures_count'] ?? 0) > 0): ?>
                                        <br><small class="text-muted">Liczba odjazdow: <?php echo (int)$brigade['departures_count']; ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">Brak</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Domyślny typ taboru"><?php echo e($brigade['default_vehicle_type'] ?? '-'); ?></td>
                            <td data-label="Spółka"><?php echo e($brigade['przewoznik'] ?? '—'); ?></td>
                            <td data-label="Status">
                                <?php if ($brigade['active']): ?>
                                    <span class="badge badge-success">Aktywna</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Nieaktywna</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Akcje">
                                <div class="btn-group">
                                    <?php if ($rbac->hasPermission('brigades', 'update')): ?>
                                        <a href="/admin/brigades/edit.php?id=<?php echo $brigade['id']; ?>" 
                                           class="btn btn-sm btn-secondary">✏️ Edytuj</a>
                                    <?php endif; ?>
                                    <?php if ($rbac->hasPermission('brigades', 'delete')): ?>
                                        <form method="POST" action="/admin/brigades/delete.php" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                            <input type="hidden" name="id" value="<?php echo $brigade['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Czy na pewno chcesz usunąć tę brygadę?');">🗑️ Usuń</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <?php echo pagination($page, $total_pages, '/admin/brigades/index.php' . ($line_filter ? '?line=' . urlencode($line_filter) . '&' : '?')); ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php View::partial('layouts/footer'); ?>
