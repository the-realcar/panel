<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="content-container">
    <div class="page-header">
        <h1><?php echo htmlspecialchars($page_title); ?></h1>
        <a href="/dispatcher/dashboard.php" class="btn btn-secondary">Powrót do panelu</a>
    </div>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($errors['general']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="form-card">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

        <div class="form-group">
            <label for="user_id">Kierowca: <span class="required">*</span></label>
            <select name="user_id" id="user_id" class="form-control <?php echo isset($errors['user_id']) ? 'is-invalid' : ''; ?>" required>
                <option value="">— Wybierz kierowcę —</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo (int)$user['id']; ?>" 
                            <?php echo (isset($form_data['user_id']) && $form_data['user_id'] == $user['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                        (<?php echo htmlspecialchars($user['employee_id']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['user_id'])): ?>
                <div class="invalid-feedback"><?php echo htmlspecialchars($errors['user_id']); ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="line_id">Linia: <span class="required">*</span></label>
            <select name="line_id" id="line_id" class="form-control <?php echo isset($errors['line_id']) ? 'is-invalid' : ''; ?>" required>
                <option value="">— Wybierz linię —</option>
                <?php foreach ($lines as $line): ?>
                    <option value="<?php echo (int)$line['id']; ?>"
                            <?php echo (isset($form_data['line_id']) && $form_data['line_id'] == $line['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($line['line_number']); ?>
                        <?php if ($line['name']): ?>
                            — <?php echo htmlspecialchars($line['name']); ?>
                        <?php endif; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['line_id'])): ?>
                <div class="invalid-feedback"><?php echo htmlspecialchars($errors['line_id']); ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="brigade_id">Brygada:</label>
            <select name="brigade_id" id="brigade_id" class="form-control">
                <option value="">— Wybierz brygadę (opcjonalnie) —</option>
                <?php foreach ($brigades as $brigade): ?>
                    <option value="<?php echo (int)$brigade['id']; ?>"
                            data-line-id="<?php echo (int)$brigade['line_id']; ?>"
                            <?php echo (isset($form_data['brigade_id']) && $form_data['brigade_id'] == $brigade['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($brigade['brigade_number']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="form-text text-muted">Brygady będą filtrowane po wybraniu linii.</small>
        </div>

        <div class="form-group">
            <label for="vehicle_id">Pojazd: <span class="required">*</span></label>
            <select name="vehicle_id" id="vehicle_id" class="form-control <?php echo isset($errors['vehicle_id']) ? 'is-invalid' : ''; ?>" required>
                <option value="">— Wybierz pojazd —</option>
                <?php foreach ($vehicles as $vehicle): ?>
                    <option value="<?php echo (int)$vehicle['id']; ?>"
                            <?php echo (isset($form_data['vehicle_id']) && $form_data['vehicle_id'] == $vehicle['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($vehicle['vehicle_number']); ?> — 
                        <?php echo htmlspecialchars($vehicle['model']); ?>
                        <?php if ($vehicle['registration_plate']): ?>
                            (<?php echo htmlspecialchars($vehicle['registration_plate']); ?>)
                        <?php endif; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['vehicle_id'])): ?>
                <div class="invalid-feedback"><?php echo htmlspecialchars($errors['vehicle_id']); ?></div>
            <?php endif; ?>
        </div>

        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="schedule_date">Data: <span class="required">*</span></label>
                <input type="date" 
                       name="schedule_date" 
                       id="schedule_date" 
                       class="form-control <?php echo isset($errors['schedule_date']) ? 'is-invalid' : ''; ?>"
                       value="<?php echo htmlspecialchars($form_data['schedule_date'] ?? date('Y-m-d')); ?>"
                       required>
                <?php if (isset($errors['schedule_date'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['schedule_date']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group col-md-4">
                <label for="start_time">Godzina rozpoczęcia: <span class="required">*</span></label>
                <input type="time" 
                       name="start_time" 
                       id="start_time" 
                       class="form-control <?php echo isset($errors['start_time']) ? 'is-invalid' : ''; ?>"
                       value="<?php echo htmlspecialchars($form_data['start_time'] ?? ''); ?>"
                       required>
                <?php if (isset($errors['start_time'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['start_time']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group col-md-4">
                <label for="end_time">Godzina zakończenia: <span class="required">*</span></label>
                <input type="time" 
                       name="end_time" 
                       id="end_time" 
                       class="form-control <?php echo isset($errors['end_time']) ? 'is-invalid' : ''; ?>"
                       value="<?php echo htmlspecialchars($form_data['end_time'] ?? ''); ?>"
                       required>
                <?php if (isset($errors['end_time'])): ?>
                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['end_time']); ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-group">
            <label for="notes">Uwagi:</label>
            <textarea name="notes" 
                      id="notes" 
                      class="form-control" 
                      rows="3"><?php echo htmlspecialchars($form_data['notes'] ?? ''); ?></textarea>
            <small class="form-text text-muted">Dodatkowe informacje dotyczące grafiku.</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Przydziel grafik</button>
            <a href="/dispatcher/dashboard.php" class="btn btn-secondary">Anuluj</a>
        </div>
    </form>
</div>

<script>
// Filtrowanie brygad po wyborze linii
document.getElementById('line_id').addEventListener('change', function() {
    const selectedLineId = this.value;
    const brigadeSelect = document.getElementById('brigade_id');
    const brigadeOptions = brigadeSelect.querySelectorAll('option[data-line-id]');
    
    brigadeOptions.forEach(option => {
        if (selectedLineId === '' || option.dataset.lineId === selectedLineId) {
            option.style.display = '';
        } else {
            option.style.display = 'none';
            if (option.selected) {
                brigadeSelect.value = '';
            }
        }
    });
});

// Wywołaj filtrowanie przy ładowaniu strony, jeśli linia jest wybrana
window.addEventListener('DOMContentLoaded', function() {
    const lineSelect = document.getElementById('line_id');
    if (lineSelect.value) {
        lineSelect.dispatchEvent(new Event('change'));
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
