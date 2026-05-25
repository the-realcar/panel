<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>🔐 Zarzadzanie rolami</h1>
    <a href="/admin/roles/create.php" class="btn btn-primary">➕ Dodaj role</a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($roles)): ?>
            <p class="text-muted">Brak rol do wyswietlenia.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nazwa</th>
                            <th>Opis</th>
                            <th>Uprawnienia</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roles as $role): ?>
                        <tr>
                            <td data-label="Nazwa"><strong><?php echo e($role['name']); ?></strong></td>
                            <td data-label="Opis"><?php echo e($role['description'] ?? '-'); ?></td>
                            <td data-label="Uprawnienia"><small><?php echo e(truncate(json_encode(json_decode($role['permissions'] ?? '{}', true) ?: [], JSON_UNESCAPED_UNICODE), 120)); ?></small></td>
                            <td data-label="Akcje">
                                <div class="btn-group">
                                    <a href="/admin/roles/edit.php?id=<?php echo (int)$role['id']; ?>" class="btn btn-sm btn-primary">✏️ Edytuj</a>
                                    <form method="POST" action="/admin/roles/delete.php" style="display:inline;">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="id" value="<?php echo (int)$role['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Czy na pewno chcesz usunac te role?');">🗑️ Usun</button>
                                    </form>
                                </div>
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
