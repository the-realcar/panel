<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>📋 Przypisz stanowisko</h1>
    <a href="/admin/users/index.php" class="btn btn-secondary">← Powrót do listy</a>
</div>

<div class="card">
    <div class="card-header">
        <h3>Użytkownik: <?php echo e($user['username']); ?></h3>
        <?php if ($user['first_name'] || $user['last_name']): ?>
            <p class="text-muted"><?php echo e(getFullName($user['first_name'], $user['last_name'])); ?></p>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <h4>Przypisz nowe stanowisko</h4>

        <?php if (!empty($errors['position'])): ?>
            <div class="alert alert-error"><?php echo e($errors['position']); ?></div>
        <?php endif; ?>

        <?php if (empty($has_positions)): ?>
            <div class="alert alert-warning">
                Brak aktywnych stanowisk do przypisania. Najpierw dodaj stanowisko w module
                <a href="/admin/positions/create.php">Stanowiska</a>.
            </div>
        <?php endif; ?>

        <form method="POST" action="/admin/users/assign-position.php?user_id=<?php echo $user_id; ?>">
            <?php echo csrfField(); ?>
            <input type="hidden" name="action" value="assign">

            <div class="form-row">
                <div class="form-group col col-12 col-md-8">
                    <label for="position_id">Stanowisko</label>
                    <select id="position_id" 
                            name="position_id" 
                            class="form-control"
                            <?php echo empty($has_positions) ? 'disabled' : ''; ?>
                            required>
                        <option value="">-- Wybierz stanowisko --</option>
                        <?php foreach ($positions as $position): ?>
                            <option value="<?php echo $position['id']; ?>">
                                <?php echo e($position['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group col col-12 col-md-4">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-block" <?php echo empty($has_positions) ? 'disabled' : ''; ?>>➕ Przypisz</button>
                </div>
            </div>
        </form>

        <hr>

        <h4>Aktualne stanowiska</h4>

        <?php if (empty($current_positions)): ?>
            <p class="text-muted">Użytkownik nie ma przypisanych stanowisk.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Stanowisko</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($current_positions as $position): ?>
                        <tr>
                            <td data-label="Stanowisko">
                                <strong><?php echo e($position['name']); ?></strong>
                            </td>
                            <td data-label="Akcje">
                                <form method="POST" action="/admin/users/assign-position.php?user_id=<?php echo $user_id; ?>" style="display:inline;">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="assignment_id" value="<?php echo $position['assignment_id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Czy na pewno chcesz usunąć to stanowisko?');">
                                        🗑️ Usuń
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
