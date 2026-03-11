<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>➕ Dodaj brygadę</h1>
    <a href="/admin/brigades/index.php" class="btn btn-secondary">⬅️ Powrót</a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error"><?php echo e($errors['general']); ?></div>
        <?php endif; ?>

        <form method="POST" class="form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

            <div class="form-group">
                <label for="line_id">Linia *</label>
                <select id="line_id" name="line_id" class="form-control" required>
                    <option value="">-- Wybierz linię --</option>
                    <?php foreach ($lines as $line): ?>
                        <option value="<?php echo $line['id']; ?>" <?php echo (isset($form_data['line_id']) && $form_data['line_id'] == $line['id']) ? 'selected' : ''; ?>>
                            <?php echo e($line['line_number'] . ' - ' . $line['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['line_id'])): ?>
                    <div class="form-error"><?php echo e($errors['line_id']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="brigade_number">Numer brygady *</label>
                <input type="text" id="brigade_number" name="brigade_number" class="form-control" 
                       value="<?php echo e($form_data['brigade_number'] ?? ''); ?>" required>
                <?php if (!empty($errors['brigade_number'])): ?>
                    <div class="form-error"><?php echo e($errors['brigade_number']); ?></div>
                <?php endif; ?>
                <small class="form-text">Podaj tylko numer brygady, np. "1" dla kodu 107/1.</small>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" id="is_peak" name="is_peak" <?php echo !empty($form_data['is_peak']) ? 'checked' : ''; ?>>
                    Brygada szczytowa
                </label>
            </div>

            <div class="form-group" id="peak_type_group" style="display: <?php echo !empty($form_data['is_peak']) ? 'block' : 'none'; ?>;">
                <label for="peak_type">Typ brygady szczytowej</label>
                <select id="peak_type" name="peak_type" class="form-control">
                    <option value="">-- Wybierz typ --</option>
                    <option value="peak" <?php echo (isset($form_data['peak_type']) && $form_data['peak_type'] === 'peak') ? 'selected' : ''; ?>>Brygada szczytowa</option>
                    <option value="single_shift" <?php echo (isset($form_data['peak_type']) && $form_data['peak_type'] === 'single_shift') ? 'selected' : ''; ?>>Brygada jednozmianowa</option>
                </select>
                <?php if (!empty($errors['peak_type'])): ?>
                    <div class="form-error"><?php echo e($errors['peak_type']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="default_vehicle_type">Domyślny typ taboru</label>
                <select id="default_vehicle_type" name="default_vehicle_type" class="form-control">
                    <option value="">-- Brak preferencji --</option>
                    <option value="bus" <?php echo (isset($form_data['default_vehicle_type']) && $form_data['default_vehicle_type'] === 'bus') ? 'selected' : ''; ?>>Autobus</option>
                    <option value="articulated_bus" <?php echo (isset($form_data['default_vehicle_type']) && $form_data['default_vehicle_type'] === 'articulated_bus') ? 'selected' : ''; ?>>Autobus przegubowy</option>
                    <option value="tram" <?php echo (isset($form_data['default_vehicle_type']) && $form_data['default_vehicle_type'] === 'tram') ? 'selected' : ''; ?>>Tramwaj</option>
                    <option value="metro" <?php echo (isset($form_data['default_vehicle_type']) && $form_data['default_vehicle_type'] === 'metro') ? 'selected' : ''; ?>>Metro</option>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="shift_start">Godzina rozpoczęcia zmiany</label>
                    <input type="time" id="shift_start" name="shift_start" class="form-control"
                           value="<?php echo e($form_data['shift_start'] ?? ''); ?>">
                    <small class="form-text">np. 04:10</small>
                </div>
                <div class="form-group">
                    <label for="shift_end">Godzina zakończenia zmiany</label>
                    <input type="time" id="shift_end" name="shift_end" class="form-control"
                           value="<?php echo e($form_data['shift_end'] ?? ''); ?>">
                    <small class="form-text">np. 13:53</small>
                </div>
            </div>

            <div class="form-group">
                <label for="description">Opis</label>
                <textarea id="description" name="description" class="form-control" rows="3"><?php echo e($form_data['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="active" <?php echo isset($form_data['active']) ? 'checked' : ''; ?>>
                    Aktywna
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Zapisz</button>
                <a href="/admin/brigades/index.php" class="btn btn-secondary">Anuluj</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const peakCheckbox = document.getElementById('is_peak');
    const peakGroup = document.getElementById('peak_type_group');
    const peakSelect = document.getElementById('peak_type');

    const syncPeakType = function() {
        if (peakCheckbox.checked) {
            peakGroup.style.display = 'block';
        } else {
            peakGroup.style.display = 'none';
            peakSelect.value = '';
        }
    };

    peakCheckbox.addEventListener('change', syncPeakType);
    syncPeakType();
});
</script>

<?php View::partial('layouts/footer'); ?>
