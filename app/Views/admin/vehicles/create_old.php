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

            <div class="form-row">
                <div class="form-group col col-12 col-md-6">
                    <label for="vehicle_number">Numer pojazdu *</label>
                    <input type="text" 
                           id="vehicle_number" 
                           name="vehicle_number" 
                           class="form-control <?php echo isset($errors['vehicle_number']) ? 'is-invalid' : ''; ?>"
                           value="<?php echo e($form_data['vehicle_number'] ?? ''); ?>"
                           required>
                    <?php if (isset($errors['vehicle_number'])): ?>
                        <div class="invalid-feedback"><?php echo e($errors['vehicle_number']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group col col-12 col-md-6">
                    <label for="registration_plate">Numer rejestracyjny</label>
                    <input type="text" 
                           id="registration_plate" 
                           name="registration_plate" 
                           class="form-control <?php echo isset($errors['registration_plate']) ? 'is-invalid' : ''; ?>"
                           value="<?php echo e($form_data['registration_plate'] ?? ''); ?>">
                    <?php if (isset($errors['registration_plate'])): ?>
                        <div class="invalid-feedback"><?php echo e($errors['registration_plate']); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col col-12 col-md-6">
                    <label for="vehicle_type">Typ pojazdu *</label>
                    <select id="vehicle_type" 
                            name="vehicle_type" 
                            class="form-control <?php echo isset($errors['vehicle_type']) ? 'is-invalid' : ''; ?>"
                            required>
                        <option value="">-- Wybierz typ --</option>
                        <option value="bus" <?php echo ($form_data['vehicle_type'] ?? '') === 'bus' ? 'selected' : ''; ?>>Autobus</option>
                        <option value="tram" <?php echo ($form_data['vehicle_type'] ?? '') === 'tram' ? 'selected' : ''; ?>>Tramwaj</option>
                        <option value="metro" <?php echo ($form_data['vehicle_type'] ?? '') === 'metro' ? 'selected' : ''; ?>>Metro</option>
                    </select>
                    <?php if (isset($errors['vehicle_type'])): ?>
                        <div class="invalid-feedback"><?php echo e($errors['vehicle_type']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group col col-12 col-md-6">
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
                    <label for="manufacture_year">Rok produkcji</label>
                    <input type="number" 
                           id="manufacture_year" 
                           name="manufacture_year" 
                           class="form-control <?php echo isset($errors['manufacture_year']) ? 'is-invalid' : ''; ?>"
                           value="<?php echo e($form_data['manufacture_year'] ?? ''); ?>"
                           min="1900"
                           max="<?php echo date('Y') + 1; ?>">
                    <?php if (isset($errors['manufacture_year'])): ?>
                        <div class="invalid-feedback"><?php echo e($errors['manufacture_year']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group col col-12 col-md-4">
                    <label for="capacity">Pojemność (liczba miejsc)</label>
                    <input type="number" 
                           id="capacity" 
                           name="capacity" 
                           class="form-control <?php echo isset($errors['capacity']) ? 'is-invalid' : ''; ?>"
                           value="<?php echo e($form_data['capacity'] ?? ''); ?>"
                           min="1">
                    <?php if (isset($errors['capacity'])): ?>
                        <div class="invalid-feedback"><?php echo e($errors['capacity']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group col col-12 col-md-4">
                    <label for="last_inspection">Data ostatniego przeglądu</label>
                    <input type="date" 
                           id="last_inspection" 
                           name="last_inspection" 
                           class="form-control <?php echo isset($errors['last_inspection']) ? 'is-invalid' : ''; ?>"
                           value="<?php echo e($form_data['last_inspection'] ?? ''); ?>">
                    <?php if (isset($errors['last_inspection'])): ?>
                        <div class="invalid-feedback"><?php echo e($errors['last_inspection']); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="status">Status *</label>
                <select id="status" 
                        name="status" 
                        class="form-control <?php echo isset($errors['status']) ? 'is-invalid' : ''; ?>"
                        required>
                    <option value="available" <?php echo ($form_data['status'] ?? 'available') === 'available' ? 'selected' : ''; ?>>Dostępny</option>
                    <option value="in_use" <?php echo ($form_data['status'] ?? '') === 'in_use' ? 'selected' : ''; ?>>W użyciu</option>
                    <option value="maintenance" <?php echo ($form_data['status'] ?? '') === 'maintenance' ? 'selected' : ''; ?>>Serwis</option>
                    <option value="broken" <?php echo ($form_data['status'] ?? '') === 'broken' ? 'selected' : ''; ?>>Awaria</option>
                </select>
                <?php if (isset($errors['status'])): ?>
                    <div class="invalid-feedback"><?php echo e($errors['status']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Zapisz pojazd</button>
                <a href="/admin/vehicles/index.php" class="btn btn-secondary">Anuluj</a>
            </div>
        </form>
    </div>
</div>

<?php View::partial('layouts/footer'); ?>
