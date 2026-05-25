<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<?php $is_edit = !empty($edit_district); ?>

<div class="page-header">
    <h1>🌆 Dzielnice</h1>
    <a href="/admin/dashboard.php" class="btn btn-secondary">← Powrót do panelu</a>
</div>

<?php if (!$districts_available): ?>
    <div class="alert alert-warning">Tabela districts nie jest dostępna. Uruchom migrację SQL z finalnego skryptu WebSSH, a następnie odśwież tę stronę.</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?php echo $is_edit ? 'Edytuj dzielnicę' : 'Dodaj dzielnicę'; ?></h2>
    </div>
    <div class="card-body">
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error"><?php echo e($errors['general']); ?></div>
        <?php endif; ?>

        <form method="POST" action="/admin/districts/index.php">
            <?php echo csrfField(); ?>
            <input type="hidden" name="district_action" value="<?php echo $is_edit ? 'update' : 'create'; ?>">
            <?php if ($is_edit): ?>
                <input type="hidden" name="id" value="<?php echo (int)$edit_district['id']; ?>">
            <?php endif; ?>

            <div class="form-row">
                <div class="form-group col col-12 col-md-4">
                    <label for="district-name">Nazwa *</label>
                    <input type="text" id="district-name" name="name" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" value="<?php echo e($edit_district['name'] ?? ''); ?>" required <?php echo !$districts_available ? 'disabled' : ''; ?>>
                    <?php if (isset($errors['name'])): ?>
                        <div class="invalid-feedback"><?php echo e($errors['name']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group col col-12 col-md-4">
                    <label for="district-city">Miasto *</label>
                    <select id="district-city" name="city_id" class="form-control <?php echo isset($errors['city_id']) ? 'is-invalid' : ''; ?>" <?php echo !$districts_available ? 'disabled' : ''; ?>>
                        <option value="">-- Wybierz miasto --</option>
                        <?php foreach ($cities as $city): ?>
                            <option value="<?php echo (int)$city['id']; ?>" <?php echo (int)($edit_district['city_id'] ?? 0) === (int)$city['id'] ? 'selected' : ''; ?>><?php echo e($city['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['city_id'])): ?>
                        <div class="invalid-feedback"><?php echo e($errors['city_id']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group col col-12 col-md-4" style="display:flex; align-items:end;">
                    <label><input type="checkbox" name="active" value="1" <?php echo !isset($edit_district['active']) || $edit_district['active'] ? 'checked' : ''; ?> <?php echo !$districts_available ? 'disabled' : ''; ?>> Aktywna</label>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary" <?php echo !$districts_available ? 'disabled' : ''; ?>><?php echo $is_edit ? '💾 Zapisz zmiany' : '➕ Dodaj dzielnicę'; ?></button>
                <?php if ($is_edit): ?>
                    <a href="/admin/districts/index.php" class="btn btn-secondary">Anuluj</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-between align-center" style="gap:1rem; flex-wrap:wrap;">
        <h2 class="card-title" style="margin:0;">Lista dzielnic</h2>
        <input type="text" id="districts-search" class="form-control" placeholder="Szukaj dzielnicy..." style="max-width:260px;">
    </div>
    <div class="card-body">
        <?php if (empty($districts)): ?>
            <p class="text-muted">Brak dzielnic do wyświetlenia.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table" id="districts-table" data-sortable-table data-default-sort="0:asc">
                    <thead>
                        <tr>
                            <th data-sort-type="number">ID</th>
                            <th>Nazwa</th>
                            <th>Miasto</th>
                            <th>Status</th>
                            <th data-no-sort="true">Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($districts as $district): ?>
                            <tr>
                                <td><?php echo (int)$district['id']; ?></td>
                                <td><strong><?php echo e($district['name']); ?></strong></td>
                                <td><?php echo e($district['city_name']); ?></td>
                                <td><?php echo !empty($district['active']) ? '<span class="badge badge-success">Aktywna</span>' : '<span class="badge badge-secondary">Nieaktywna</span>'; ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="/admin/districts/index.php?edit=<?php echo (int)$district['id']; ?>" class="btn btn-sm btn-secondary">✏️ Edytuj</a>
                                        <form method="POST" action="/admin/districts/index.php" style="display:inline;">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="district_action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo (int)$district['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Czy na pewno chcesz usunąć tę dzielnicę?');">🗑️ Usuń</button>
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
    const input = document.getElementById('districts-search');
    const rows = Array.from(document.querySelectorAll('#districts-table tbody tr'));
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