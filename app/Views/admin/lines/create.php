<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>➕ Dodaj linię</h1>
    <a href="/admin/lines/index.php" class="btn btn-secondary">← Powrót do listy</a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error"><?php echo e($errors['general']); ?></div>
        <?php endif; ?>

        <form method="POST" action="/admin/lines/create.php">
            <?php echo csrfField(); ?>

            <div class="form-row">
                <div class="form-group col col-12 col-md-6">
                    <label for="line_number">Numer linii *</label>
                    <input type="text" 
                           id="line_number" 
                           name="line_number" 
                           class="form-control <?php echo isset($errors['line_number']) ? 'is-invalid' : ''; ?>"
                           value="<?php echo e($form_data['line_number'] ?? ''); ?>"
                           required>
                    <?php if (isset($errors['line_number'])): ?>
                        <div class="invalid-feedback"><?php echo e($errors['line_number']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group col col-12 col-md-6">
                    <label for="name">Nazwa *</label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>"
                           value="<?php echo e($form_data['name'] ?? ''); ?>"
                           required>
                    <?php if (isset($errors['name'])): ?>
                        <div class="invalid-feedback"><?php echo e($errors['name']); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="line_type">Typ linii *</label>
                <select id="line_type" 
                        name="line_type" 
                        class="form-control <?php echo isset($errors['line_type']) ? 'is-invalid' : ''; ?>"
                        required>
                    <option value="">-- Wybierz typ --</option>
                    <option value="bus" <?php echo ($form_data['line_type'] ?? 'bus') === 'bus' ? 'selected' : ''; ?>>Autobus</option>
                    <option value="tram" <?php echo ($form_data['line_type'] ?? '') === 'tram' ? 'selected' : ''; ?>>Tramwaj</option>
                    <option value="metro" <?php echo ($form_data['line_type'] ?? '') === 'metro' ? 'selected' : ''; ?>>Metro</option>
                </select>
                <?php if (isset($errors['line_type'])): ?>
                    <div class="invalid-feedback"><?php echo e($errors['line_type']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="route_description">Opis trasy</label>
                <textarea id="route_description" 
                          name="route_description" 
                          class="form-control"
                          rows="4"><?php echo e($form_data['route_description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" 
                           name="active" 
                           <?php echo isset($form_data['active']) ? 'checked' : ''; ?>>
                    Aktywna
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Zapisz linię</button>
                <a href="/admin/lines/index.php" class="btn btn-secondary">Anuluj</a>
            </div>
        </form>
    </div>
</div>

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-body">
        <h2>Masowy import linii z JSON</h2>
        <p class="text-muted">Kazdy rekord powinien zawierac pola <strong>Line</strong>, <strong>Destination</strong> i <strong>Stops</strong>. Import tworzy brakujace linie, warianty tras, przystanki i domyslne stanowiska 01.</p>

        <form method="POST" action="/admin/lines/create.php">
            <?php echo csrfField(); ?>
            <input type="hidden" name="import_mode" value="bulk_json">

            <div class="form-row">
                <div class="form-group col col-12 col-md-6">
                    <label for="bulk_line_type">Typ linii dla nowych rekordow *</label>
                    <select id="bulk_line_type"
                            name="bulk_line_type"
                            class="form-control <?php echo isset($errors['bulk_line_type']) ? 'is-invalid' : ''; ?>"
                            required>
                        <option value="bus" <?php echo ($form_data['bulk_line_type'] ?? 'bus') === 'bus' ? 'selected' : ''; ?>>Autobus</option>
                        <option value="tram" <?php echo ($form_data['bulk_line_type'] ?? '') === 'tram' ? 'selected' : ''; ?>>Tramwaj</option>
                        <option value="metro" <?php echo ($form_data['bulk_line_type'] ?? '') === 'metro' ? 'selected' : ''; ?>>Metro</option>
                    </select>
                    <?php if (isset($errors['bulk_line_type'])): ?>
                        <div class="invalid-feedback"><?php echo e($errors['bulk_line_type']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group col col-12 col-md-6">
                    <label class="checkbox-label" style="margin-top: 2rem;">
                        <input type="checkbox" name="bulk_active" <?php echo isset($form_data['bulk_active']) ? 'checked' : ''; ?>>
                        Oznacz nowe linie jako aktywne
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="bulk_json">Dane JSON *</label>
                <textarea id="bulk_json"
                          name="bulk_json"
                          class="form-control <?php echo isset($errors['bulk_json']) ? 'is-invalid' : ''; ?>"
                          rows="10"
                          placeholder='{"Line":"52","Destination":"Rzędzin Pętla","Stops":["Rondo Czyżyńskie","Chopina"]};{"Line":"52","Destination":"Rondo Czyżyńskie","Stops":["Rzędzin Pętla","Rzędzin"]}'><?php echo e($form_data['bulk_json'] ?? ''); ?></textarea>
                <?php if (isset($errors['bulk_json'])): ?>
                    <div class="invalid-feedback"><?php echo e($errors['bulk_json']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">📥 Importuj linie</button>
            </div>
        </form>
    </div>
</div>

<?php View::partial('layouts/footer'); ?>
