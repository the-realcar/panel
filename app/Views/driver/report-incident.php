<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<h1>⚠️ Zgłoś Incydent</h1>
<p class="text-muted">Zgłoś awarię, wypadek lub inne zdarzenie</p>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Formularz zgłoszenia</h2>
    </div>
    <div class="card-body">
        <form method="POST" action="" class="form">
            <?php echo csrfField(); ?>

            <div class="row">
                <div class="col col-12 col-md-6">
                    <div class="form-group">
                        <label for="vehicle_id">Pojazd</label>
                        <select name="vehicle_id" id="vehicle_id" class="form-control">
                            <option value="">Nie dotyczy pojazdu</option>
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
                        <small class="text-muted">Wybierz pojazd, jeśli incydent dotyczy konkretnego pojazdu</small>
                    </div>
                </div>

                <div class="col col-12 col-md-6">
                    <div class="form-group">
                        <label for="incident_date">Data i godzina incydentu *</label>
                        <input type="datetime-local" name="incident_date" id="incident_date" class="form-control" 
                               value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col col-12 col-md-6">
                    <div class="form-group">
                        <label for="incident_type">Typ incydentu *</label>
                        <select name="incident_type" id="incident_type" class="form-control" required>
                            <option value="">Wybierz typ</option>
                            <option value="breakdown">Awaria techniczna</option>
                            <option value="accident">Wypadek</option>
                            <option value="complaint">Skarga pasażera</option>
                            <option value="other">Inne</option>
                        </select>
                    </div>
                </div>

                <div class="col col-12 col-md-6">
                    <div class="form-group">
                        <label for="severity">Poziom ważności *</label>
                        <select name="severity" id="severity" class="form-control" required>
                            <option value="">Wybierz poziom</option>
                            <option value="low">Niski</option>
                            <option value="medium">Średni</option>
                            <option value="high">Wysoki</option>
                            <option value="critical">Krytyczny</option>
                        </select>
                        <small class="text-muted">Określ wagę incydentu</small>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="title">Tytuł incydentu *</label>
                <input type="text" name="title" id="title" class="form-control" 
                       placeholder="Krótki opis problemu" maxlength="200" required>
                <small class="text-muted">Minimum 5 znaków</small>
            </div>

            <div class="form-group">
                <label for="description">Szczegółowy opis *</label>
                <textarea name="description" id="description" class="form-control" rows="6" 
                          placeholder="Opisz szczegółowo co się wydarzyło, kiedy i gdzie..." required></textarea>
                <small class="text-muted">Minimum 10 znaków. Im więcej szczegółów, tym szybsze rozwiązanie.</small>
            </div>

            <div class="alert alert-info">
                <strong>Wskazówka:</strong> W opisie incydentu podaj:
                <ul>
                    <li>Dokładną lokalizację zdarzenia</li>
                    <li>Godzinę zdarzenia</li>
                    <li>Warunki (pogoda, natężenie ruchu itp.)</li>
                    <li>Świadków (jeśli są)</li>
                    <li>Działania podjęte w związku z incydentem</li>
                </ul>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    Wyślij zgłoszenie
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
        <h2 class="card-title">Twoje ostatnie zgłoszenia</h2>
    </div>
    <div class="card-body">
        <?php if (empty($recent_incidents)): ?>
            <p class="text-muted">Brak zgłoszeń.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Pojazd</th>
                            <th>Typ</th>
                            <th>Ważność</th>
                            <th>Tytuł</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_incidents as $incident): ?>
                        <tr>
                            <td data-label="Data">
                                <?php echo formatDateTime($incident['incident_date'], 'd.m.Y H:i'); ?>
                            </td>
                            <td data-label="Pojazd">
                                <?php if ($incident['nr_poj']): ?>
                                    <?php echo e($incident['nr_poj']); ?>
                                    <?php if ($incident['model']): ?>
                                        <br><small class="text-muted"><?php echo e($incident['model']); ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">Brak</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Typ">
                                <?php 
                                $type_labels = [
                                    'breakdown' => 'Awaria',
                                    'accident' => 'Wypadek',
                                    'complaint' => 'Skarga',
                                    'other' => 'Inne'
                                ];
                                echo e($type_labels[$incident['incident_type']] ?? $incident['incident_type']);
                                ?>
                            </td>
                            <td data-label="Ważność">
                                <?php echo getSeverityBadge($incident['severity']); ?>
                            </td>
                            <td data-label="Tytuł">
                                <?php echo e($incident['title']); ?>
                            </td>
                            <td data-label="Status">
                                <?php echo getStatusBadge($incident['status']); ?>
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
