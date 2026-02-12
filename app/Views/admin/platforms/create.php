<?php include __DIR__ . '/../../layouts/header.php'; ?>

<div class="content-container">
    <div class="page-header">
        <h1><?php echo htmlspecialchars($page_title); ?></h1>
        <a href="/admin/platforms/index.php?stop_id=<?php echo urlencode($stop['stop_id']); ?>" class="btn btn-secondary">Anuluj</a>
    </div>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($errors['general']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="form-card">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
        <input type="hidden" name="stop_id" value="<?php echo htmlspecialchars($stop['stop_id']); ?>">

        <div class="form-group">
            <label for="platform_number">Numer platformy: <span class="required">*</span></label>
            <input type="text" 
                   name="platform_number" 
                   id="platform_number" 
                   class="form-control <?php echo isset($errors['platform_number']) ? 'is-invalid' : ''; ?>"
                   value="<?php echo htmlspecialchars($form_data['platform_number'] ?? ''); ?>"
                   placeholder="Np. '01', '02', 'A', 'B'"
                   maxlength="10"
                   required>
            <?php if (isset($errors['platform_number'])): ?>
                <div class="invalid-feedback"><?php echo htmlspecialchars($errors['platform_number']); ?></div>
            <?php endif; ?>
            <small class="form-text text-muted">Unikalny numer platformy na tym przystanku (np. '01', '02', 'A', 'B').</small>
        </div>

        <div class="form-group">
            <label for="description">Opis:</label>
            <textarea name="description" 
                      id="description" 
                      class="form-control" 
                      rows="3"><?php echo htmlspecialchars($form_data['description'] ?? ''); ?></textarea>
            <small class="form-text text-muted">Dodatkowe informacje o platformie (np. kierunek, typ).</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Dodaj platformę</button>
            <a href="/admin/platforms/index.php?stop_id=<?php echo urlencode($stop['stop_id']); ?>" class="btn btn-secondary">Anuluj</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
