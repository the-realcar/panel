<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="page-header">
    <h1>📋 Generator Rozkladow Pasazerow</h1>
</div>

<div class="card">
    <div class="card-header">
        <h2>Wybierz linie i wariant trasy</h2>
    </div>
    <div class="card-body">
        <?php if (empty($lines)): ?>
            <div class="empty-state">
                <p>Brak aktywnych linii w systemie. Dodaj linie w panelu administratora.</p>
            </div>
        <?php else: ?>
            <form method="GET" action="/management/schedule-generator/generate.php" id="generator-form">
                <div class="form-group">
                    <label for="line_id">Linia</label>
                    <select name="line_id" id="line_id" class="form-control" required>
                        <option value="">-- wybierz linie --</option>
                        <?php foreach ($lines as $line): ?>
                            <option value="<?php echo (int)$line['id']; ?>">
                                <?php echo e($line['line_number']); ?>
                                <?php if ($line['name']): ?>
                                    - <?php echo e($line['name']); ?>
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" id="variant-group" style="display:none;">
                    <label for="variant_id">Wariant trasy (kierunek)</label>
                    <select name="variant_id" id="variant_id" class="form-control" required>
                        <option value="">-- wybierz wariant --</option>
                    </select>
                </div>

                <div class="form-actions" id="submit-group" style="display:none;">
                    <button type="submit" class="btn btn-primary">Generuj rozklad</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('line_id').addEventListener('change', function () {
    var lineId = this.value;
    var variantGroup = document.getElementById('variant-group');
    var submitGroup  = document.getElementById('submit-group');
    var variantSelect = document.getElementById('variant_id');

    variantSelect.innerHTML = '<option value="">-- wybierz wariant --</option>';
    variantGroup.style.display = 'none';
    submitGroup.style.display  = 'none';

    if (!lineId) {
        return;
    }

    fetch('/management/schedule-generator/get-variants.php?line_id=' + encodeURIComponent(lineId))
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.length === 0) {
                variantSelect.innerHTML = '<option value="">Brak aktywnych wariantow dla tej linii</option>';
                variantGroup.style.display = 'block';
                return;
            }
            data.forEach(function (v) {
                var opt = document.createElement('option');
                opt.value = v.id;
                opt.textContent = v.variant_name + (v.direction ? ' (' + v.direction + ')' : '');
                variantSelect.appendChild(opt);
            });
            variantGroup.style.display = 'block';
            submitGroup.style.display  = 'block';
        })
        .catch(function () {
            variantGroup.style.display = 'none';
        });
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
