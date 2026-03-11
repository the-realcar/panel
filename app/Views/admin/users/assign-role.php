<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>👤 Przypisz role</h1>
    <a href="/admin/users/index.php" class="btn btn-secondary">← Powrot do listy</a>
</div>

<div class="card">
    <div class="card-header">
        <h3>Uzytkownik: <?php echo e($user['username']); ?></h3>
        <?php if ($user['first_name'] || $user['last_name']): ?>
            <p class="text-muted"><?php echo e(getFullName($user['first_name'], $user['last_name'])); ?></p>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <h4>Przypisz nowa role</h4>

        <?php if (!empty($errors['role'])): ?>
            <div class="alert alert-error"><?php echo e($errors['role']); ?></div>
        <?php endif; ?>

        <?php if (empty($has_roles)): ?>
            <div class="alert alert-warning">
                Brak rol do przypisania. Dodaj role w tabeli roles lub zaladuj dane testowe.
            </div>
        <?php endif; ?>

        <form method="POST" action="/admin/users/assign-role.php?user_id=<?php echo $user_id; ?>">
            <?php echo csrfField(); ?>
            <input type="hidden" name="action" value="assign">

            <div class="form-row">
                <div class="form-group col col-12 col-md-8">
                    <label for="role_id">Rola</label>
                    <select id="role_id"
                            name="role_id"
                            class="form-control"
                            <?php echo empty($has_roles) ? 'disabled' : ''; ?>
                            required>
                        <option value="">-- Wybierz role --</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role['id']; ?>"><?php echo e($role['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group col col-12 col-md-4">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-block" <?php echo empty($has_roles) ? 'disabled' : ''; ?>>➕ Przypisz</button>
                </div>
            </div>
        </form>

        <hr>

        <h4>Aktualne role</h4>

        <?php if (empty($current_roles)): ?>
            <p class="text-muted">Uzytkownik nie ma przypisanych rol.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Rola</th>
                            <th>Opis</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($current_roles as $role): ?>
                        <tr>
                            <td data-label="Rola"><strong><?php echo e($role['name']); ?></strong></td>
                            <td data-label="Opis"><?php echo e($role['description'] ?? '-'); ?></td>
                            <td data-label="Akcje">
                                <form method="POST" action="/admin/users/assign-role.php?user_id=<?php echo $user_id; ?>" style="display:inline;">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="assignment_id" value="<?php echo $role['assignment_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Czy na pewno chcesz usunac te role?');">
                                        🗑️ Usun
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php View::partial('layouts/footer'); ?>
