<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

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
        width: 100%;
        table-layout: fixed;
        font-size: clamp(0.74rem, 0.62rem + 0.24vw, 0.9rem);
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
        white-space: normal;
        vertical-align: middle;
        line-height: 1.2;
        padding: 0.75rem 0.55rem;
    }

    .fleet-table td {
        vertical-align: middle;
        padding: 0.75rem 0.55rem;
        overflow-wrap: anywhere;
        word-break: break-word;
        line-height: 1.35;
    }

    .fleet-table th:nth-child(1),
    .fleet-table td:nth-child(1) {
        width: 8.5%;
    }

    .fleet-table th:nth-child(2),
    .fleet-table td:nth-child(2),
    .fleet-table th:nth-child(3),
    .fleet-table td:nth-child(3),
    .fleet-table th:nth-child(4),
    .fleet-table td:nth-child(4),
    .fleet-table th:nth-child(5),
    .fleet-table td:nth-child(5),
    .fleet-table th:nth-child(6),
    .fleet-table td:nth-child(6),
    .fleet-table th:nth-child(7),
    .fleet-table td:nth-child(7),
    .fleet-table th:nth-child(8),
    .fleet-table td:nth-child(8),
    .fleet-table th:nth-child(9),
    .fleet-table td:nth-child(9),
    .fleet-table th:nth-child(10),
    .fleet-table td:nth-child(10),
    .fleet-table th:nth-child(11),
    .fleet-table td:nth-child(11),
    .fleet-table th:nth-child(12),
    .fleet-table td:nth-child(12),
    .fleet-table th:nth-child(13),
    .fleet-table td:nth-child(13) {
        width: 7.625%;
    }

    .fleet-table .badge {
        display: inline-flex;
        max-width: 100%;
        justify-content: center;
        text-align: center;
        white-space: normal;
        line-height: 1.15;
    }

    .fleet-table tbody tr.fleet-unavailable-row > * {
        background-color: #f1aeb5 !important;
        box-shadow: none !important;
        color: #58151c !important;
    }

    [data-theme="dark"] .fleet-table tbody tr.fleet-unavailable-row > * {
        background-color: #5b1a1f !important;
        box-shadow: none !important;
        color: #ffd9dc !important;
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
        <div class="table-responsive" style="overflow-x: visible;">
            <table class="table fleet-table">
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
                        <tr class="<?php echo $is_unavailable ? 'fleet-unavailable-row' : ''; ?>">
                            <td><strong><?php echo htmlspecialchars($vehicle['nr_poj']); ?></strong></td>
                            <td><?php echo htmlspecialchars($vehicle['marka'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($vehicle['model']); ?></td>
                            <td><?php echo htmlspecialchars($vehicle['typ_napedu'] ?? '—'); ?></td>
                            <td>
                                <?php if ($vehicle['reg_plate']): ?>
                                    <span class="badge badge-secondary">
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
                                    'sprawny' => ['label' => 'Sprawny', 'class' => 'badge-success'],
                                    'w naprawie' => ['label' => 'W naprawie', 'class' => 'badge-warning'],
                                    'odstawiony' => ['label' => 'Odstawiony', 'class' => 'badge-danger'],
                                    'zawieszony' => ['label' => 'Zawieszony', 'class' => 'badge-secondary']
                                ];
                                $status = $vehicle['status'];
                                $info = $status_info[$status] ?? ['label' => $status, 'class' => 'badge-secondary'];
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

<?php View::partial('layouts/footer'); ?>
