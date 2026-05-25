<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>⚙️ Ustawienia konta</h1>
    <a href="/index.php" class="btn btn-secondary">← Powrot</a>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Zmiana hasla</h2>
    </div>
    <div class="card-body">
        <p class="text-muted" style="margin-bottom: 1rem;">Konto: <strong><?php echo e($user['username']); ?></strong></p>

        <form method="POST" action="/settings.php">
            <?php echo csrfField(); ?>

            <div class="form-row">
                <div class="form-group col col-12 col-md-6">
                    <label for="current_password" class="form-label">Aktualne haslo</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                    <?php if (!empty($errors['current_password'])): ?>
                        <div class="form-error"><?php echo e($errors['current_password']); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col col-12 col-md-6">
                    <label for="new_password" class="form-label">Nowe haslo</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" required>
                    <?php if (!empty($errors['new_password'])): ?>
                        <div class="form-error"><?php echo e($errors['new_password']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group col col-12 col-md-6">
                    <label for="confirm_password" class="form-label">Potwierdz haslo</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    <?php if (!empty($errors['confirm_password'])): ?>
                        <div class="form-error"><?php echo e($errors['confirm_password']); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" id="generate-password-btn">Generuj haslo</button>
                <button type="submit" class="btn btn-primary">Zmien haslo</button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    function generatePassword(length) {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%^&*';
        let result = '';
        const targetLength = length || 16;
        for (let i = 0; i < targetLength; i += 1) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return result;
    }

    const newPasswordInput = document.getElementById('new_password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const generateButton = document.getElementById('generate-password-btn');

    if (!newPasswordInput || !confirmPasswordInput || !generateButton) {
        return;
    }

    generateButton.addEventListener('click', function() {
        const generated = generatePassword(16);
        newPasswordInput.value = generated;
        confirmPasswordInput.value = generated;
        newPasswordInput.type = 'text';
        confirmPasswordInput.type = 'text';
        newPasswordInput.focus();
    });
})();
</script>

<?php View::partial('layouts/footer'); ?>
