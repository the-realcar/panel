<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>➕ Dodaj uzytkownika</h1>
    <a href="/admin/users/index.php" class="btn btn-secondary">← Powrot do listy</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/admin/users/create.php">
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
                    <label for="password" class="form-label">Haslo</label>
                    <input type="password" id="password" name="password" class="form-control" required>
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

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Zapisz</button>
                <a href="/admin/users/index.php" class="btn btn-secondary">Anuluj</a>
            </div>
        </form>
    </div>
</div>

<?php View::partial('layouts/footer'); ?>
