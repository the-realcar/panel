<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>➕ Dodaj przystanek</h1>
    <a href="/admin/stops/index.php" class="btn btn-secondary">⬅️ Powrót</a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error"><?php echo e($errors['general']); ?></div>
        <?php endif; ?>

        <form method="POST" class="form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

            <div class="form-group">
                <label for="stop_id">Identyfikator przystanku *</label>
                <input type="text" id="stop_id" name="stop_id" class="form-control" 
                       value="<?php echo e($form_data['stop_id'] ?? ''); ?>" required>
                <?php if (!empty($errors['stop_id'])): ?>
                    <div class="form-error"><?php echo e($errors['stop_id']); ?></div>
                <?php endif; ?>
                <small class="form-text">Unikalny identyfikator zgodny z SIL (np. "P001")</small>
            </div>

            <div class="form-group">
                <label for="name">Nazwa przystanku *</label>
                <input type="text" id="name" name="name" class="form-control" 
                       value="<?php echo e($form_data['name'] ?? ''); ?>" required>
                <?php if (!empty($errors['name'])): ?>
                    <div class="form-error"><?php echo e($errors['name']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="location_description">Opis lokalizacji</label>
                <textarea id="location_description" name="location_description" class="form-control" rows="3"><?php echo e($form_data['location_description'] ?? ''); ?></textarea>
                <small class="form-text">Np. "Przy dworcu PKP", "Obok centrum handlowego"</small>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="latitude">Szerokość geograficzna</label>
                    <input type="text" id="latitude" name="latitude" class="form-control" 
                           value="<?php echo e($form_data['latitude'] ?? ''); ?>" placeholder="52.229676">
                    <?php if (!empty($errors['latitude'])): ?>
                        <div class="form-error"><?php echo e($errors['latitude']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="longitude">Długość geograficzna</label>
                    <input type="text" id="longitude" name="longitude" class="form-control" 
                           value="<?php echo e($form_data['longitude'] ?? ''); ?>" placeholder="21.012229">
                    <?php if (!empty($errors['longitude'])): ?>
                        <div class="form-error"><?php echo e($errors['longitude']); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="active" <?php echo isset($form_data['active']) ? 'checked' : ''; ?>>
                    Aktywny
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Zapisz</button>
                <a href="/admin/stops/index.php" class="btn btn-secondary">Anuluj</a>
            </div>
        </form>
    </div>
</div>

<?php View::partial('layouts/footer'); ?>
