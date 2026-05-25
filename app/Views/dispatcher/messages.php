<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1><?php echo htmlspecialchars($page_title); ?></h1>
        <div class="header-actions">
            <a href="/dispatcher/dashboard.php" class="btn btn-secondary">Powrot do dashboardu</a>
        </div>
    </div>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($errors['general']); ?></div>
    <?php endif; ?>

    <?php if (!$dispatches_available): ?>
        <div class="alert alert-warning">
            Tabela dyspozycji nie jest dostępna w aktualnej bazie danych. Widok pozostaje dostępny, ale wysyłka i historia komunikatów są wyłączone do czasu synchronizacji schematu.
        </div>
    <?php endif; ?>

    <div class="card dispatcher-message-card">
        <div class="card-header">
            <h2 class="card-title">Nowy komunikat</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="/dispatcher/messages.php">
                <?php echo csrfField(); ?>

                <div class="dispatcher-message-mode">
                    <label class="dispatcher-message-option">
                        <input type="radio" name="send_to_all" value="0" <?php echo empty($form['send_to_all']) ? 'checked' : ''; ?>>
                        <span>Do jednego kierowcy</span>
                    </label>
                    <label class="dispatcher-message-option">
                        <input type="radio" name="send_to_all" value="1" <?php echo !empty($form['send_to_all']) ? 'checked' : ''; ?>>
                        <span>Do wszystkich kierowcow</span>
                    </label>
                </div>

                <div class="form-group" id="recipient-group">
                    <label for="recipient_id" class="form-label">Kierowca</label>
                    <select id="recipient_id" name="recipient_id" class="form-control" <?php echo empty($form['send_to_all']) ? 'required' : ''; ?>>
                        <option value="">Wybierz kierowce</option>
                        <?php foreach ($drivers as $driver): ?>
                            <option value="<?php echo (int)$driver['id']; ?>" <?php echo (int)$form['recipient_id'] === (int)$driver['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(trim(($driver['first_name'] ?? '') . ' ' . ($driver['last_name'] ?? ''))); ?> (<?php echo htmlspecialchars($driver['username']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($errors['recipient_id'])): ?>
                        <div class="form-error"><?php echo htmlspecialchars($errors['recipient_id']); ?></div>
                    <?php endif; ?>
                    <small class="text-muted">Przy wysylce do wszystkich lista kierowcow jest pomijana.</small>
                </div>

                <div class="form-group">
                    <label for="message" class="form-label">Tresc komunikatu</label>
                    <textarea id="message" name="message" class="form-control" rows="5" maxlength="2000" required><?php echo htmlspecialchars($form['message']); ?></textarea>
                    <?php if (!empty($errors['message'])): ?>
                        <div class="form-error"><?php echo htmlspecialchars($errors['message']); ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" <?php echo !$dispatches_available ? 'disabled' : ''; ?>>Wyslij komunikat</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card dispatcher-message-card">
        <div class="card-header">
            <h2 class="card-title">Historia wyslanych komunikatow</h2>
        </div>
        <div class="card-body">
            <?php if (empty($sent_messages)): ?>
                <p class="text-muted">Brak wyslanych komunikatow.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table dispatcher-history-table">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Kierowca</th>
                                <th>Komunikat</th>
                                <th>Status</th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sent_messages as $item): ?>
                                <tr>
                                    <td data-label="Data"><?php echo formatDateTime($item['created_at'], 'd.m.Y H:i'); ?></td>
                                    <td data-label="Kierowca">
                                        <?php echo htmlspecialchars(trim(($item['recipient_first_name'] ?? '') . ' ' . ($item['recipient_last_name'] ?? ''))); ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($item['recipient_username']); ?></small>
                                    </td>
                                    <td data-label="Komunikat" style="max-width: 500px; white-space: pre-wrap;">
                                        <form method="POST" action="/dispatcher/messages.php" style="display: flex; gap: 0.5rem; align-items: flex-start; flex-wrap: wrap;">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="action" value="edit">
                                            <input type="hidden" name="dispatch_id" value="<?php echo (int)$item['id']; ?>">
                                            <textarea name="message" class="form-control" rows="3" maxlength="2000" style="min-width: 320px; max-width: 500px;"><?php echo htmlspecialchars($item['message']); ?></textarea>
                                            <button type="submit" class="btn btn-sm btn-primary">💾 Zapisz</button>
                                        </form>
                                    </td>
                                    <td data-label="Status">
                                        <?php if (!empty($item['read_at'])): ?>
                                            <span class="badge badge-success">Odczytano</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Nieodczytany</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Akcje">
                                        <form method="POST" action="/dispatcher/messages.php" style="display: inline;">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="dispatch_id" value="<?php echo $item['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Czy chcesz usunać ten komunikat?');">
                                                🗑️ Usuń
                                            </button>
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

<script>
(function () {
    const radios = document.querySelectorAll('input[name="send_to_all"]');
    const recipientGroup = document.getElementById('recipient-group');
    const recipientSelect = document.getElementById('recipient_id');

    function syncMode() {
        const sendToAll = document.querySelector('input[name="send_to_all"]:checked')?.value === '1';
        if (!recipientGroup || !recipientSelect) {
            return;
        }

        recipientGroup.style.opacity = sendToAll ? '0.55' : '1';
        recipientSelect.disabled = sendToAll;
        recipientSelect.required = !sendToAll;
    }

    radios.forEach(function (radio) {
        radio.addEventListener('change', syncMode);
    });

    syncMode();
})();
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
