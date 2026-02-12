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
                    <option value="available" <?php echo $status_filter === 'available' ? 'selected' : ''; ?>>
                        Dostępne
                    </option>
                    <option value="in_use" <?php echo $status_filter === 'in_use' ? 'selected' : ''; ?>>
                        W trasie
                    </option>
                    <option value="maintenance" <?php echo $status_filter === 'maintenance' ? 'selected' : ''; ?>>
                        Konserwacja
                    </option>
                    <option value="broken" <?php echo $status_filter === 'broken' ? 'selected' : ''; ?>>
                        Niesprawny
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
                        <th>Ostatni przegląd</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vehicles as $vehicle): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($vehicle['vehicle_number']); ?></strong></td>
                            <td><?php echo htmlspecialchars($vehicle['model']); ?></td>
                            <td>
                                <?php if ($vehicle['registration_plate']): ?>
                                    <span class="badge badge-secondary">
                                        <?php echo htmlspecialchars($vehicle['registration_plate']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $type_labels = [
                                    'bus' => 'Autobus',
                                    'articulated_bus' => 'Autobus przegubowy',
                                    'tram' => 'Tramwaj',
                                    'metro' => 'Metro'
                                ];
                                echo htmlspecialchars($type_labels[$vehicle['vehicle_type']] ?? $vehicle['vehicle_type']);
                                ?>
                            </td>
                            <td><?php echo (int)$vehicle['capacity']; ?> osób</td>
                            <td>
                                <?php
                                $status_info = [
                                    'available' => ['label' => 'Dostępny', 'class' => 'badge-success'],
                                    'in_use' => ['label' => 'W trasie', 'class' => 'badge-primary'],
                                    'maintenance' => ['label' => 'Konserwacja', 'class' => 'badge-warning'],
                                    'broken' => ['label' => 'Niesprawny', 'class' => 'badge-danger']
                                ];
                                $status = $vehicle['status'];
                                $info = $status_info[$status] ?? ['label' => $status, 'class' => 'badge-secondary'];
                                ?>
                                <span class="badge <?php echo $info['class']; ?>">
                                    <?php echo htmlspecialchars($info['label']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($vehicle['last_inspection']): ?>
                                    <?php echo date('d.m.Y', strtotime($vehicle['last_inspection'])); ?>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
