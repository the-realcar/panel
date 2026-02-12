<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>🚌 Zarządzanie brygadami</h1>
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
                            <th>Linia</th>
                            <th>Numer brygady</th>
                            <th>Domyślny typ taboru</th>
                            <th>Status</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($brigades as $brigade): ?>
                        <tr>
                            <td data-label="ID"><?php echo $brigade['id']; ?></td>
                            <td data-label="Linia"><?php echo e($brigade['line_number'] . ' - ' . $brigade['line_name']); ?></td>
                            <td data-label="Numer brygady"><strong><?php echo e($brigade['brigade_number']); ?></strong></td>
                            <td data-label="Domyślny typ taboru"><?php echo e($brigade['default_vehicle_type'] ?? '-'); ?></td>
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
