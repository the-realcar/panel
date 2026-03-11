<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>⚠️ Zgłoszenie #<?php echo (int)$incident['id']; ?></h1>
    <a href="/admin/incidents/index.php" class="btn btn-secondary">← Powrót do listy</a>
</div>

<div class="row" style="align-items: flex-start; gap: 1.5rem;">
    <!-- Szczegóły -->
    <div class="col col-12 col-md-6">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Szczegóły zgłoszenia</h2>
            </div>
            <div class="card-body">
                <table class="table">
                    <tbody>
                        <tr>
                            <th style="width:40%">Zgłaszający</th>
                            <td>
                                <?php echo e(trim(($incident['reporter_first'] ?? '') . ' ' . ($incident['reporter_last'] ?? '')) ?: $incident['reporter_name'] ?? '—'); ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Pojazd</th>
                            <td><?php echo $incident['nr_poj'] ? e($incident['nr_poj'] . ($incident['vehicle_model'] ? ' – ' . $incident['vehicle_model'] : '')) : '—'; ?></td>
                        </tr>
                        <tr>
                            <th>Data zdarzenia</th>
                            <td><?php echo e(formatDateTime($incident['incident_date'], 'd.m.Y H:i')); ?></td>
                        </tr>
                        <tr>
                            <th>Data zgłoszenia</th>
                            <td><?php echo e(formatDateTime($incident['created_at'], 'd.m.Y H:i')); ?></td>
                        </tr>
                        <?php if ($incident['resolved_at']): ?>
                        <tr>
                            <th>Rozwiązano</th>
                            <td><?php echo e(formatDateTime($incident['resolved_at'], 'd.m.Y H:i')); ?> przez <?php echo e($incident['resolver_username'] ?? '—'); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <th>Opis</th>
                            <td><?php echo nl2br(e($incident['description'])); ?></td>
                        </tr>
                        <?php if (!empty($incident['resolution_notes'])): ?>
                        <tr>
                            <th>Notatki rozwiązania</th>
                            <td><?php echo nl2br(e($incident['resolution_notes'])); ?></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Formularz edycji -->
    <div class="col col-12 col-md-6">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">✏️ Edytuj zgłoszenie</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="" class="form">
                    <?php echo csrfField(); ?>

                    <div class="form-group">
                        <label for="title">Tytuł *</label>
                        <input type="text" name="title" id="title" class="form-control"
                               value="<?php echo e($incident['title']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="incident_type">Typ</label>
                        <select name="incident_type" id="incident_type" class="form-control">
                            <option value="breakdown" <?php echo $incident['incident_type'] === 'breakdown' ? 'selected' : ''; ?>>Awaria</option>
                            <option value="accident"  <?php echo $incident['incident_type'] === 'accident'  ? 'selected' : ''; ?>>Wypadek</option>
                            <option value="complaint" <?php echo $incident['incident_type'] === 'complaint' ? 'selected' : ''; ?>>Skarga</option>
                            <option value="other"     <?php echo $incident['incident_type'] === 'other'     ? 'selected' : ''; ?>>Inne</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="severity">Priorytet</label>
                        <select name="severity" id="severity" class="form-control">
                            <option value="low"      <?php echo $incident['severity'] === 'low'      ? 'selected' : ''; ?>>Niski</option>
                            <option value="medium"   <?php echo $incident['severity'] === 'medium'   ? 'selected' : ''; ?>>Średni</option>
                            <option value="high"     <?php echo $incident['severity'] === 'high'     ? 'selected' : ''; ?>>Wysoki</option>
                            <option value="critical" <?php echo $incident['severity'] === 'critical' ? 'selected' : ''; ?>>Krytyczny</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="open"        <?php echo $incident['status'] === 'open'        ? 'selected' : ''; ?>>Otwarte</option>
                            <option value="in_progress" <?php echo $incident['status'] === 'in_progress' ? 'selected' : ''; ?>>W trakcie</option>
                            <option value="resolved"    <?php echo $incident['status'] === 'resolved'    ? 'selected' : ''; ?>>Rozwiązane</option>
                            <option value="closed"      <?php echo $incident['status'] === 'closed'      ? 'selected' : ''; ?>>Zamknięte</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="description">Opis *</label>
                        <textarea name="description" id="description" class="form-control" rows="4"><?php echo e($incident['description']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="resolution_notes">Notatki / aktualizacje</label>
                        <textarea name="resolution_notes" id="resolution_notes" class="form-control" rows="3"
                                  placeholder="Dodaj opis aktualizacji, podjętych działań..."><?php echo e($incident['resolution_notes'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">💾 Zapisz zmiany</button>
                        <a href="/admin/incidents/index.php" class="btn btn-secondary">Anuluj</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php View::partial('layouts/footer'); ?>
