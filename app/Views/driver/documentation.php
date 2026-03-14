<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<h1>📚 Dokumentacja kierowcy</h1>
<p class="text-muted">Dostep tylko do odczytu dla instrukcji i regulaminow.</p>

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
                    <pre style="white-space: pre-wrap; word-break: break-word; margin: 0;"><?php echo e((string)$content); ?></pre>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php View::partial('layouts/footer'); ?>
