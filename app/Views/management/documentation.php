<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>📚 Dokumenty Zarządu KOT</h1>
    <a href="/index.php" class="btn btn-secondary">Powrót</a>
</div>

<?php if ($can_edit): ?>
<div class="row" style="align-items: stretch; gap: 1rem; margin-bottom: 1.5rem;">
    <div class="col col-12 col-lg-6">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Dodaj nowy dokument</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="/management/documentation.php">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="action" value="create">

                    <div class="form-group">
                        <label for="new-file-name" class="form-label">Nazwa pliku</label>
                        <input id="new-file-name" name="file_name" class="form-control" type="text" placeholder="np. regulamin-kot.md" required>
                        <small class="text-muted">Do tworzenia nowych dokumentow obslugiwane sa pliki TXT i MD.</small>
                    </div>

                    <div class="form-group">
                        <label for="new-file-content" class="form-label">Poczatkowa tresc</label>
                        <textarea id="new-file-content" name="content" class="form-control" rows="6" style="font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;"></textarea>
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
                <form method="POST" action="/management/documentation.php" enctype="multipart/form-data">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="action" value="upload">

                    <div class="form-group">
                        <label for="upload-document" class="form-label">Plik</label>
                        <input id="upload-document" name="document_file" class="form-control" type="file" accept=".txt,.md,.pdf" required>
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

<div class="row" style="align-items: flex-start; gap: 1.5rem;">
    <div class="col col-12 col-md-4">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Lista dokumentów</h2>
            </div>
            <div class="card-body">
                <?php if (empty($documents)): ?>
                    <p class="text-muted">Brak dokumentów w katalogu docs/management.</p>
                <?php else: ?>
                    <ul style="list-style:none; padding-left:0; margin:0; display:grid; gap:.5rem;">
                        <?php foreach ($documents as $doc): ?>
                            <li>
                                <a href="/management/documentation.php?file=<?php echo urlencode($doc['name']); ?>"
                                   class="btn btn-outline" style="width:100%; justify-content:space-between;">
                                    <span><?php echo e($doc['name']); ?></span>
                                    <small><?php echo strtoupper($doc['extension']); ?> • <?php echo number_format(($doc['size'] / 1024), 1, ',', ' '); ?> KB</small>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col col-12 col-md-8">
        <div class="card">
            <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; gap:.75rem;">
                <h2 class="card-title" style="margin:0;">
                    <?php echo $selected ? e($selected['name']) : 'Podglad dokumentu'; ?>
                </h2>
                <?php if ($selected && $can_edit): ?>
                    <form method="POST" action="/management/documentation.php" onsubmit="return confirm('Czy na pewno usunac ten dokument?');" style="margin:0;">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="file_name" value="<?php echo e($selected['name']); ?>">
                        <button type="submit" class="btn btn-danger">Usun dokument</button>
                    </form>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (!$selected): ?>
                    <p class="text-muted">Wybierz dokument z listy po lewej stronie.</p>
                <?php elseif ($selected['extension'] === 'pdf'): ?>
                    <iframe
                        src="/docs/management/<?php echo rawurlencode($selected['name']); ?>"
                        style="width:100%; min-height:70vh; border:1px solid var(--border); border-radius:8px;"
                        title="Podgląd PDF">
                    </iframe>
                <?php else: ?>
                    <form method="POST" action="/management/documentation.php?file=<?php echo urlencode($selected['name']); ?>">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="file_name" value="<?php echo e($selected['name']); ?>">

                        <div class="form-group">
                            <label for="content" class="form-label">Treść dokumentu</label>
                            <textarea id="content" name="content" class="form-control" rows="24" style="font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;"><?php echo e((string)$content); ?></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">💾 Zapisz dokument</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php View::partial('layouts/footer'); ?>
