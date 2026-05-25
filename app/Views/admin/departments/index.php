<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<?php $is_edit = !empty($edit_department); ?>

<div class="page-header">
    <h1>🏢 Dzialy</h1>
    <a href="/admin/dashboard.php" class="btn btn-secondary">← Powrót do panelu</a>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?php echo $is_edit ? 'Edytuj dzial' : 'Dodaj dzial'; ?></h2>
    </div>
    <div class="card-body">
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error"><?php echo e($errors['general']); ?></div>
        <?php endif; ?>

        <form method="POST" action="/admin/departments/index.php">
            <?php echo csrfField(); ?>
            <input type="hidden" name="department_action" value="<?php echo $is_edit ? 'update' : 'create'; ?>">
            <?php if ($is_edit): ?>
                <input type="hidden" name="id" value="<?php echo (int)$edit_department['id']; ?>">
            <?php endif; ?>

            <div class="form-row">
                <div class="form-group col col-12 col-md-4">
                    <label for="department-name">Nazwa *</label>
                    <input type="text" id="department-name" name="name" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" value="<?php echo e($edit_department['name'] ?? ''); ?>" required>
                    <?php if (isset($errors['name'])): ?>
                        <div class="invalid-feedback"><?php echo e($errors['name']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group col col-12 col-md-6">
                    <label for="department-description">Opis</label>
                    <input type="text" id="department-description" name="description" class="form-control" value="<?php echo e($edit_department['description'] ?? ''); ?>">
                </div>
                <div class="form-group col col-12 col-md-2" style="display:flex; align-items:end;">
                    <label><input type="checkbox" name="active" value="1" <?php echo !isset($edit_department['active']) || $edit_department['active'] ? 'checked' : ''; ?>> Aktywny</label>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?php echo $is_edit ? '💾 Zapisz zmiany' : '➕ Dodaj dzial'; ?></button>
                <?php if ($is_edit): ?>
                    <a href="/admin/departments/index.php" class="btn btn-secondary">Anuluj</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-between align-center" style="gap:1rem; flex-wrap:wrap;">
        <h2 class="card-title" style="margin:0;">Lista dzialow</h2>
        <input type="text" id="departments-search" class="form-control" placeholder="Szukaj dzialu..." style="max-width:260px;">
    </div>
    <div class="card-body">
        <?php if (empty($departments)): ?>
            <p class="text-muted">Brak dzialow do wyświetlenia.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table" id="departments-table" data-sortable-table data-default-sort="0:asc">
                    <thead>
                        <tr>
                            <th data-sort-type="number">ID</th>
                            <th>Nazwa</th>
                            <th>Opis</th>
                            <th data-sort-type="number">Stanowiska</th>
                            <th>Status</th>
                            <th data-no-sort="true">Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($departments as $department): ?>
                            <tr>
                                <td><?php echo (int)$department['id']; ?></td>
                                <td><strong><?php echo e($department['name']); ?></strong></td>
                                <td><?php echo e($department['description'] ?: '—'); ?></td>
                                <td><?php echo (int)($department['positions_count'] ?? 0); ?></td>
                                <td><?php echo !empty($department['active']) ? '<span class="badge badge-success">Aktywny</span>' : '<span class="badge badge-secondary">Nieaktywny</span>'; ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="/admin/departments/index.php?edit=<?php echo (int)$department['id']; ?>" class="btn btn-sm btn-secondary">✏️ Edytuj</a>
                                        <form method="POST" action="/admin/departments/index.php" style="display:inline;">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="department_action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo (int)$department['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Czy na pewno chcesz usunąć ten dzial?');" <?php echo (int)($department['positions_count'] ?? 0) > 0 ? 'disabled title="Najpierw usuń lub przepnij stanowiska."' : ''; ?>>🗑️ Usuń</button>
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

<script>
(function() {
    const input = document.getElementById('departments-search');
    const rows = Array.from(document.querySelectorAll('#departments-table tbody tr'));
    if (!input || rows.length === 0) {
        return;
    }

    input.addEventListener('input', function() {
        const query = input.value.trim().toLowerCase();
        rows.forEach(function(row) {
            row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
        });
    });
})();
</script>

<?php View::partial('layouts/footer'); ?>