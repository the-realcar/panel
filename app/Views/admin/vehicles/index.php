<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>🚌 Zarządzanie pojazdami</h1>
    <?php if ($rbac->hasPermission('vehicles', 'create')): ?>
        <a href="/admin/vehicles/create.php" class="btn btn-primary">➕ Dodaj pojazd</a>
    <?php endif; ?>
</div>

<?php
$displayValue = static function ($value, $fallback = '—') {
    $text = trim((string)($value ?? ''));
    if ($text === '' || in_array(mb_strtolower($text), ['nd', 'nd.', 'n/d', 'null'], true)) {
        return $fallback;
    }

    return $text;
};
?>

<div class="card">
    <div class="card-header">
        <form method="GET" class="form-inline" style="gap: 0.75rem; flex-wrap: wrap;">
            <label for="status">Filtruj po statusie:</label>
            <select name="status" id="status" class="form-control" onchange="this.form.submit()">
                <option value="">Wszystkie</option>
                <option value="sprawny" <?php echo $status_filter === 'sprawny' ? 'selected' : ''; ?>>Sprawny</option>
                <option value="w naprawie" <?php echo $status_filter === 'w naprawie' ? 'selected' : ''; ?>>W naprawie</option>
                <option value="odstawiony" <?php echo $status_filter === 'odstawiony' ? 'selected' : ''; ?>>Odstawiony</option>
                <option value="zawieszony" <?php echo $status_filter === 'zawieszony' ? 'selected' : ''; ?>>Zawieszony</option>
            </select>
            <input type="text" id="vehicles-search" class="form-control" placeholder="Szukaj pojazdu..." style="min-width: 220px;">
        </form>
    </div>
    <div class="card-body">
        <?php if (empty($vehicles)): ?>
            <p class="text-muted">Brak pojazdów do wyświetlenia.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table" id="vehicles-table" data-sortable-table data-default-sort="0:asc">
                    <thead>
                        <tr>
                            <th data-sort-type="number">ID</th>
                            <th>Numer pojazdu</th>
                            <th>Rejestracja</th>
                            <th>Typ</th>
                            <th>Model</th>
                            <th>Pojemnosc</th>
                            <th>Zajezdnia</th>
                            <th>Status</th>
                            <th data-no-sort="true">Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vehicles as $vehicle): ?>
                        <tr>
                            <td data-label="ID"><?php echo $vehicle['id']; ?></td>
                            <td data-label="Numer pojazdu"><strong><?php echo e($vehicle['nr_poj']); ?></strong></td>
                            <td data-label="Rejestracja"><?php echo e($vehicle['reg_plate'] ?? '-'); ?></td>
                            <td data-label="Typ"><?php echo e($vehicle['vehicle_type']); ?></td>
                            <td data-label="Model"><?php echo e($displayValue($vehicle['model'] ?? null)); ?></td>
                            <td data-label="Pojemnosc"><?php echo e($displayValue($vehicle['pojemnosc'] ?? null)); ?></td>
                            <td data-label="Zajezdnia"><?php echo e($displayValue($vehicle['zajezdnia'] ?? null)); ?></td>
                            <td data-label="Status"><?php echo getStatusBadge($vehicle['status']); ?></td>
                            <td data-label="Akcje">
                                <div class="btn-group">
                                    <?php if ($rbac->hasPermission('vehicles', 'update')): ?>
                                        <a href="/admin/vehicles/edit.php?id=<?php echo $vehicle['id']; ?>" 
                                           class="btn btn-sm btn-secondary">✏️ Edytuj</a>
                                    <?php endif; ?>
                                    <?php if ($rbac->hasPermission('vehicles', 'delete')): ?>
                                        <form method="POST" action="/admin/vehicles/delete.php" style="display:inline;">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="id" value="<?php echo $vehicle['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Czy na pewno chcesz usunąć ten pojazd?');">
                                                🗑️ Usuń
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <?php echo pagination($page, $total_pages, '/admin/vehicles/index.php' . ($status_filter ? '?status=' . urlencode($status_filter) . '&' : '?')); ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
(function() {
    const input = document.getElementById('vehicles-search');
    if (!input) {
        return;
    }

    const rows = Array.from(document.querySelectorAll('table tbody tr'));
    input.addEventListener('input', function() {
        const q = input.value.trim().toLowerCase();
        rows.forEach(function(row) {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
})();
</script>

<?php View::partial('layouts/footer'); ?>
