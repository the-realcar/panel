<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>💼 Zarządzanie stanowiskami</h1>
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="/admin/positions/structure.php" class="btn btn-secondary">🏢 Struktura organizacyjna</a>
        <?php if ($rbac->hasPermission('positions', 'create')): ?>
            <a href="/admin/positions/create.php" class="btn btn-primary">➕ Dodaj stanowisko</a>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($positions)): ?>
            <p class="text-muted">Brak stanowisk do wyświetlenia.</p>
        <?php else: ?>
            <p style="margin-bottom: 16px; color: var(--text-muted);">Razem stanowisk: <strong><?php echo count($positions); ?></strong></p>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nazwa</th>
                            <th>Dział</th>
                            <th>Limit</th>
                            <th>Obecny stan</th>
                            <th>Opis</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($positions as $position): ?>
                        <tr>
                            <td data-label="ID"><strong><?php echo $position['id']; ?></strong></td>
                            <td data-label="Nazwa"><strong><?php echo e($position['name']); ?></strong></td>
                            <td data-label="Dział"><?php echo e($position['department_name'] ?? '-'); ?></td>
                            <td data-label="Limit">
                                <?php if ($position['max_count']): ?>
                                    <?php echo $position['max_count']; ?>
                                <?php else: ?>
                                    <span class="text-muted">Bez limitu</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Obecny stan">
                                <?php 
                                $current = $position['current_count'];
                                $max = $position['max_count'];
                                ?>
                                <?php echo $current; ?>
                                <?php if ($max): ?>
                                    / <?php echo $max; ?>
                                    <?php
                                    $ratio = $max > 0 ? ($current / $max) : 0;
                                    $color = 'var(--success)';
                                    if ($ratio >= 1) {
                                        $color = 'var(--danger)';
                                    } elseif ($ratio >= 0.8) {
                                        $color = 'var(--warning)';
                                    }
                                    ?>
                                    <div class="progress-bar" style="margin-top: 5px;">
                                        <div class="progress-fill" style="width: <?php echo min(100, ($current / $max) * 100); ?>%; background-color: <?php echo $color; ?>;"></div>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td data-label="Opis">
                                <?php echo e(truncate($position['description'] ?? '', 50)); ?>
                            </td>
                            <td data-label="Akcje">
                                <?php if ($rbac->hasPermission('positions', 'update')): ?>
                                    <a href="/admin/positions/edit.php?id=<?php echo $position['id']; ?>" 
                                       class="btn btn-sm btn-secondary">✏️ Edytuj</a>
                                <?php endif; ?>
                                <?php if ($rbac->hasPermission('positions', 'delete')): ?>
                                    <form method="POST" action="/admin/positions/delete.php" style="display:inline;">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="id" value="<?php echo (int)$position['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Czy na pewno chcesz usunac to stanowisko?');">🗑️ Usun</button>
                                    </form>
                                <?php endif; ?>
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
