<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>🕒 Ewidencja Czasu Pracy (ECP)</h1>
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="/hr/dashboard.php?month=<?php echo urlencode($month); ?>" class="btn btn-secondary">← Powrot do panelu kadr</a>
        <?php if ((int)$selected_user_id > 0): ?>
            <a href="/hr/monthly-report.php?month=<?php echo urlencode($month); ?>&user_id=<?php echo (int)$selected_user_id; ?>" class="btn btn-primary">Raport miesieczny PDF</a>
            <a href="/hr/export-report.php?format=csv&month=<?php echo urlencode($month); ?>&user_id=<?php echo (int)$selected_user_id; ?>" class="btn btn-secondary">CSV</a>
            <a href="/hr/export-report.php?format=pdf&month=<?php echo urlencode($month); ?>&user_id=<?php echo (int)$selected_user_id; ?>" class="btn btn-secondary">PDF</a>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" class="form-inline">
            <div class="form-group">
                <label for="month">Miesiac:</label>
                <input type="month" id="month" name="month" class="form-control" value="<?php echo e($month); ?>">
            </div>
            <div class="form-group">
                <label for="user_id">Kierowca:</label>
                <select id="user_id" name="user_id" class="form-control">
                    <?php foreach ($drivers as $driver): ?>
                        <option value="<?php echo (int)$driver['id']; ?>" <?php echo (int)$selected_user_id === (int)$driver['id'] ? 'selected' : ''; ?>>
                            <?php echo e(trim(($driver['first_name'] ?? '') . ' ' . ($driver['last_name'] ?? ''))); ?> (<?php echo e($driver['username']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-secondary">Pokaz</button>
            </div>
        </form>
    </div>
</div>

<div class="dashboard-grid">
    <div class="stat-card">
        <div class="stat-card-title">Wpisy w miesiacu</div>
        <div class="stat-card-value"><?php echo count($entries); ?></div>
    </div>
    <div class="stat-card" style="border-left-color: var(--success);">
        <div class="stat-card-title">Suma godzin</div>
        <div class="stat-card-value"><?php echo number_format((float)$monthly_total, 2, ',', ' '); ?></div>
    </div>
</div>

<div class="row">
    <div class="col col-12 col-lg-5">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Dodaj / aktualizuj wpis</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="/hr/work-hours.php?month=<?php echo urlencode($month); ?>&user_id=<?php echo (int)$selected_user_id; ?>">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="user_id" value="<?php echo (int)$selected_user_id; ?>">

                    <div class="form-group">
                        <label for="work_date" class="form-label">Data</label>
                        <input type="date" id="work_date" name="work_date" class="form-control" required>
                        <?php if (!empty($errors['work_date'])): ?>
                            <div class="form-error"><?php echo e($errors['work_date']); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="hours_worked" class="form-label">Liczba godzin</label>
                        <input type="number" id="hours_worked" name="hours_worked" class="form-control" min="0" max="24" step="0.25" required>
                        <?php if (!empty($errors['hours_worked'])): ?>
                            <div class="form-error"><?php echo e($errors['hours_worked']); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="notes" class="form-label">Uwagi</label>
                        <textarea id="notes" name="notes" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Zapisz wpis</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col col-12 col-lg-7">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Wpisy ECP</h2>
            </div>
            <div class="card-body">
                <?php if (empty($entries)): ?>
                    <p class="text-muted">Brak wpisow dla wybranego kierowcy i miesiaca.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Godziny</th>
                                    <th>Uwagi</th>
                                    <th>Akcje</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($entries as $entry): ?>
                                    <tr>
                                        <td data-label="Data"><?php echo formatDate($entry['work_date'], 'd.m.Y'); ?></td>
                                        <td data-label="Godziny"><?php echo number_format((float)$entry['hours_worked'], 2, ',', ' '); ?></td>
                                        <td data-label="Uwagi"><?php echo e($entry['notes'] ?? ''); ?></td>
                                        <td data-label="Akcje">
                                            <form method="POST" action="/hr/work-hours.php?month=<?php echo urlencode($month); ?>&user_id=<?php echo (int)$selected_user_id; ?>" onsubmit="return confirm('Usunac ten wpis ECP?');">
                                                <?php echo csrfField(); ?>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="entry_id" value="<?php echo (int)$entry['id']; ?>">
                                                <button type="submit" class="btn btn-danger">Usun</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php View::partial('layouts/footer'); ?>
