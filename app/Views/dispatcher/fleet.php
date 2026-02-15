<?php include __DIR__ . '/../layouts/header.php'; ?>

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
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Numer pojazdu</th>
                        <th>Model</th>
                        <th>Rejestracja</th>
                        <th>Typ</th>
                        <th>Pojemność</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vehicles as $vehicle): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($vehicle['nr_poj']); ?></strong></td>
                            <td><?php echo htmlspecialchars($vehicle['model']); ?></td>
                            <td>
                                <?php if ($vehicle['reg_plate']): ?>
                                    <span class="badge badge-secondary">
                                        <?php echo htmlspecialchars($vehicle['reg_plate']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $type_labels = [
                                    'bus' => 'Autobus',
                                    'tbus' => 'Trolejbus',
                                    'articulated_bus' => 'Autobus przegubowy',
                                    'tram' => 'Tramwaj',
                                    'metro' => 'Metro'
                                ];
                                echo htmlspecialchars($type_labels[$vehicle['vehicle_type']] ?? $vehicle['vehicle_type']);
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($vehicle['pojemnosc'] ?? '-'); ?></td>
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

<?php include __DIR__ . '/../layouts/footer.php'; ?>
