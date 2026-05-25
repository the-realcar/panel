<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>✏️ Edytuj uzytkownika</h1>
    <a href="/admin/users/index.php" class="btn btn-secondary">← Powrot do listy</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/admin/users/edit.php?id=<?php echo $user_id; ?>">
            <?php echo csrfField(); ?>

            <div class="form-row">
                <div class="form-group col col-12 col-md-6">
                    <label for="username" class="form-label">Login</label>
                    <input type="text" id="username" name="username" class="form-control" value="<?php echo e($form['username']); ?>" required>
                    <?php if (!empty($errors['username'])): ?>
                        <div class="form-error"><?php echo e($errors['username']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group col col-12 col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo e($form['email']); ?>" required>
                    <?php if (!empty($errors['email'])): ?>
                        <div class="form-error"><?php echo e($errors['email']); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col col-12 col-md-6">
                    <label for="first_name" class="form-label">Imie</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" value="<?php echo e($form['first_name']); ?>">
                </div>
                <div class="form-group col col-12 col-md-6">
                    <label for="last_name" class="form-label">Nazwisko</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" value="<?php echo e($form['last_name']); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col col-12 col-md-6">
                    <label for="hired_date" class="form-label">Data zatrudnienia</label>
                    <input type="date" id="hired_date" name="hired_date" class="form-control" value="<?php echo e($form['hired_date']); ?>" required>
                    <?php if (!empty($errors['hired_date'])): ?>
                        <div class="form-error"><?php echo e($errors['hired_date']); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col col-12 col-md-6">
                    <label for="password" class="form-label">Nowe haslo (opcjonalnie)</label>
                    <input type="password" id="password" name="password" class="form-control">
                    <div style="margin-top: 0.5rem; display: flex; gap: 0.5rem;">
                        <button type="button" class="btn btn-sm btn-secondary" id="generate-password-btn">Generuj haslo</button>
                    </div>
                    <?php if (!empty($errors['password'])): ?>
                        <div class="form-error"><?php echo e($errors['password']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group col col-12 col-md-6">
                    <label class="form-label">Status</label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="active" <?php echo $form['active'] ? 'checked' : ''; ?>>
                        Aktywny
                    </label>
                    <label class="checkbox-label" style="margin-top: 0.5rem; display: block;">
                        <input type="checkbox" name="archived" <?php echo $form['archived'] ? 'checked' : ''; ?>>
                        Zarchiwizowany
                    </label>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col col-12 col-md-6">
                    <label for="discord_id" class="form-label">Discord ID</label>
                    <input type="text" id="discord_id" name="discord_id" class="form-control" value="<?php echo e($form['discord_id']); ?>">
                    <?php if (!empty($errors['discord_id'])): ?>
                        <div class="form-error"><?php echo e($errors['discord_id']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="form-group col col-12 col-md-6">
                    <label for="roblox_id" class="form-label">Roblox ID</label>
                    <input type="text" id="roblox_id" name="roblox_id" class="form-control" value="<?php echo e($form['roblox_id']); ?>">
                    <?php if (!empty($errors['roblox_id'])): ?>
                        <div class="form-error"><?php echo e($errors['roblox_id']); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="form-row">
                <div class="form-group col col-12">
                    <label class="form-label">🏢 Przypisz do spółek</label>
                    <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 8px;">Wybierz jedną lub więcej spółek (pracownik może pracować w wielu spółkach na raz)</p>
                    <?php if (!empty($companies)): ?>
                        <div class="checkbox-group" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                            <?php 
                            $user_company_ids = [];
                            foreach ($user_companies as $company) {
                                $user_company_ids[] = $company['id'];
                            }
                            ?>
                            <?php foreach ($companies as $company): ?>
                                <label class="checkbox-label" style="display: flex; align-items: center; gap: 8px;">
                                    <input type="checkbox" name="companies[]" value="<?php echo $company['id']; ?>" <?php echo in_array($company['id'], $user_company_ids) ? 'checked' : ''; ?>>
                                    <span><?php echo e($company['name']); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Brak dostępnych spółek. Admin powinien dodać spółki w systemie.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Zapisz</button>
                <a href="/admin/users/index.php" class="btn btn-secondary">Anuluj</a>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    function generatePassword(length) {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%^&*';
        let result = '';
        const targetLength = length || 14;
        for (let i = 0; i < targetLength; i += 1) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return result;
    }

    const passwordInput = document.getElementById('password');
    const generateButton = document.getElementById('generate-password-btn');

    if (passwordInput && generateButton) {
        generateButton.addEventListener('click', function() {
            passwordInput.value = generatePassword(16);
            passwordInput.type = 'text';
            passwordInput.focus();
        });
    }
})();
</script>

<?php View::partial('layouts/footer'); ?>
