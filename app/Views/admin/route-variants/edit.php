<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>✏️ Edytuj wariant trasy</h1>
    <a href="/admin/route-variants/index.php" class="btn btn-secondary">← Powrot</a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error"><?php echo e($errors['general']); ?></div>
        <?php endif; ?>

        <form method="POST" action="/admin/route-variants/edit.php?id=<?php echo (int)$variant_id; ?>">
            <?php echo csrfField(); ?>

            <div class="form-group">
                <label for="line_id" class="form-label">Linia</label>
                <select id="line_id" name="line_id" class="form-control" required>
                    <?php foreach ($lines as $line): ?>
                        <option value="<?php echo (int)$line['id']; ?>" <?php echo ((int)($form_data['line_id'] ?? 0) === (int)$line['id']) ? 'selected' : ''; ?>>
                            <?php echo e($line['line_number']); ?> - <?php echo e($line['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['line_id'])): ?><div class="form-error"><?php echo e($errors['line_id']); ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="variant_name" class="form-label">Nazwa wariantu</label>
                <input type="text" id="variant_name" name="variant_name" class="form-control" value="<?php echo e($form_data['variant_name'] ?? ''); ?>" required>
                <?php if (!empty($errors['variant_name'])): ?><div class="form-error"><?php echo e($errors['variant_name']); ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="variant_type" class="form-label">Typ wariantu</label>
                <select id="variant_type" name="variant_type" class="form-control" required>
                    <?php $types = ['normal' => 'Normalny', 'short' => 'Skrocony', 'depot_entry' => 'Zjazd do zajezdni', 'depot_exit' => 'Wyjazd z zajezdni']; ?>
                    <?php foreach ($types as $value => $label): ?>
                        <option value="<?php echo e($value); ?>" <?php echo (($form_data['variant_type'] ?? 'normal') === $value) ? 'selected' : ''; ?>><?php echo e($label); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['variant_type'])): ?><div class="form-error"><?php echo e($errors['variant_type']); ?></div><?php endif; ?>
            </div>

            <div class="form-group">
                <label for="direction" class="form-label">Kierunek</label>
                <input type="text" id="direction" name="direction" class="form-control" value="<?php echo e($form_data['direction'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_active" <?php echo !empty($form_data['is_active']) ? 'checked' : ''; ?>>
                    Aktywny wariant
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
                <a href="/admin/route-variants/index.php" class="btn btn-secondary">Anuluj</a>
            </div>
        </form>
    </div>
</div>

<?php View::partial('layouts/footer'); ?>
