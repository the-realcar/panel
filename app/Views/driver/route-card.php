<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<h1>📝 Karta Drogowa</h1>
<p class="text-muted">Wypełnij kartę drogową po zakończeniu trasy</p>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Nowa karta drogowa</h2>
    </div>
    <div class="card-body">
        <form method="POST" action="" class="form">
            <?php echo csrfField(); ?>

            <div class="row">
                <div class="col col-12 col-md-6">
                    <div class="form-group">
                        <label for="vehicle_id">Pojazd *</label>
                        <select name="vehicle_id" id="vehicle_id" class="form-control" required>
                            <option value="">Wybierz pojazd</option>
                            <?php foreach ($vehicles as $vehicle): ?>
                                <option value="<?php echo $vehicle['id']; ?>">
                                    <?php echo e($vehicle['nr_poj']); ?>
                                    <?php if ($vehicle['reg_plate']): ?>
                                        (<?php echo e($vehicle['reg_plate']); ?>)
                                    <?php endif; ?>
                                    <?php if ($vehicle['model']): ?>
                                        - <?php echo e($vehicle['model']); ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col col-12 col-md-6">
                    <div class="form-group">
                        <label for="line_id">Linia *</label>
                        <select name="line_id" id="line_id" class="form-control" required>
                            <option value="">Wybierz linię</option>
                            <?php foreach ($lines as $line): ?>
                                <option value="<?php echo $line['id']; ?>">
                                    <?php echo e($line['line_number']); ?> - <?php echo e($line['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col col-12 col-md-4">
                    <div class="form-group">
                        <label for="route_date">Data trasy *</label>
                        <input type="date" name="route_date" id="route_date" class="form-control"
                               value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>

                <div class="col col-12 col-md-4">
                    <div class="form-group">
                        <label for="start_time">Godzina rozpoczęcia *</label>
                        <input type="time" name="start_time" id="start_time" class="form-control" required>
                    </div>
                </div>

                <div class="col col-12 col-md-4">
                    <div class="form-group">
                        <label for="end_time">Godzina zakończenia *</label>
                        <input type="time" name="end_time" id="end_time" class="form-control" required>
                    </div>
                </div>
            </div>

            <div id="variants-section" style="display:none;">
                <h3 style="margin:1.25rem 0 0.75rem;">Wykonane kursy</h3>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Kierunek / Wariant trasy</th>
                                <th>Typ</th>
                                <th style="width:130px;">Liczba kursów</th>
                            </tr>
                        </thead>
                        <tbody id="variants-tbody"></tbody>
                    </table>
                </div>
            </div>

            <div id="variants-empty" class="alert alert-info" style="display:none;">
                Brak zdefiniowanych kierunków dla tej linii. Możesz mimo to zapisać kartę.
            </div>

            <div class="row" style="margin-top:0.5rem;">
                <div class="col col-12 col-md-4">
                    <div class="form-group">
                        <label for="passengers_count">Liczba pasażerów</label>
                        <input type="number" name="passengers_count" id="passengers_count" class="form-control"
                               min="0" step="1" value="0">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="notes">Uwagi</label>
                <textarea name="notes" id="notes" class="form-control" rows="4"
                          placeholder="Dodatkowe informacje o trasie..."></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Zapisz kartę drogową</button>
                <a href="/driver/dashboard.php" class="btn btn-secondary">Anuluj</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Ostatnie karty drogowe</h2>
    </div>
    <div class="card-body">
        <?php if (empty($recent_cards)): ?>
            <p class="text-muted">Brak zapisanych kart drogowych.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Pojazd</th>
                            <th>Linia</th>
                            <th>Godziny</th>
                            <th>Kursy</th>
                            <th>Pasażerowie</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_cards as $card): ?>
                        <tr>
                            <td data-label="Data">
                                <?php echo formatDate($card['route_date'], 'd.m.Y'); ?>
                            </td>
                            <td data-label="Pojazd">
                                <?php echo e($card['nr_poj'] ?? 'Brak'); ?>
                                <?php if ($card['model']): ?>
                                    <br><small class="text-muted"><?php echo e($card['model']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td data-label="Linia">
                                <strong><?php echo e($card['line_number'] ?? 'Brak'); ?></strong>
                                <?php if ($card['line_name']): ?>
                                    <br><small class="text-muted"><?php echo e($card['line_name']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td data-label="Godziny">
                                <?php echo formatTime($card['start_time']); ?> -
                                <?php echo formatTime($card['end_time']); ?>
                            </td>
                            <td data-label="Kursy">
                                <?php echo (int)($card['total_trips'] ?? 0); ?>
                            </td>
                            <td data-label="Pasażerowie">
                                <?php echo $card['passengers_count'] ?? 0; ?>
                            </td>
                            <td data-label="Status">
                                <?php echo getStatusBadge($card['status']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function () {
    var variantsByLine = <?php echo json_encode($variants_by_line, JSON_HEX_TAG | JSON_HEX_AMP); ?>;

    var typeLabels = {
        'normal':      'Normalny',
        'short':       'Skrócony',
        'depot_entry': 'Zjazd do zajezdni',
        'depot_exit':  'Wyjazd z zajezdni'
    };

    var lineSelect = document.getElementById('line_id');
    var section    = document.getElementById('variants-section');
    var empty      = document.getElementById('variants-empty');
    var tbody      = document.getElementById('variants-tbody');

    function updateVariants() {
        var lineId = lineSelect.value;
        tbody.innerHTML = '';
        section.style.display = 'none';
        empty.style.display   = 'none';

        if (!lineId) return;

        var variants = variantsByLine[lineId];
        if (!variants || variants.length === 0) {
            empty.style.display = 'block';
            return;
        }

        variants.forEach(function (v) {
            var nameHtml = v.direction
                ? v.variant_name + ' <small class="text-muted">(' + v.direction + ')</small>'
                : v.variant_name;
            var typeLabel = typeLabels[v.variant_type] || v.variant_type;

            var tr = document.createElement('tr');
            tr.innerHTML =
                '<td data-label="Kierunek">' + nameHtml + '</td>' +
                '<td data-label="Typ">' + typeLabel + '</td>' +
                '<td data-label="Liczba kurs\u00f3w">' +
                    '<input type="number" name="trips[' + v.id + ']" class="form-control"' +
                    ' min="0" step="1" value="0" style="width:100px;">' +
                '</td>';
            tbody.appendChild(tr);
        });

        section.style.display = 'block';
    }

    lineSelect.addEventListener('change', updateVariants);
    updateVariants();
}());
</script>

<?php View::partial('layouts/footer'); ?>


<div class="card">
    <div class="card-header">
        <h2 class="card-title">Nowa karta drogowa</h2>
    </div>
    <div class="card-body">
        <form method="POST" action="" class="form">
            <?php echo csrfField(); ?>

            <div class="row">
                <div class="col col-12 col-md-6">
                    <div class="form-group">
                        <label for="vehicle_id">Pojazd *</label>
                        <select name="vehicle_id" id="vehicle_id" class="form-control" required>
                            <option value="">Wybierz pojazd</option>
                            <?php foreach ($vehicles as $vehicle): ?>
                                <option value="<?php echo $vehicle['id']; ?>">
                                    <?php echo e($vehicle['nr_poj']); ?> 
                                    <?php if ($vehicle['reg_plate']): ?>
                                        (<?php echo e($vehicle['reg_plate']); ?>)
                                    <?php endif; ?>
                                    <?php if ($vehicle['model']): ?>
                                        - <?php echo e($vehicle['model']); ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col col-12 col-md-6">
                    <div class="form-group">
                        <label for="line_id">Linia *</label>
                        <select name="line_id" id="line_id" class="form-control" required>
                            <option value="">Wybierz linię</option>
                            <?php foreach ($lines as $line): ?>
                                <option value="<?php echo $line['id']; ?>">
                                    <?php echo e($line['line_number']); ?> - <?php echo e($line['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col col-12 col-md-4">
                    <div class="form-group">
                        <label for="route_date">Data trasy *</label>
                        <input type="date" name="route_date" id="route_date" class="form-control" 
                               value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>

                <div class="col col-12 col-md-4">
                    <div class="form-group">
                        <label for="start_time">Godzina rozpoczęcia *</label>
                        <input type="time" name="start_time" id="start_time" class="form-control" required>
                    </div>
                </div>

                <div class="col col-12 col-md-4">
                    <div class="form-group">
                        <label for="end_time">Godzina zakończenia *</label>
                        <input type="time" name="end_time" id="end_time" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col col-12 col-md-6">
                    <div class="form-group">
                        <label for="passengers_count">Liczba pasażerów</label>
                        <input type="number" name="passengers_count" id="passengers_count" class="form-control" 
                               min="0" step="1" value="0">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="notes">Uwagi</label>
                <textarea name="notes" id="notes" class="form-control" rows="4" 
                          placeholder="Dodatkowe informacje o trasie..."></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    Zapisz kartę drogową
                </button>
                <a href="/driver/dashboard.php" class="btn btn-secondary">
                    Anuluj
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Ostatnie karty drogowe</h2>
    </div>
    <div class="card-body">
        <?php if (empty($recent_cards)): ?>
            <p class="text-muted">Brak zapisanych kart drogowych.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Pojazd</th>
                            <th>Linia</th>
                            <th>Godziny</th>
                            <th>Wykonane kursy</th>
                            <th>Pasażerowie</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_cards as $card): ?>
                        <tr>
                            <td data-label="Data">
                                <?php echo formatDate($card['route_date'], 'd.m.Y'); ?>
                            </td>
                            <td data-label="Pojazd">
                                <?php echo e($card['nr_poj'] ?? 'Brak'); ?>
                                <?php if ($card['model']): ?>
                                    <br><small class="text-muted"><?php echo e($card['model']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td data-label="Linia">
                                <strong><?php echo e($card['line_number'] ?? 'Brak'); ?></strong>
                                <?php if ($card['line_name']): ?>
                                    <br><small class="text-muted"><?php echo e($card['line_name']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td data-label="Godziny">
                                <?php echo formatTime($card['start_time']); ?> - 
                                <?php echo formatTime($card['end_time']); ?>
                            </td>
                            <td data-label="Wykonane kursy">
                                <?php echo (int)($card['total_trips'] ?? 0); ?>
                            </td>
                            <td data-label="Pasażerowie">
                                <?php echo $card['passengers_count'] ?? 0; ?>
                            </td>
                            <td data-label="Status">
                                <?php echo getStatusBadge($card['status']); ?>
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
