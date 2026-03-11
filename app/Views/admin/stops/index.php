<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>🚏 Zarządzanie przystankami</h1>
    <?php if ($rbac->hasPermission('stops', 'create')): ?>
        <a href="/admin/stops/create.php" class="btn btn-primary">➕ Dodaj przystanek</a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($stops)): ?>
            <p class="text-muted">Brak przystanków do wyświetlenia.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Identyfikator</th>
                            <th>Nazwa</th>
                            <th>Opis</th>
                            <th>Status NZ</th>
                            <th>Status</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stops as $stop): ?>
                        <tr>
                            <td data-label="ID"><?php echo $stop['id']; ?></td>
                            <td data-label="Identyfikator"><strong><?php echo e($stop['stop_id']); ?></strong></td>
                            <td data-label="Nazwa"><?php echo e($stop['name']); ?></td>
                            <td data-label="Opis"><?php echo e($stop['opis'] ?? '-'); ?></td>
                            <td data-label="Status NZ">
                                <?php if (!empty($stop['status_nz'])): ?>
                                    <span class="badge badge-warning">Tak</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Nie</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Status">
                                <?php if ($stop['active']): ?>
                                    <span class="badge badge-success">Aktywny</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Nieaktywny</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Akcje">
                                <div class="btn-group">
                                    <a href="/admin/platforms/index.php?stop_id=<?php echo urlencode($stop['stop_id']); ?>" 
                                       class="btn btn-sm btn-info">🏢 Platformy</a>
                                    <?php if ($rbac->hasPermission('stops', 'update')): ?>
                                        <a href="/admin/stops/edit.php?id=<?php echo $stop['id']; ?>" 
                                           class="btn btn-sm btn-secondary">✏️ Edytuj</a>
                                    <?php endif; ?>
                                    <?php if ($rbac->hasPermission('stops', 'delete')): ?>
                                        <form method="POST" action="/admin/stops/delete.php" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                            <input type="hidden" name="id" value="<?php echo $stop['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Czy na pewno chcesz usunąć ten przystanek?');">🗑️ Usuń</button>
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
                <?php echo pagination($page, $total_pages, '/admin/stops/index.php?'); ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php View::partial('layouts/footer'); ?>
