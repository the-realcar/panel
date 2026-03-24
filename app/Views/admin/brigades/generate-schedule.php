<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>📄 Generator rozkładów jazdy</h1>
    <a href="/admin/brigades/index.php" class="btn btn-secondary">⬅️ Brygady</a>
</div>

<div class="card">
    <div class="card-header">
        <form method="GET" class="form-inline" id="line-filter-form">
            <label for="line">Wybierz linię:</label>
            <select name="line" id="line" class="form-control" onchange="this.form.submit()">
                <option value="">— wybierz linię —</option>
                <?php foreach ($lines as $line): ?>
                    <option value="<?php echo $line['id']; ?>" <?php echo $line_filter == $line['id'] ? 'selected' : ''; ?>>
                        <?php echo e($line['line_number'] . ' – ' . $line['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <div class="card-body">
        <?php if (!$departures_available): ?>
            <div class="alert alert-warning">
                Tabela odjazdow brygad nie jest dostępna w tej bazie danych. Strona pozostaje dostępna, ale generowanie plików ZIP będzie wyłączone do czasu synchronizacji schematu bazy.
            </div>
        <?php endif; ?>

        <?php if ($line_filter && !empty($brigades)): ?>
            <form method="POST" action="/admin/brigades/generate-schedule.php?line=<?php echo (int)$line_filter; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

                <p style="margin-bottom: 0.75rem;">
                    Zaznacz brygady, dla których chcesz wygenerować rozkłady jazdy, a następnie kliknij przycisk pobierania.
                    Plik <code>.zip</code> będzie zawierał pliki HTML gotowe do wydruku.
                </p>

                <div style="margin-bottom: 0.75rem; display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="toggleAll(true)">Zaznacz wszystkie</button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="toggleAll(false)">Odznacz wszystkie</button>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 40px;"><input type="checkbox" id="check-all" onchange="toggleAll(this.checked)"></th>
                                <th>Brygada</th>
                                <th>Zmiana A</th>
                                <th>Zmiana B</th>
                                <th>Spółka</th>
                                <th>Odjazdy</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($brigades as $brigade): ?>
                            <tr>
                                <td><input type="checkbox" name="brigade_ids[]" value="<?php echo (int)$brigade['id']; ?>" class="brigade-check"></td>
                                <td><strong><?php echo e($brigade['line_number'] . '/' . $brigade['brigade_number']); ?></strong></td>
                                <td>
                                    <?php if (!empty($brigade['shift_a_start']) && !empty($brigade['shift_a_end'])): ?>
                                        <?php echo e(substr($brigade['shift_a_start'], 0, 5) . ' – ' . substr($brigade['shift_a_end'], 0, 5)); ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($brigade['shift_b_start']) && !empty($brigade['shift_b_end'])): ?>
                                        <?php echo e(substr($brigade['shift_b_start'], 0, 5) . ' – ' . substr($brigade['shift_b_end'], 0, 5)); ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo !empty($brigade['przewoznik']) ? e($brigade['przewoznik']) : '<span class="text-muted">—</span>'; ?></td>
                                <td>
                                    <?php if ((int)($brigade['departures_count'] ?? 0) > 0): ?>
                                        <?php echo (int)$brigade['departures_count']; ?> odjazdów
                                    <?php else: ?>
                                        <span class="text-muted">brak</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary" <?php echo !$departures_available ? 'disabled' : ''; ?>>📥 Pobierz rozkłady (.zip)</button>
                </div>
            </form>
        <?php elseif ($line_filter): ?>
            <p class="text-muted">Brak aktywnych brygad dla wybranej linii.</p>
        <?php else: ?>
            <p class="text-muted">Wybierz linię, aby zobaczyć dostępne brygady.</p>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleAll(checked) {
    document.querySelectorAll('.brigade-check').forEach(function(cb) { cb.checked = checked; });
    var ca = document.getElementById('check-all');
    if (ca) ca.checked = checked;
}
</script>

<?php View::partial('layouts/footer'); ?>
