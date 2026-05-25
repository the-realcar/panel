<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<?php
$vehicleTypeLabels = [
    'bus' => 'Autobus',
    'tbus' => 'Trolejbus',
    'tram' => 'Tramwaj',
    'metro' => 'Metro'
];
?>

<div class="page-header">
    <h1>✏️ Edytuj pojazd</h1>
    <a href="/admin/vehicles/index.php" class="btn btn-secondary">← Powrót do listy</a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error"><?php echo e($errors['general']); ?></div>
        <?php endif; ?>

        <form method="POST" action="/admin/vehicles/edit.php?id=<?php echo $vehicle_id; ?>">
            <?php echo csrfField(); ?>

            <h3>Podstawowe informacje</h3>
            <div class="form-row">
                <div class="form-group col col-12 col-md-4">
                    <label for="nr_poj">Numer pojazdu *</label>
                    <input type="text" 
                           id="nr_poj" 
                           name="nr_poj" 
                           class="form-control <?php echo isset($errors['nr_poj']) ? 'is-invalid' : ''; ?>"
                           value="<?php echo e($form_data['nr_poj'] ?? ''); ?>"
                           required>
                    <?php if (isset($errors['nr_poj'])): ?>
                        <div class="invalid-feedback"><?php echo e($errors['nr_poj']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group col col-12 col-md-4">
                    <label for="reg_plate">Numer rejestracyjny</label>
                    <input type="text" 
                           id="reg_plate" 
                           name="reg_plate" 
                           class="form-control <?php echo isset($errors['reg_plate']) ? 'is-invalid' : ''; ?>"
                           value="<?php echo e($form_data['reg_plate'] ?? ''); ?>">
                    <?php if (isset($errors['reg_plate'])): ?>
                        <div class="invalid-feedback"><?php echo e($errors['reg_plate']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group col col-12 col-md-4">
                    <label for="status">Status *</label>
                    <select id="status" 
                            name="status" 
                            class="form-control <?php echo isset($errors['status']) ? 'is-invalid' : ''; ?>"
                            required>
                        <option value="sprawny" <?php echo ($form_data['status'] ?? 'sprawny') === 'sprawny' ? 'selected' : ''; ?>>Sprawny</option>
                        <option value="w naprawie" <?php echo ($form_data['status'] ?? '') === 'w naprawie' ? 'selected' : ''; ?>>W naprawie</option>
                        <option value="odstawiony" <?php echo ($form_data['status'] ?? '') === 'odstawiony' ? 'selected' : ''; ?>>Odstawiony</option>
                        <option value="zawieszony" <?php echo ($form_data['status'] ?? '') === 'zawieszony' ? 'selected' : ''; ?>>Zawieszony</option>
                    </select>
                    <?php if (isset($errors['status'])): ?>
                        <div class="invalid-feedback"><?php echo e($errors['status']); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col col-12 col-md-4">
                    <label for="vehicle_type">Typ pojazdu *</label>
                    <select id="vehicle_type" 
                            name="vehicle_type" 
                            class="form-control <?php echo isset($errors['vehicle_type']) ? 'is-invalid' : ''; ?>"
                            required>
                        <option value="">-- Wybierz typ --</option>
                        <?php foreach (($dict['vehicle_types'] ?? []) as $type): ?>
                            <option value="<?php echo e($type); ?>" <?php echo ($form_data['vehicle_type'] ?? '') === $type ? 'selected' : ''; ?>><?php echo e($vehicleTypeLabels[$type] ?? $type); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['vehicle_type'])): ?>
                        <div class="invalid-feedback"><?php echo e($errors['vehicle_type']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group col col-12 col-md-4">
                    <label for="marka">Marka</label>
                    <input type="text" 
                           id="marka" 
                           name="marka" 
                           class="form-control"
                           maxlength="25"
                           value="<?php echo e($form_data['marka'] ?? ''); ?>">
                </div>

                <div class="form-group col col-12 col-md-4">
                    <label for="model">Model</label>
                    <input type="text" 
                           id="model" 
                           name="model" 
                           class="form-control"
                           value="<?php echo e($form_data['model'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col col-12 col-md-4">
                    <label for="rok_prod">Rok produkcji</label>
                    <input type="number" 
                           id="rok_prod" 
                           name="rok_prod" 
                           class="form-control <?php echo isset($errors['rok_prod']) ? 'is-invalid' : ''; ?>"
                           value="<?php echo e($form_data['rok_prod'] ?? ''); ?>"
                           min="1900"
                           max="<?php echo date('Y') + 1; ?>">
                    <?php if (isset($errors['rok_prod'])): ?>
                        <div class="invalid-feedback"><?php echo e($errors['rok_prod']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group col col-12 col-md-4">
                    <label for="pojemnosc">Pojemność</label>
                    <select id="pojemnosc" 
                            name="pojemnosc" 
                            class="form-control">
                        <option value="">-- Wybierz pojemność --</option>
                        <?php foreach (($dict['vehicle_capacities'] ?? []) as $capacity): ?>
                            <option value="<?php echo e($capacity); ?>" <?php echo ($form_data['pojemnosc'] ?? '') === $capacity ? 'selected' : ''; ?>><?php echo e($capacity); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group col col-12 col-md-4">
                    <label for="pulpit">Pulpit</label>
                    <input type="text" 
                           id="pulpit" 
                           name="pulpit" 
                           class="form-control"
                           maxlength="25"
                           value="<?php echo e($form_data['pulpit'] ?? ''); ?>">
                </div>
            </div>

            <h3 class="mt-4">Napęd i wyposażenie</h3>
            <div class="form-row">
                <div class="form-group col col-12 col-md-4">
                    <label for="typ_napedu">Typ napędu</label>
                    <select id="typ_napedu" 
                            name="typ_napedu" 
                            class="form-control">
                        <option value="">-- Wybierz typ napędu --</option>
                        <?php foreach (($dict['vehicle_drive_types'] ?? []) as $drive): ?>
                            <option value="<?php echo e($drive); ?>" <?php echo ($form_data['typ_napedu'] ?? '') === $drive ? 'selected' : ''; ?>><?php echo e($drive); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group col col-12 col-md-4">
                    <label for="engine">Silnik</label>
                    <input type="text" 
                           id="engine" 
                           name="engine" 
                           class="form-control"
                           maxlength="50"
                           value="<?php echo e($form_data['engine'] ?? ''); ?>">
                </div>

                <div class="form-group col col-12 col-md-4">
                    <label for="gearbox">Skrzynia biegów</label>
                    <input type="text" 
                           id="gearbox" 
                           name="gearbox" 
                           class="form-control"
                           maxlength="50"
                           value="<?php echo e($form_data['gearbox'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col col-12 col-md-4">
                    <label for="norma_spalin">Norma spalin</label>
                    <select id="norma_spalin" name="norma_spalin" class="form-control">
                        <option value="">-- Wybierz normę --</option>
                        <?php foreach (($dict['vehicle_emission_standards'] ?? []) as $standard): ?>
                            <option value="<?php echo e($standard); ?>" <?php echo ($form_data['norma_spalin'] ?? ($form_data['norma_spalania'] ?? '')) === $standard ? 'selected' : ''; ?>><?php echo e($standard); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group col col-12 col-md-4">
                    <label for="klimatyzacja">
                        <input type="checkbox" 
                               id="klimatyzacja" 
                               name="klimatyzacja" 
                               value="1"
                               <?php echo isset($form_data['klimatyzacja']) && $form_data['klimatyzacja'] ? 'checked' : ''; ?>>
                        Klimatyzacja
                    </label>
                </div>
            </div>

            <h3 class="mt-4">Przypisanie</h3>
            <div class="form-row">
                <div class="form-group col col-12 col-md-4">
                    <label for="zajezdnia">Zajezdnia</label>
                    <select id="zajezdnia" 
                            name="zajezdnia" 
                            class="form-control">
                        <option value="">-- Wybierz zajezdnię --</option>
                        <?php foreach (($dict['vehicle_depots'] ?? []) as $depot): ?>
                            <option value="<?php echo e($depot); ?>" <?php echo ($form_data['zajezdnia'] ?? '') === $depot ? 'selected' : ''; ?>><?php echo e($depot); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group col col-12 col-md-4">
                    <label for="przewoznik">Przewoźnik</label>
                    <select id="przewoznik" 
                            name="przewoznik" 
                            class="form-control">
                        <option value="">-- Wybierz przewoźnika --</option>
                        <?php foreach (($dict['vehicle_carriers'] ?? []) as $carrier): ?>
                            <option value="<?php echo e($carrier); ?>" <?php echo ($form_data['przewoznik'] ?? '') === $carrier ? 'selected' : ''; ?>><?php echo e($carrier); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col col-12 col-md-6">
                    <label for="opiekun_1">Opiekun 1 (nr pracownika)</label>
                    <input type="text" 
                           id="opiekun_1" 
                           name="opiekun_1" 
                           class="form-control"
                           maxlength="20"
                           value="<?php echo e($form_data['opiekun_1'] ?? ''); ?>">
                </div>

                <div class="form-group col col-12 col-md-6">
                    <label for="opiekun_2">Opiekun 2 (nr pracownika)</label>
                    <input type="text" 
                           id="opiekun_2" 
                           name="opiekun_2" 
                           class="form-control"
                           maxlength="20"
                           value="<?php echo e($form_data['opiekun_2'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="notes">Uwagi</label>
                <textarea id="notes" 
                          name="notes" 
                          class="form-control"
                          rows="3"
                          maxlength="500"><?php echo e($form_data['notes'] ?? ($form_data['dodatkowe_informacje'] ?? '')); ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Zapisz zmiany</button>
                <a href="/admin/vehicles/index.php" class="btn btn-secondary">Anuluj</a>
            </div>
        </form>
    </div>
</div>

<?php View::partial('layouts/footer'); ?>
