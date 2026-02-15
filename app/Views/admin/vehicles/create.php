<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>➕ Dodaj pojazd</h1>
    <a href="/admin/vehicles/index.php" class="btn btn-secondary">← Powrót do listy</a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error"><?php echo e($errors['general']); ?></div>
        <?php endif; ?>

        <form method="POST" action="/admin/vehicles/create.php">
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
                        <option value="bus" <?php echo ($form_data['vehicle_type'] ?? '') === 'bus' ? 'selected' : ''; ?>>Autobus</option>
                        <option value="tbus" <?php echo ($form_data['vehicle_type'] ?? '') === 'tbus' ? 'selected' : ''; ?>>Trolejbus</option>
                        <option value="tram" <?php echo ($form_data['vehicle_type'] ?? '') === 'tram' ? 'selected' : ''; ?>>Tramwaj</option>
                        <option value="metro" <?php echo ($form_data['vehicle_type'] ?? '') === 'metro' ? 'selected' : ''; ?>>Metro</option>
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
                        <option value="MINI" <?php echo ($form_data['pojemnosc'] ?? '') === 'MINI' ? 'selected' : ''; ?>>MINI</option>
                        <option value="MIDI" <?php echo ($form_data['pojemnosc'] ?? '') === 'MIDI' ? 'selected' : ''; ?>>MIDI</option>
                        <option value="MAXI" <?php echo ($form_data['pojemnosc'] ?? '') === 'MAXI' ? 'selected' : ''; ?>>MAXI</option>
                        <option value="MAXI+" <?php echo ($form_data['pojemnosc'] ?? '') === 'MAXI+' ? 'selected' : ''; ?>>MAXI+</option>
                        <option value="MEGA" <?php echo ($form_data['pojemnosc'] ?? '') === 'MEGA' ? 'selected' : ''; ?>>MEGA</option>
                        <option value="MEGA+" <?php echo ($form_data['pojemnosc'] ?? '') === 'MEGA+' ? 'selected' : ''; ?>>MEGA+</option>
                        <option value="GIGA" <?php echo ($form_data['pojemnosc'] ?? '') === 'GIGA' ? 'selected' : ''; ?>>GIGA</option>
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
                        <option value="Diesel" <?php echo ($form_data['typ_napedu'] ?? '') === 'Diesel' ? 'selected' : ''; ?>>Diesel</option>
                        <option value="CNG" <?php echo ($form_data['typ_napedu'] ?? '') === 'CNG' ? 'selected' : ''; ?>>CNG</option>
                        <option value="Hybrydowy" <?php echo ($form_data['typ_napedu'] ?? '') === 'Hybrydowy' ? 'selected' : ''; ?>>Hybrydowy</option>
                        <option value="Elektryczny" <?php echo ($form_data['typ_napedu'] ?? '') === 'Elektryczny' ? 'selected' : ''; ?>>Elektryczny</option>
                        <option value="Wodorowy" <?php echo ($form_data['typ_napedu'] ?? '') === 'Wodorowy' ? 'selected' : ''; ?>>Wodorowy</option>
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
                    <label for="norma_spalania">Norma spalania</label>
                    <input type="text" 
                           id="norma_spalania" 
                           name="norma_spalania" 
                           class="form-control"
                           maxlength="10"
                           value="<?php echo e($form_data['norma_spalania'] ?? ''); ?>">
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
                        <option value="KM" <?php echo ($form_data['zajezdnia'] ?? '') === 'KM' ? 'selected' : ''; ?>>KM</option>
                        <option value="KW" <?php echo ($form_data['zajezdnia'] ?? '') === 'KW' ? 'selected' : ''; ?>>KW</option>
                        <option value="MC" <?php echo ($form_data['zajezdnia'] ?? '') === 'MC' ? 'selected' : ''; ?>>MC</option>
                    </select>
                </div>

                <div class="form-group col col-12 col-md-4">
                    <label for="przewoznik">Przewoźnik</label>
                    <select id="przewoznik" 
                            name="przewoznik" 
                            class="form-control">
                        <option value="">-- Wybierz przewoźnika --</option>
                        <option value="Ostrans" <?php echo ($form_data['przewoznik'] ?? '') === 'Ostrans' ? 'selected' : ''; ?>>Ostrans</option>
                        <option value="KujaTrans" <?php echo ($form_data['przewoznik'] ?? '') === 'KujaTrans' ? 'selected' : ''; ?>>KujaTrans</option>
                        <option value="Ostromunikacja" <?php echo ($form_data['przewoznik'] ?? '') === 'Ostromunikacja' ? 'selected' : ''; ?>>Ostromunikacja</option>
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
                <label for="dodatkowe_informacje">Dodatkowe informacje</label>
                <textarea id="dodatkowe_informacje" 
                          name="dodatkowe_informacje" 
                          class="form-control"
                          rows="3"
                          maxlength="100"><?php echo e($form_data['dodatkowe_informacje'] ?? ''); ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Zapisz pojazd</button>
                <a href="/admin/vehicles/index.php" class="btn btn-secondary">Anuluj</a>
            </div>
        </form>
    </div>
</div>

<?php View::partial('layouts/footer'); ?>
