<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>➕ Dodaj brygadę</h1>
    <a href="/admin/brigades/index.php" class="btn btn-secondary">⬅️ Powrót</a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error"><?php echo e($errors['general']); ?></div>
        <?php endif; ?>

        <form method="POST" class="form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

            <div class="form-group">
                <label for="line_id">Linia *</label>
                <select id="line_id" name="line_id" class="form-control" required>
                    <option value="">-- Wybierz linię --</option>
                    <?php foreach ($lines as $line): ?>
                        <option value="<?php echo $line['id']; ?>" <?php echo (isset($form_data['line_id']) && $form_data['line_id'] == $line['id']) ? 'selected' : ''; ?>>
                            <?php echo e($line['line_number'] . ' - ' . $line['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['line_id'])): ?>
                    <div class="form-error"><?php echo e($errors['line_id']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="brigade_number">Numer brygady *</label>
                <input type="text" id="brigade_number" name="brigade_number" class="form-control" 
                       value="<?php echo e($form_data['brigade_number'] ?? ''); ?>" required>
                <?php if (!empty($errors['brigade_number'])): ?>
                    <div class="form-error"><?php echo e($errors['brigade_number']); ?></div>
                <?php endif; ?>
                <small class="form-text">Np. "105/1", "105/02"</small>
            </div>

            <div class="form-group">
                <label for="default_vehicle_type">Domyślny typ taboru</label>
                <select id="default_vehicle_type" name="default_vehicle_type" class="form-control">
                    <option value="">-- Brak preferencji --</option>
                    <option value="bus" <?php echo (isset($form_data['default_vehicle_type']) && $form_data['default_vehicle_type'] === 'bus') ? 'selected' : ''; ?>>Autobus</option>
                    <option value="articulated_bus" <?php echo (isset($form_data['default_vehicle_type']) && $form_data['default_vehicle_type'] === 'articulated_bus') ? 'selected' : ''; ?>>Autobus przegubowy</option>
                    <option value="tram" <?php echo (isset($form_data['default_vehicle_type']) && $form_data['default_vehicle_type'] === 'tram') ? 'selected' : ''; ?>>Tramwaj</option>
                    <option value="metro" <?php echo (isset($form_data['default_vehicle_type']) && $form_data['default_vehicle_type'] === 'metro') ? 'selected' : ''; ?>>Metro</option>
                </select>
            </div>

            <div class="form-group">
                <label for="description">Opis</label>
                <textarea id="description" name="description" class="form-control" rows="3"><?php echo e($form_data['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="active" <?php echo isset($form_data['active']) ? 'checked' : ''; ?>>
                    Aktywna
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Zapisz</button>
                <a href="/admin/brigades/index.php" class="btn btn-secondary">Anuluj</a>
            </div>
        </form>
    </div>
</div>

<?php View::partial('layouts/footer'); ?>
