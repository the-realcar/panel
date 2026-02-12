<?php include __DIR__ . '/../../layouts/header.php'; ?>

<div class="content-container">
    <div class="page-header">
        <h1><?php echo htmlspecialchars($page_title); ?></h1>
        <div class="header-actions">
            <a href="/admin/stops/index.php" class="btn btn-secondary">Powrót do przystanków</a>
            <?php if ($rbac->hasPermission('platforms', 'create')): ?>
                <a href="/admin/platforms/create.php?stop_id=<?php echo urlencode($stop['stop_id']); ?>" class="btn btn-primary">Dodaj platformę</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="info-card">
        <h3>Przystanek: <?php echo htmlspecialchars($stop['name']); ?></h3>
        <p><strong>Identyfikator:</strong> <?php echo htmlspecialchars($stop['stop_id']); ?></p>
        <?php if ($stop['location_description']): ?>
            <p><strong>Lokalizacja:</strong> <?php echo htmlspecialchars($stop['location_description']); ?></p>
        <?php endif; ?>
    </div>

    <?php if (empty($platforms)): ?>
        <div class="empty-state">
            <p>Brak platform na tym przystanku.</p>
            <?php if ($rbac->hasPermission('platforms', 'create')): ?>
                <a href="/admin/platforms/create.php?stop_id=<?php echo urlencode($stop['stop_id']); ?>" class="btn btn-primary">Dodaj pierwszą platformę</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Numer platformy</th>
                        <th>Opis</th>
                        <th>Data utworzenia</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($platforms as $platform): ?>
                        <tr>
                            <td data-label="Numer">
                                <strong><?php echo htmlspecialchars($platform['platform_number']); ?></strong>
                            </td>
                            <td data-label="Opis">
                                <?php if ($platform['description']): ?>
                                    <?php echo htmlspecialchars($platform['description']); ?>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Data utworzenia">
                                <?php echo date('d.m.Y H:i', strtotime($platform['created_at'])); ?>
                            </td>
                            <td data-label="Akcje">
                                <div class="btn-group">
                                    <?php if ($rbac->hasPermission('platforms', 'update')): ?>
                                        <a href="/admin/platforms/edit.php?id=<?php echo $platform['id']; ?>" 
                                           class="btn btn-sm btn-secondary">✏️ Edytuj</a>
                                    <?php endif; ?>
                                    <?php if ($rbac->hasPermission('platforms', 'delete')): ?>
                                        <form method="POST" action="/admin/platforms/delete.php" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                            <input type="hidden" name="id" value="<?php echo $platform['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Czy na pewno chcesz usunąć tę platformę?');">🗑️ Usuń</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
