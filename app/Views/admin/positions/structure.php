<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>🏢 Struktura organizacyjna</h1>
    <a href="/admin/positions/index.php" class="btn btn-secondary">← Powrot do stanowisk</a>
</div>

<?php if (empty($structure)): ?>
    <div class="card"><div class="card-body"><p class="text-muted">Brak danych struktury.</p></div></div>
<?php else: ?>
    <?php foreach ($structure as $department => $positions): ?>
        <div class="card" style="margin-bottom: 1rem;">
            <div class="card-header">
                <h2 class="card-title"><?php echo e($department); ?></h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Stanowisko</th>
                                <th>Oblozenie</th>
                                <th>Status limitu</th>
                                <th>Przypisani uzytkownicy</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($positions as $position): ?>
                                <?php
                                $current = (int)$position['current_count'];
                                $max = $position['max_count'] !== null ? (int)$position['max_count'] : null;
                                $ratio = ($max && $max > 0) ? ($current / $max) : null;

                                $color = 'var(--info)';
                                $label = 'Bez limitu';

                                if ($max !== null && $max > 0) {
                                    if ($ratio >= 1) {
                                        $color = 'var(--danger)';
                                        $label = 'Limit osiagniety';
                                    } elseif ($ratio >= 0.8) {
                                        $color = 'var(--warning)';
                                        $label = 'Blisko limitu (80-99%)';
                                    } else {
                                        $color = 'var(--success)';
                                        $label = 'Ponizej limitu';
                                    }
                                }
                                ?>
                                <tr>
                                    <td data-label="Stanowisko">
                                        <strong><?php echo e($position['name']); ?></strong>
                                        <?php if (!empty($position['description'])): ?>
                                            <br><small class="text-muted"><?php echo e($position['description']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Oblozenie">
                                        <?php if ($max !== null && $max > 0): ?>
                                            <?php echo $current; ?> / <?php echo $max; ?>
                                        <?php else: ?>
                                            <?php echo $current; ?> / ∞
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Status limitu">
                                        <span class="badge" style="background: <?php echo $color; ?>; color: #fff;"><?php echo e($label); ?></span>
                                        <?php if ($max !== null && $max > 0): ?>
                                            <div class="progress-bar" style="margin-top: 0.5rem;">
                                                <div class="progress-fill" style="width: <?php echo min(100, max(0, $ratio * 100)); ?>%; background-color: <?php echo $color; ?>;"></div>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Przypisani uzytkownicy">
                                        <details>
                                            <summary>Zobacz liste (<?php echo count($position['assigned_users']); ?>)</summary>
                                            <?php if (empty($position['assigned_users'])): ?>
                                                <p class="text-muted" style="margin-top: 0.5rem;">Brak przypisanych osob.</p>
                                            <?php else: ?>
                                                <ul style="margin-top: 0.5rem; padding-left: 1.25rem;">
                                                    <?php foreach ($position['assigned_users'] as $user): ?>
                                                        <li>
                                                            <?php echo e(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: $user['username']); ?>
                                                            <small class="text-muted">(<?php echo e($user['username']); ?><?php echo !empty($user['active']) ? ', aktywny' : ', nieaktywny'; ?>)</small>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                        </details>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php View::partial('layouts/footer'); ?>
