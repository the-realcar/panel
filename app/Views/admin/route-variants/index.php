<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>🧭 Warianty tras</h1>
    <?php if ($rbac->hasPermission('route_variants', 'create')): ?>
        <a href="/admin/route-variants/create.php" class="btn btn-primary">➕ Dodaj wariant</a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="form-inline">
            <label for="line_id">Linia:</label>
            <select id="line_id" name="line_id" class="form-control">
                <option value="">Wszystkie</option>
                <?php foreach ($lines as $line): ?>
                    <option value="<?php echo (int)$line['id']; ?>" <?php echo $line_filter !== null && (int)$line_filter === (int)$line['id'] ? 'selected' : ''; ?>>
                        <?php echo e($line['line_number']); ?> - <?php echo e($line['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label class="checkbox-label" style="margin-left: 1rem;">
                <input type="checkbox" name="active" value="1" <?php echo $active_only ? 'checked' : ''; ?>>
                Tylko aktywne
            </label>

            <button type="submit" class="btn btn-secondary">Filtruj</button>
        </form>
    </div>

    <div class="card-body">
        <?php if (empty($variants)): ?>
            <p class="text-muted">Brak wariantow tras do wyswietlenia.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Linia</th>
                            <th>Nazwa wariantu</th>
                            <th>Typ</th>
                            <th>Kierunek</th>
                            <th>Przystanki</th>
                            <th>Status</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($variants as $variant): ?>
                            <tr>
                                <td data-label="ID"><?php echo (int)$variant['id']; ?></td>
                                <td data-label="Linia">
                                    <strong><?php echo e($variant['line_number']); ?></strong><br>
                                    <small class="text-muted"><?php echo e($variant['line_name']); ?></small>
                                </td>
                                <td data-label="Nazwa wariantu"><?php echo e($variant['variant_name']); ?></td>
                                <td data-label="Typ"><?php echo e($variant['variant_type']); ?></td>
                                <td data-label="Kierunek"><?php echo e($variant['direction'] ?? '-'); ?></td>
                                <td data-label="Przystanki"><?php echo (int)$variant['stops_count']; ?></td>
                                <td data-label="Status">
                                    <?php if (!empty($variant['is_active'])): ?>
                                        <span class="badge badge-success">Aktywny</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Nieaktywny</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Akcje">
                                    <div class="btn-group">
                                        <a href="/admin/route-variants/stops.php?id=<?php echo (int)$variant['id']; ?>" class="btn btn-sm btn-primary">🧱 Builder</a>
                                        <?php if ($rbac->hasPermission('route_variants', 'update')): ?>
                                            <a href="/admin/route-variants/edit.php?id=<?php echo (int)$variant['id']; ?>" class="btn btn-sm btn-secondary">✏️ Edytuj</a>
                                        <?php endif; ?>
                                        <?php if ($rbac->hasPermission('route_variants', 'delete')): ?>
                                            <form method="POST" action="/admin/route-variants/delete.php" style="display:inline;">
                                                <?php echo csrfField(); ?>
                                                <input type="hidden" name="id" value="<?php echo (int)$variant['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Czy na pewno usunac wariant trasy?');">🗑️ Usun</button>
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
                <?php echo pagination($page, $total_pages, '/admin/route-variants/index.php?'); ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php View::partial('layouts/footer'); ?>
