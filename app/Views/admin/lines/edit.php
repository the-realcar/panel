<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>✏️ Edytuj linię</h1>
    <a href="/admin/lines/index.php" class="btn btn-secondary">← Powrót do listy</a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error"><?php echo e($errors['general']); ?></div>
        <?php endif; ?>

        <form method="POST" action="/admin/lines/edit.php?id=<?php echo $line_id; ?>">
            <?php echo csrfField(); ?>

            <div class="form-row">
                <div class="form-group col col-12 col-md-6">
                    <label for="line_number">Numer linii *</label>
                    <input type="text" 
                           id="line_number" 
                           name="line_number" 
                           class="form-control <?php echo isset($errors['line_number']) ? 'is-invalid' : ''; ?>"
                           value="<?php echo e($form_data['line_number'] ?? ''); ?>"
                           required>
                    <?php if (isset($errors['line_number'])): ?>
                        <div class="invalid-feedback"><?php echo e($errors['line_number']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group col col-12 col-md-6">
                    <label for="name">Nazwa *</label>
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
            </div>

            <div class="form-group">
                <label for="line_type">Typ linii *</label>
                <select id="line_type" 
                        name="line_type" 
                        class="form-control <?php echo isset($errors['line_type']) ? 'is-invalid' : ''; ?>"
                        required>
                    <option value="">-- Wybierz typ --</option>
                    <option value="bus" <?php echo ($form_data['line_type'] ?? '') === 'bus' ? 'selected' : ''; ?>>Autobus</option>
                    <option value="tram" <?php echo ($form_data['line_type'] ?? '') === 'tram' ? 'selected' : ''; ?>>Tramwaj</option>
                    <option value="metro" <?php echo ($form_data['line_type'] ?? '') === 'metro' ? 'selected' : ''; ?>>Metro</option>
                </select>
                <?php if (isset($errors['line_type'])): ?>
                    <div class="invalid-feedback"><?php echo e($errors['line_type']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="route_description">Opis trasy</label>
                <textarea id="route_description" 
                          name="route_description" 
                          class="form-control"
                          rows="4"><?php echo e($form_data['route_description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" 
                           name="active" 
                           <?php echo ($form_data['active'] ?? false) ? 'checked' : ''; ?>>
                    Aktywna
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Zapisz zmiany</button>
                <a href="/admin/lines/index.php" class="btn btn-secondary">Anuluj</a>
            </div>
        </form>
    </div>
</div>

<?php View::partial('layouts/footer'); ?>
