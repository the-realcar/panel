<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<h1>📚 Dokumentacja kierowcy</h1>
<p class="text-muted">Dostep tylko do odczytu dla instrukcji i regulaminow.</p>

<?php if ($can_edit): ?>
<div class="row" style="margin-bottom: 1.5rem; gap: 1rem; align-items: stretch;">
    <div class="col col-12 col-lg-6">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Dodaj nowy dokument</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="/driver/documentation.php">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="action" value="create">

                    <div class="form-group">
                        <label for="driver-new-file-name" class="form-label">Nazwa pliku</label>
                        <input id="driver-new-file-name" name="file_name" class="form-control" type="text" placeholder="np. instrukcja-kierowcy.md" required>
                        <small class="text-muted">Do tworzenia nowych dokumentow obslugiwane sa pliki TXT i MD.</small>
                    </div>

                    <div class="form-group">
                        <label for="driver-new-file-content" class="form-label">Poczatkowa tresc</label>
                        <textarea id="driver-new-file-content" name="content" class="form-control" rows="6" style="font-family: monospace;"></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Dodaj dokument</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col col-12 col-lg-6">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Przeslij dokument</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="/driver/documentation.php" enctype="multipart/form-data">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="action" value="upload">

                    <div class="form-group">
                        <label for="driver-upload-document" class="form-label">Plik</label>
                        <input id="driver-upload-document" name="document_file" class="form-control" type="file" accept=".txt,.md,.pdf" required>
                        <small class="text-muted">Mozesz przeslac plik TXT, MD albo PDF do 10 MB.</small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-secondary">Przeslij dokument</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <div class="col col-12 col-lg-4">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Lista dokumentow</h2>
            </div>
            <div class="card-body">
                <?php if (empty($documents)): ?>
                    <p class="text-muted">Brak dokumentow w katalogu <code>docs/driver</code>.</p>
                <?php else: ?>
                    <ul style="list-style: none; margin: 0; padding: 0;">
                        <?php foreach ($documents as $doc): ?>
                            <li style="padding: 0.5rem 0; border-bottom: 1px solid var(--border);">
                                <a href="/driver/documentation.php?file=<?php echo urlencode($doc['name']); ?>">
                                    <?php echo e($doc['name']); ?>
                                </a>
                                <div class="text-muted" style="font-size: 0.85rem; margin-top: 0.25rem;">
                                    <?php echo strtoupper($doc['extension']); ?> • <?php echo number_format(($doc['size'] / 1024), 1, ',', ' '); ?> KB
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col col-12 col-lg-8">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <?php echo $selected ? e($selected['name']) : 'Podglad dokumentu'; ?>
                </h2>
                <?php if ($selected && $can_edit): ?>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.5rem;">
                        <?php if ($selected['extension'] !== 'pdf'): ?>
                            <button type="button" class="btn btn-sm btn-primary" onclick="toggleEditMode()">
                                ✏️ Edytuj dokument
                            </button>
                        <?php endif; ?>
                        <form method="POST" action="/driver/documentation.php" onsubmit="return confirm('Czy na pewno usunac ten dokument?');" style="margin: 0;">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="file_name" value="<?php echo e($selected['name']); ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Usun dokument</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if ($selected === null): ?>
                    <p class="text-muted">Wybierz dokument z listy po lewej stronie.</p>
                <?php elseif ($selected['extension'] === 'pdf'): ?>
                    <p class="text-muted">Podglad PDF:</p>
                    <iframe
                        src="/docs/driver/<?php echo rawurlencode($selected['name']); ?>"
                        title="Podglad PDF"
                        style="width: 100%; height: 70vh; border: 1px solid var(--border); border-radius: var(--radius);"
                    ></iframe>
                <?php else: ?>
                    <div id="view-mode">
                        <pre style="white-space: pre-wrap; word-break: break-word; margin: 0;"><?php echo e((string)$content); ?></pre>
                    </div>

                    <?php if ($can_edit): ?>
                    <div id="edit-mode" style="display: none;">
                        <form method="POST" action="/driver/documentation.php">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="file_name" value="<?php echo e($selected['name']); ?>">

                            <textarea name="content" class="form-control" rows="20" style="font-family: monospace; margin-bottom: 1rem;"><?php echo e((string)$content); ?></textarea>

                            <div style="display: flex; gap: 0.5rem;">
                                <button type="submit" class="btn btn-success">💾 Zapisz</button>
                                <button type="button" class="btn btn-secondary" onclick="toggleEditMode()">❌ Anuluj</button>
                            </div>
                        </form>
                    </div>

                    <script>
                    function toggleEditMode() {
                        const viewMode = document.getElementById('view-mode');
                        const editMode = document.getElementById('edit-mode');
                        if (viewMode.style.display === 'none') {
                            viewMode.style.display = '';
                            editMode.style.display = 'none';
                        } else {
                            viewMode.style.display = 'none';
                            editMode.style.display = '';
                        }
                    }
                    </script>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php View::partial('layouts/footer'); ?>
