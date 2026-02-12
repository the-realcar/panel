<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>🚏 Zarządzanie liniami</h1>
    <?php if ($rbac->hasPermission('lines', 'create')): ?>
        <a href="/admin/lines/create.php" class="btn btn-primary">➕ Dodaj linię</a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="form-inline">
            <label for="type">Filtruj po typie:</label>
            <select name="type" id="type" class="form-control" onchange="this.form.submit()">
                <option value="">Wszystkie</option>
                <option value="bus" <?php echo $type_filter === 'bus' ? 'selected' : ''; ?>>Autobus</option>
                <option value="tram" <?php echo $type_filter === 'tram' ? 'selected' : ''; ?>>Tramwaj</option>
                <option value="metro" <?php echo $type_filter === 'metro' ? 'selected' : ''; ?>>Metro</option>
            </select>
        </form>
    </div>
    <div class="card-body">
        <?php if (empty($lines)): ?>
            <p class="text-muted">Brak linii do wyświetlenia.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Numer linii</th>
                            <th>Nazwa</th>
                            <th>Typ</th>
                            <th>Status</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lines as $line): ?>
                        <tr>
                            <td data-label="ID"><?php echo $line['id']; ?></td>
                            <td data-label="Numer linii"><strong><?php echo e($line['line_number']); ?></strong></td>
                            <td data-label="Nazwa"><?php echo e($line['name']); ?></td>
                            <td data-label="Typ"><?php echo e($line['line_type']); ?></td>
                            <td data-label="Status">
                                <?php if ($line['active']): ?>
                                    <span class="badge badge-success">Aktywna</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Nieaktywna</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Akcje">
                                <div class="btn-group">
                                    <?php if ($rbac->hasPermission('lines', 'update')): ?>
                                        <a href="/admin/lines/edit.php?id=<?php echo $line['id']; ?>" 
                                           class="btn btn-sm btn-secondary">✏️ Edytuj</a>
                                    <?php endif; ?>
                                    <?php if ($rbac->hasPermission('lines', 'delete')): ?>
                                        <form method="POST" action="/admin/lines/delete.php" style="display:inline;">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="id" value="<?php echo $line['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Czy na pewno chcesz usunąć tę linię?');">
                                                🗑️ Usuń
                                            </button>
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
                <?php echo pagination($page, $total_pages, '/admin/lines/index.php' . ($type_filter ? '?type=' . urlencode($type_filter) . '&' : '?')); ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php View::partial('layouts/footer'); ?>
