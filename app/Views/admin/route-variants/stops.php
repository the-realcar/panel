<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>🧱 Builder trasy</h1>
    <a href="/admin/route-variants/index.php" class="btn btn-secondary">← Powrot do wariantow</a>
</div>

<div class="card">
    <div class="card-body">
        <p><strong>Linia:</strong> <?php echo e($variant['line_number']); ?> - <?php echo e($variant['line_name']); ?></p>
        <p><strong>Wariant:</strong> <?php echo e($variant['variant_name']); ?> (<?php echo e($variant['variant_type']); ?>)</p>
        <p><strong>Kierunek:</strong> <?php echo e($variant['direction'] ?? '-'); ?></p>
    </div>
</div>

<div class="row">
    <div class="col col-12 col-lg-5">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Dodaj przystanek do sekwencji</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/route-variants/stops.php?id=<?php echo (int)$variant['id']; ?>">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="action" value="add">

                    <div class="form-group">
                        <label for="platform_id" class="form-label">Stanowisko</label>
                        <select id="platform_id" name="platform_id" class="form-control" required>
                            <option value="">Wybierz stanowisko</option>
                            <?php foreach ($platforms as $platform): ?>
                                <option value="<?php echo (int)$platform['id']; ?>">
                                    <?php echo e($platform['stop_name']); ?> [<?php echo e($platform['platform_number']); ?>]
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="travel_time_minutes" class="form-label">Czas dojazdu od poprzedniego (min)</label>
                        <input type="number" id="travel_time_minutes" name="travel_time_minutes" class="form-control" min="0" step="1">
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_timing_point" value="1">
                            Punkt czasowy
                        </label>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Dodaj do sekwencji</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col col-12 col-lg-7">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Sekwencja przystankow</h2>
            </div>
            <div class="card-body">
                <?php if (empty($route_stops)): ?>
                    <p class="text-muted">Brak przystankow przypisanych do wariantu.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Kolejnosc</th>
                                    <th>Przystanek / stanowisko</th>
                                    <th>Czas dojazdu</th>
                                    <th>Punkt czasowy</th>
                                    <th>Akcje</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($route_stops as $stop): ?>
                                    <tr>
                                        <td data-label="Kolejnosc"><strong><?php echo (int)$stop['stop_sequence']; ?></strong></td>
                                        <td data-label="Przystanek / stanowisko">
                                            <?php echo e($stop['stop_name']); ?>
                                            <br><small class="text-muted">Stanowisko: <?php echo e($stop['platform_number']); ?> (<?php echo e($stop['platform_type']); ?>)</small>
                                        </td>
                                        <td data-label="Czas dojazdu">
                                            <form method="POST" action="/admin/route-variants/stops.php?id=<?php echo (int)$variant['id']; ?>" class="form-inline" style="gap: 0.5rem;">
                                                <?php echo csrfField(); ?>
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="stop_id" value="<?php echo (int)$stop['id']; ?>">
                                                <input type="number" name="travel_time_minutes" class="form-control" min="0" step="1" value="<?php echo e((string)($stop['travel_time_minutes'] ?? '')); ?>" style="max-width: 90px;">
                                        </td>
                                        <td data-label="Punkt czasowy">
                                                <label class="checkbox-label" style="justify-content: center;">
                                                    <input type="checkbox" name="is_timing_point" value="1" <?php echo !empty($stop['is_timing_point']) ? 'checked' : ''; ?>>
                                                </label>
                                        </td>
                                        <td data-label="Akcje">
                                                <button type="submit" class="btn btn-sm btn-secondary">Zapisz</button>
                                            </form>
                                            <div class="btn-group" style="margin-top: 0.5rem;">
                                                <form method="POST" action="/admin/route-variants/stops.php?id=<?php echo (int)$variant['id']; ?>" style="display:inline;">
                                                    <?php echo csrfField(); ?>
                                                    <input type="hidden" name="action" value="move_up">
                                                    <input type="hidden" name="stop_id" value="<?php echo (int)$stop['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-secondary">↑</button>
                                                </form>
                                                <form method="POST" action="/admin/route-variants/stops.php?id=<?php echo (int)$variant['id']; ?>" style="display:inline;">
                                                    <?php echo csrfField(); ?>
                                                    <input type="hidden" name="action" value="move_down">
                                                    <input type="hidden" name="stop_id" value="<?php echo (int)$stop['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-secondary">↓</button>
                                                </form>
                                                <form method="POST" action="/admin/route-variants/stops.php?id=<?php echo (int)$variant['id']; ?>" style="display:inline;">
                                                    <?php echo csrfField(); ?>
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="stop_id" value="<?php echo (int)$stop['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Usunac przystanek z sekwencji?');">🗑️</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php View::partial('layouts/footer'); ?>
