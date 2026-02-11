<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>✏️ Edytuj stanowisko</h1>
    <a href="/admin/positions/index.php" class="btn btn-secondary">← Powrót do listy</a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error"><?php echo e($errors['general']); ?></div>
        <?php endif; ?>

        <form method="POST" action="/admin/positions/edit.php?id=<?php echo $position_id; ?>">
            <?php echo csrfField(); ?>

            <div class="form-group">
                <label for="name">Nazwa stanowiska *</label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>"
                       value="<?php echo e($form_data['name'] ?? ''); ?>"
                       required>
                <?php if (isset($errors['name'])): ?>
                    <div class="invalid-feedback"><?php echo e($errors['name']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-row">
                <div class="form-group col col-12 col-md-6">
                    <label for="department_id">Dział</label>
                    <select id="department_id" 
                            name="department_id" 
                            class="form-control">
                        <option value="">-- Wybierz dział --</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>" 
                                    <?php echo ($form_data['department_id'] ?? '') == $dept['id'] ? 'selected' : ''; ?>>
                                <?php echo e($dept['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group col col-12 col-md-6">
                    <label for="max_count">Limit pracowników</label>
                    <input type="number" 
                           id="max_count" 
                           name="max_count" 
                           class="form-control <?php echo isset($errors['max_count']) ? 'is-invalid' : ''; ?>"
                           value="<?php echo e($form_data['max_count'] ?? ''); ?>"
                           min="1"
                           placeholder="Pozostaw puste dla braku limitu">
                    <?php if (isset($errors['max_count'])): ?>
                        <div class="invalid-feedback"><?php echo e($errors['max_count']); ?></div>
                    <?php endif; ?>
                    <small class="form-text text-muted">
                        Pozostaw puste jeśli nie chcesz ustalać limitu pracowników na tym stanowisku.
                    </small>
                </div>
            </div>

            <div class="form-group">
                <label for="description">Opis</label>
                <textarea id="description" 
                          name="description" 
                          class="form-control"
                          rows="4"><?php echo e($form_data['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" 
                           name="active" 
                           <?php echo ($form_data['active'] ?? false) ? 'checked' : ''; ?>>
                    Aktywne
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Zapisz zmiany</button>
                <a href="/admin/positions/index.php" class="btn btn-secondary">Anuluj</a>
            </div>
        </form>
    </div>
</div>

<?php View::partial('layouts/footer'); ?>
