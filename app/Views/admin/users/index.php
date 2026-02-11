<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>👥 Zarządzanie użytkownikami</h1>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="form-inline">
            <label for="status">Filtruj po statusie:</label>
            <select name="status" id="status" class="form-control" onchange="this.form.submit()">
                <option value="">Wszyscy</option>
                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Aktywni</option>
                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Nieaktywni</option>
            </select>
        </form>
    </div>
    <div class="card-body">
        <?php if (empty($users)): ?>
            <p class="text-muted">Brak użytkowników do wyświetlenia.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Użytkownik</th>
                            <th>Email</th>
                            <th>Imię i nazwisko</th>
                            <th>Role</th>
                            <th>Stanowiska</th>
                            <th>Status</th>
                            <th>Ostatnie logowanie</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td data-label="ID"><?php echo $user['id']; ?></td>
                            <td data-label="Użytkownik"><strong><?php echo e($user['username']); ?></strong></td>
                            <td data-label="Email"><?php echo e($user['email']); ?></td>
                            <td data-label="Imię i nazwisko">
                                <?php echo e(getFullName($user['first_name'], $user['last_name'])); ?>
                            </td>
                            <td data-label="Role">
                                <?php if ($user['roles']): ?>
                                    <small><?php echo e($user['roles']); ?></small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Stanowiska">
                                <?php if ($user['positions']): ?>
                                    <small><?php echo e($user['positions']); ?></small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Status">
                                <?php if ($user['active']): ?>
                                    <span class="badge badge-success">Aktywny</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Nieaktywny</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Ostatnie logowanie">
                                <?php if ($user['last_login']): ?>
                                    <small><?php echo formatDateTime($user['last_login'], 'd.m.Y H:i'); ?></small>
                                <?php else: ?>
                                    <span class="text-muted">Nigdy</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Akcje">
                                <div class="btn-group">
                                    <?php if ($rbac->hasPermission('users', 'update')): ?>
                                        <a href="/admin/users/assign-position.php?user_id=<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-secondary">📋 Stanowiska</a>
                                    <?php endif; ?>
                                    <?php if ($rbac->hasPermission('users', 'update') && $user['id'] != getCurrentUserId()): ?>
                                        <?php if ($user['active']): ?>
                                            <a href="/admin/users/toggle-status.php?id=<?php echo $user['id']; ?>&action=deactivate&csrf_token=<?php echo generateCsrfToken(); ?>" 
                                               class="btn btn-sm btn-warning"
                                               onclick="return confirm('Czy na pewno chcesz dezaktywować tego użytkownika?');">
                                                🔒 Dezaktywuj
                                            </a>
                                        <?php else: ?>
                                            <a href="/admin/users/toggle-status.php?id=<?php echo $user['id']; ?>&action=activate&csrf_token=<?php echo generateCsrfToken(); ?>" 
                                               class="btn btn-sm btn-success">
                                                ✅ Aktywuj
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <?php echo pagination($page, $total_pages, '/admin/users/index.php' . ($status_filter ? '?status=' . urlencode($status_filter) . '&' : '?')); ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php View::partial('layouts/footer'); ?>
