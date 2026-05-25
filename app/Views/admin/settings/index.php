<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>⚙️ Ustawienia systemowe</h1>
    <a href="/admin/dashboard.php" class="btn btn-secondary">← Powrot do dashboardu</a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!$settings_available): ?>
            <div class="alert alert-warning">
                Tabela <code>settings</code> nie jest dostępna w aktualnej bazie danych. Formularz pozostaje widoczny wyłącznie informacyjnie i zapis jest zablokowany do czasu synchronizacji schematu.
            </div>
        <?php endif; ?>

        <p class="text-muted">
            Zmiany zapisywane sa w tabeli <code>settings</code> i logowane w audycie.
        </p>

        <form method="POST" action="/admin/settings/index.php">
            <?php echo csrfField(); ?>

            <div class="form-group">
                <label for="company_name" class="form-label">Nazwa firmy</label>
                <input
                    type="text"
                    id="company_name"
                    name="company_name"
                    class="form-control"
                    value="<?php echo e($form['company_name']); ?>"
                    required
                >
                <?php if (!empty($errors['company_name'])): ?>
                    <div class="form-error"><?php echo e($errors['company_name']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="base_url" class="form-label">BASE URL</label>
                <input
                    type="url"
                    id="base_url"
                    name="base_url"
                    class="form-control"
                    value="<?php echo e($form['base_url']); ?>"
                    required
                >
                <?php if (!empty($errors['base_url'])): ?>
                    <div class="form-error"><?php echo e($errors['base_url']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="support_email" class="form-label">Email wsparcia</label>
                <input
                    type="email"
                    id="support_email"
                    name="support_email"
                    class="form-control"
                    value="<?php echo e($form['support_email']); ?>"
                    placeholder="admin@firmakot.pl"
                >
                <?php if (!empty($errors['support_email'])): ?>
                    <div class="form-error"><?php echo e($errors['support_email']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="session_timeout" class="form-label">Timeout sesji (sekundy)</label>
                <input
                    type="number"
                    id="session_timeout"
                    name="session_timeout"
                    class="form-control"
                    min="300"
                    max="86400"
                    step="1"
                    value="<?php echo e($form['session_timeout']); ?>"
                    required
                >
                <?php if (!empty($errors['session_timeout'])): ?>
                    <div class="form-error"><?php echo e($errors['session_timeout']); ?></div>
                <?php endif; ?>
            </div>

            <h3 style="margin-top: 1.25rem;">Slowniki systemowe</h3>
            <p class="text-muted">Podawaj wartosci w osobnych liniach.</p>

            <div class="form-row">
                <div class="form-group col col-12 col-md-6">
                    <label for="dict_vehicle_types" class="form-label">Typy pojazdow</label>
                    <textarea id="dict_vehicle_types" name="dict_vehicle_types" class="form-control" rows="5"><?php echo e($form['dict_vehicle_types']); ?></textarea>
                </div>
                <div class="form-group col col-12 col-md-6">
                    <label for="dict_vehicle_capacities" class="form-label">Pojemnosci pojazdow</label>
                    <textarea id="dict_vehicle_capacities" name="dict_vehicle_capacities" class="form-control" rows="5"><?php echo e($form['dict_vehicle_capacities']); ?></textarea>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col col-12 col-md-6">
                    <label for="dict_vehicle_drive_types" class="form-label">Typy napedu</label>
                    <textarea id="dict_vehicle_drive_types" name="dict_vehicle_drive_types" class="form-control" rows="5"><?php echo e($form['dict_vehicle_drive_types']); ?></textarea>
                </div>
                <div class="form-group col col-12 col-md-6">
                    <label for="dict_vehicle_depots" class="form-label">Zajezdnie</label>
                    <textarea id="dict_vehicle_depots" name="dict_vehicle_depots" class="form-control" rows="5"><?php echo e($form['dict_vehicle_depots']); ?></textarea>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col col-12 col-md-6">
                    <label for="dict_vehicle_carriers" class="form-label">Przewoznicy</label>
                    <textarea id="dict_vehicle_carriers" name="dict_vehicle_carriers" class="form-control" rows="5"><?php echo e($form['dict_vehicle_carriers']); ?></textarea>
                </div>
                <div class="form-group col col-12 col-md-6">
                    <label for="dict_departments" class="form-label">Dzialy (slownik)</label>
                    <textarea id="dict_departments" name="dict_departments" class="form-control" rows="5"><?php echo e($form['dict_departments']); ?></textarea>
                </div>
            </div>

            <div class="form-group">
                <label for="dict_districts" class="form-label">Dzielnice (slownik)</label>
                <textarea id="dict_districts" name="dict_districts" class="form-control" rows="5"><?php echo e($form['dict_districts']); ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary" <?php echo !$settings_available ? 'disabled' : ''; ?>>Zapisz ustawienia</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Aktualne wpisy w settings</h2>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Klucz</th>
                        <th>Wartosc</th>
                        <th>Opis</th>
                        <th>Aktualizacja</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_settings as $setting): ?>
                        <tr>
                            <td data-label="Klucz"><code><?php echo e($setting['key']); ?></code></td>
                            <td data-label="Wartosc"><?php echo e((string)$setting['value']); ?></td>
                            <td data-label="Opis"><?php echo e((string)($setting['description'] ?? '')); ?></td>
                            <td data-label="Aktualizacja"><?php echo e((string)($setting['updated_at'] ?? '')); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php View::partial('layouts/footer'); ?>
