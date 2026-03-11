<?php include __DIR__ . '/../layouts/header.php'; ?>

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
    crossorigin="anonymous"
>

<style>
    [data-theme="dark"] body,
    [data-theme="dark"] .main-content,
    [data-theme="dark"] .content-container {
        background-color: var(--bg) !important;
        color: var(--text);
    }

    [data-theme="dark"] .filters-section,
    [data-theme="dark"] .table-responsive {
        background-color: transparent;
    }

    .fleet-table {
        font-size: 0.92rem;
    }

    [data-theme="dark"] .fleet-table {
        --bs-table-color: var(--text);
        --bs-table-bg: var(--surface);
        --bs-table-border-color: var(--border);
        --bs-table-striped-color: var(--text);
        --bs-table-striped-bg: #253247;
        --bs-table-hover-color: var(--text);
        --bs-table-hover-bg: #2d3d54;
    }

    [data-theme="dark"] .fleet-table thead th {
        background-color: var(--surface-alt);
    }

    .fleet-table th {
        white-space: nowrap;
        vertical-align: middle;
    }

    .fleet-table td {
        vertical-align: middle;
    }

    .fleet-table tbody tr.fleet-unavailable-row > * {
        background-color: #f8d7da !important;
        color: #842029;
    }

    [data-theme="dark"] .fleet-table tbody tr.fleet-unavailable-row > * {
        background-color: #5b1a1f !important;
        color: #ffd9dc;
    }
</style>

<div class="content-container">
    <div class="page-header">
        <h1><?php echo htmlspecialchars($page_title); ?></h1>
        <a href="/dispatcher/dashboard.php" class="btn btn-secondary">Powrót do panelu</a>
    </div>

    <!-- Filtry -->
    <div class="filters-section">
        <form method="GET" class="filter-form">
            <div class="form-group">
                <label for="status">Status pojazdu:</label>
                <select name="status" id="status" class="form-control" onchange="this.form.submit()">
                    <option value="">Wszystkie</option>
                    <option value="sprawny" <?php echo $status_filter === 'sprawny' ? 'selected' : ''; ?>>
                        Sprawny
                    </option>
                    <option value="w naprawie" <?php echo $status_filter === 'w naprawie' ? 'selected' : ''; ?>>
                        W naprawie
                    </option>
                    <option value="odstawiony" <?php echo $status_filter === 'odstawiony' ? 'selected' : ''; ?>>
                        Odstawiony
                    </option>
                    <option value="zawieszony" <?php echo $status_filter === 'zawieszony' ? 'selected' : ''; ?>>
                        Zawieszony
                    </option>
                </select>
            </div>
        </form>
    </div>

    <!-- Lista pojazdów -->
    <?php if (empty($vehicles)): ?>
        <div class="empty-state">
            <p>Brak pojazdów do wyświetlenia.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered align-middle fleet-table mb-0">
                <thead>
                    <tr>
                        <th>Nr pojazdu</th>
                        <th>Marka</th>
                        <th>Model</th>
                        <th>Napęd</th>
                        <th>Rejestracja poj.</th>
                        <th>Opiekun 1</th>
                        <th>Opiekun 2</th>
                        <th>Notatka</th>
                        <th>Rok produkcji</th>
                        <th>Pojemność</th>
                        <th>Silnik</th>
                        <th>Skrzynia</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vehicles as $vehicle): ?>
                        <?php
                        $is_unavailable = in_array($vehicle['status'], ['w naprawie', 'odstawiony'], true);
                        ?>
                        <tr class="<?php echo $is_unavailable ? 'fleet-unavailable-row table-danger' : ''; ?>">
                            <td><strong><?php echo htmlspecialchars($vehicle['nr_poj']); ?></strong></td>
                            <td><?php echo htmlspecialchars($vehicle['marka'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($vehicle['model']); ?></td>
                            <td><?php echo htmlspecialchars($vehicle['typ_napedu'] ?? '—'); ?></td>
                            <td>
                                <?php if ($vehicle['reg_plate']): ?>
                                    <span class="badge text-bg-secondary">
                                        <?php echo htmlspecialchars($vehicle['reg_plate']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($vehicle['opiekun_1'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($vehicle['opiekun_2'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($vehicle['dodatkowe_informacje'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($vehicle['rok_prod'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($vehicle['pojemnosc'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($vehicle['engine'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($vehicle['gearbox'] ?? '—'); ?></td>
                            <td>
                                <?php
                                $status_info = [
                                    'sprawny' => ['label' => 'Sprawny', 'class' => 'text-bg-success'],
                                    'w naprawie' => ['label' => 'W naprawie', 'class' => 'text-bg-warning'],
                                    'odstawiony' => ['label' => 'Odstawiony', 'class' => 'text-bg-danger'],
                                    'zawieszony' => ['label' => 'Zawieszony', 'class' => 'text-bg-secondary']
                                ];
                                $status = $vehicle['status'];
                                $info = $status_info[$status] ?? ['label' => $status, 'class' => 'text-bg-secondary'];
                                ?>
                                <span class="badge <?php echo $info['class']; ?>">
                                    <?php echo htmlspecialchars($info['label']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
