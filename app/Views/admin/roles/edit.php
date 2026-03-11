<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>✏️ Edytuj role</h1>
    <a href="/admin/roles/index.php" class="btn btn-secondary">← Powrot do listy</a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error"><?php echo e($errors['general']); ?></div>
        <?php endif; ?>

        <form method="POST" action="/admin/roles/edit.php?id=<?php echo $role_id; ?>">
            <?php echo csrfField(); ?>

            <div class="form-group">
                <label for="name" class="form-label">Nazwa roli</label>
                <input type="text" id="name" name="name" class="form-control" value="<?php echo e($form['name']); ?>" required>
                <?php if (!empty($errors['name'])): ?>
                    <div class="form-error"><?php echo e($errors['name']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Opis</label>
                <textarea id="description" name="description" class="form-control" rows="3"><?php echo e($form['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Uprawnienia</label>
                <?php if (!empty($errors['permissions'])): ?>
                    <div class="form-error"><?php echo e($errors['permissions']); ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Moduł</th>
                                <th>Odczyt</th>
                                <th>Tworzenie</th>
                                <th>Edycja</th>
                                <th>Usuwanie</th>
                                <th>Rozwiązanie</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($permission_definition as $resource => $config): ?>
                                <tr>
                                    <td data-label="Moduł"><strong><?php echo e($config['label']); ?></strong></td>
                                    <?php foreach (['read', 'create', 'update', 'delete', 'resolve'] as $action): ?>
                                        <td data-label="<?php echo e($action); ?>">
                                            <?php if (in_array($action, $config['actions'], true)): ?>
                                                <label class="checkbox-label" style="justify-content: center;">
                                                    <input
                                                        type="checkbox"
                                                        name="permissions[<?php echo e($resource); ?>][<?php echo e($action); ?>]"
                                                        value="1"
                                                        <?php echo !empty($selected_permissions[$resource]) && in_array($action, $selected_permissions[$resource], true) ? 'checked' : ''; ?>
                                                    >
                                                </label>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Zapisz</button>
                <a href="/admin/roles/index.php" class="btn btn-secondary">Anuluj</a>
            </div>
        </form>
    </div>
</div>

<?php View::partial('layouts/footer'); ?>
