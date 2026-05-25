<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<?php $is_edit = $editing_value !== ''; ?>

<div class="page-header">
    <h1><?php echo e($config['title']); ?></h1>
    <a href="/admin/dashboard.php" class="btn btn-secondary">← Powrót do panelu</a>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?php echo $is_edit ? 'Edytuj' : 'Dodaj'; ?> <?php echo e($config['singular']); ?></h2>
    </div>
    <div class="card-body">
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error"><?php echo e($errors['general']); ?></div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e($config['route']); ?>" class="form-inline" style="gap: 0.75rem; flex-wrap: wrap; align-items: flex-start;">
            <?php echo csrfField(); ?>
            <input type="hidden" name="dictionary_action" value="<?php echo $is_edit ? 'update' : 'create'; ?>">
            <input type="hidden" name="original_value" value="<?php echo e($editing_value); ?>">
            <div class="form-group" style="min-width: 280px;">
                <label for="dictionary-value"><?php echo e(ucfirst($config['singular'])); ?></label>
                <input type="text" id="dictionary-value" name="value" class="form-control <?php echo isset($errors['value']) ? 'is-invalid' : ''; ?>" value="<?php echo e($editing_value); ?>" required>
                <?php if (isset($errors['value'])): ?>
                    <div class="invalid-feedback" style="display:block;"><?php echo e($errors['value']); ?></div>
                <?php endif; ?>
            </div>
            <div class="form-group" style="padding-top: 1.85rem;">
                <button type="submit" class="btn btn-primary"><?php echo $is_edit ? '💾 Zapisz' : '➕ Dodaj'; ?></button>
                <?php if ($is_edit): ?>
                    <a href="<?php echo e($config['route']); ?>" class="btn btn-secondary">Anuluj</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-between align-center" style="gap: 1rem; flex-wrap: wrap;">
        <h2 class="card-title" style="margin:0;">Lista</h2>
        <input type="text" id="dictionary-search" class="form-control" placeholder="Szukaj..." style="max-width: 260px;">
    </div>
    <div class="card-body">
        <?php if (empty($items)): ?>
            <p class="text-muted">Brak wartości w słowniku.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table" id="dictionary-table" data-sortable-table data-default-sort="0:asc">
                    <thead>
                        <tr>
                            <th data-sort-type="number">ID</th>
                            <th>Wartość</th>
                            <th data-no-sort="true">Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo (int)$item['id']; ?></td>
                                <td><?php echo e($item['value']); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="<?php echo e($config['route']); ?>?edit=<?php echo urlencode($item['value']); ?>" class="btn btn-sm btn-secondary">✏️ Edytuj</a>
                                        <form method="POST" action="<?php echo e($config['route']); ?>" style="display:inline;">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="dictionary_action" value="delete">
                                            <input type="hidden" name="original_value" value="<?php echo e($item['value']); ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Czy na pewno chcesz usunąć tę wartość?');">🗑️ Usuń</button>
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
    const input = document.getElementById('dictionary-search');
    const rows = Array.from(document.querySelectorAll('#dictionary-table tbody tr'));
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