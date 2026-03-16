<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>✏️ Edytuj brygadę</h1>
    <a href="/admin/brigades/index.php" class="btn btn-secondary">⬅️ Powrót</a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error"><?php echo e($errors['general']); ?></div>
        <?php endif; ?>

        <form method="POST" class="form">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

            <?php
                $departure_times = $form_data['departure_time'] ?? [''];
                $departure_directions = $form_data['departure_direction'] ?? [''];
                $departure_rows = max(count($departure_times), count($departure_directions), 1);
            ?>

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

            <div class="form-group">
                <label for="przewoznik">Spółka przewozowa</label>
                <select id="przewoznik" name="przewoznik" class="form-control">
                    <option value="">-- Brak przypisania --</option>
                    <option value="Ostrans" <?php echo (isset($form_data['przewoznik']) && $form_data['przewoznik'] === 'Ostrans') ? 'selected' : ''; ?>>Ostrans</option>
                    <option value="KujaTrans" <?php echo (isset($form_data['przewoznik']) && $form_data['przewoznik'] === 'KujaTrans') ? 'selected' : ''; ?>>KujaTrans</option>
                    <option value="Ostromunikacja" <?php echo (isset($form_data['przewoznik']) && $form_data['przewoznik'] === 'Ostromunikacja') ? 'selected' : ''; ?>>Ostromunikacja</option>
                </select>
                <?php if (!empty($errors['przewoznik'])): ?>
                    <div class="form-error"><?php echo e($errors['przewoznik']); ?></div>
                <?php endif; ?>
            </div>

            <fieldset class="form-group" style="border: 1px solid #ddd; border-radius: 4px; padding: 0.75rem 1rem;">
                <legend style="font-weight: 600; font-size: 0.9rem; padding: 0 0.4rem;">Zmiana A</legend>
                <div class="form-row">
                    <div class="form-group">
                        <label for="shift_a_start">Godzina rozpoczęcia</label>
                        <input type="time" id="shift_a_start" name="shift_a_start" class="form-control"
                               value="<?php echo e($form_data['shift_a_start'] ?? ''); ?>">
                        <small class="form-text">np. 04:10</small>
                    </div>
                    <div class="form-group">
                        <label for="shift_a_end">Godzina zakończenia</label>
                        <input type="time" id="shift_a_end" name="shift_a_end" class="form-control"
                               value="<?php echo e($form_data['shift_a_end'] ?? ''); ?>">
                        <small class="form-text">np. 13:53</small>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="shift_a_first_stop">1. Przystanek</label>
                        <input type="text" id="shift_a_first_stop" name="shift_a_first_stop" class="form-control" maxlength="100"
                               value="<?php echo e($form_data['shift_a_first_stop'] ?? ''); ?>"
                               placeholder="np. Salwator">
                    </div>
                    <div class="form-group">
                        <label for="shift_a_last_stop">Przystanek końcowy</label>
                        <input type="text" id="shift_a_last_stop" name="shift_a_last_stop" class="form-control" maxlength="100"
                               value="<?php echo e($form_data['shift_a_last_stop'] ?? ''); ?>"
                               placeholder="np. Salwator">
                    </div>
                    <div class="form-group">
                        <label for="shift_a_capacity">Pojemność</label>
                        <select id="shift_a_capacity" name="shift_a_capacity" class="form-control">
                            <option value="">-- Brak --</option>
                            <?php foreach (['MIDI','MAXI','MEGA','MEGA/MAXI+','MAXI/MAXI+'] as $cap): ?>
                                <option value="<?php echo $cap; ?>" <?php echo (isset($form_data['shift_a_capacity']) && $form_data['shift_a_capacity'] === $cap) ? 'selected' : ''; ?>><?php echo $cap; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </fieldset>

            <fieldset class="form-group" style="border: 1px solid #ddd; border-radius: 4px; padding: 0.75rem 1rem;">
                <legend style="font-weight: 600; font-size: 0.9rem; padding: 0 0.4rem;">Zmiana B</legend>
                <div class="form-row">
                    <div class="form-group">
                        <label for="shift_b_start">Godzina rozpoczęcia</label>
                        <input type="time" id="shift_b_start" name="shift_b_start" class="form-control"
                               value="<?php echo e($form_data['shift_b_start'] ?? ''); ?>">
                        <small class="form-text">np. 14:00</small>
                    </div>
                    <div class="form-group">
                        <label for="shift_b_end">Godzina zakończenia</label>
                        <input type="time" id="shift_b_end" name="shift_b_end" class="form-control"
                               value="<?php echo e($form_data['shift_b_end'] ?? ''); ?>">
                        <small class="form-text">np. 22:30</small>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="shift_b_first_stop">1. Przystanek</label>
                        <input type="text" id="shift_b_first_stop" name="shift_b_first_stop" class="form-control" maxlength="100"
                               value="<?php echo e($form_data['shift_b_first_stop'] ?? ''); ?>"
                               placeholder="np. Salwator">
                    </div>
                    <div class="form-group">
                        <label for="shift_b_last_stop">Przystanek końcowy</label>
                        <input type="text" id="shift_b_last_stop" name="shift_b_last_stop" class="form-control" maxlength="100"
                               value="<?php echo e($form_data['shift_b_last_stop'] ?? ''); ?>"
                               placeholder="np. Salwator">
                    </div>
                    <div class="form-group">
                        <label for="shift_b_capacity">Pojemność</label>
                        <select id="shift_b_capacity" name="shift_b_capacity" class="form-control">
                            <option value="">-- Brak --</option>
                            <?php foreach (['MIDI','MAXI','MEGA','MEGA/MAXI+','MAXI/MAXI+'] as $cap): ?>
                                <option value="<?php echo $cap; ?>" <?php echo (isset($form_data['shift_b_capacity']) && $form_data['shift_b_capacity'] === $cap) ? 'selected' : ''; ?>><?php echo $cap; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </fieldset>

            <div class="form-group">
                <label>Odjazdy i kierunki</label>
                <?php if (!empty($errors['departures'])): ?>
                    <div class="form-error"><?php echo e($errors['departures']); ?></div>
                <?php endif; ?>
                <div id="departures-list" class="form-group" style="display: grid; gap: 0.5rem;">
                    <?php for ($i = 0; $i < $departure_rows; $i++): ?>
                        <div class="form-row departure-row" style="align-items: end; gap: 0.5rem;">
                            <div class="form-group" style="flex: 0 0 170px;">
                                <label>Godzina</label>
                                <input type="time" name="departure_time[]" class="form-control"
                                       value="<?php echo e($departure_times[$i] ?? ''); ?>">
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label>Kierunek</label>
                                <input type="text" name="departure_direction[]" class="form-control" maxlength="120"
                                       placeholder="np. Dworzec Główny"
                                       value="<?php echo e($departure_directions[$i] ?? ''); ?>">
                            </div>
                            <button type="button" class="btn btn-secondary remove-departure">Usuń</button>
                        </div>
                    <?php endfor; ?>
                </div>
                <button type="button" id="add-departure" class="btn btn-secondary">+ Dodaj odjazd</button>
                <small class="form-text">Dodaj tyle odjazdow, ile potrzeba. Kazdy wiersz powinien miec godzine i kierunek.</small>
            </div>

            <div class="form-group">
                <label for="description">Opis</label>
                <textarea id="description" name="description" class="form-control" rows="3"><?php echo e($form_data['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="active" <?php echo !empty($form_data['active']) && $form_data['active'] ? 'checked' : ''; ?>>
                    Aktywna
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Zapisz zmiany</button>
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
    const departuresList = document.getElementById('departures-list');
    const addDepartureButton = document.getElementById('add-departure');

    const bindRemoveButtons = function() {
        departuresList.querySelectorAll('.remove-departure').forEach(function(button) {
            button.onclick = function() {
                const rows = departuresList.querySelectorAll('.departure-row');
                const row = button.closest('.departure-row');

                if (rows.length <= 1) {
                    const timeInput = row.querySelector('input[name="departure_time[]"]');
                    const directionInput = row.querySelector('input[name="departure_direction[]"]');
                    if (timeInput) {
                        timeInput.value = '';
                    }
                    if (directionInput) {
                        directionInput.value = '';
                    }
                    return;
                }

                row.remove();
            };
        });
    };

    const syncPeakType = function() {
        if (peakCheckbox.checked) {
            peakGroup.style.display = 'block';
        } else {
            peakGroup.style.display = 'none';
            peakSelect.value = '';
        }
    };

    addDepartureButton.addEventListener('click', function() {
        const row = document.createElement('div');
        row.className = 'form-row departure-row';
        row.style.alignItems = 'end';
        row.style.gap = '0.5rem';
        row.innerHTML =
            '<div class="form-group" style="flex: 0 0 170px;">' +
                '<label>Godzina</label>' +
                '<input type="time" name="departure_time[]" class="form-control" value="">' +
            '</div>' +
            '<div class="form-group" style="flex: 1;">' +
                '<label>Kierunek</label>' +
                '<input type="text" name="departure_direction[]" class="form-control" maxlength="120" placeholder="np. Dworzec Główny" value="">' +
            '</div>' +
            '<button type="button" class="btn btn-secondary remove-departure">Usuń</button>';
        departuresList.appendChild(row);
        bindRemoveButtons();
    });

    peakCheckbox.addEventListener('change', syncPeakType);
    bindRemoveButtons();
    syncPeakType();
});
</script>

<?php View::partial('layouts/footer'); ?>
