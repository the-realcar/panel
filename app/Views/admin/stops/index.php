<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<div class="page-header">
    <h1>🚏 Zarządzanie przystankami</h1>
    <?php if ($rbac->hasPermission('stops', 'create')): ?>
        <a href="/admin/stops/create.php" class="btn btn-primary">➕ Dodaj przystanek</a>
    <?php endif; ?>
</div>

<!-- Zarządzanie miastami -->
<div class="card">
    <div class="card-header d-flex justify-between align-center">
        <h2 class="card-title" style="margin:0;">🏙️ Miasta</h2>
        <button class="btn btn-sm btn-primary" onclick="showCityForm()" <?php echo !$cities_available ? 'disabled' : ''; ?>>➕ Dodaj miasto</button>
    </div>
    <div class="card-body">
        <?php if (!$cities_available): ?>
            <div class="alert alert-warning">
                Tabela <code>cities</code> nie jest dostępna w aktualnej bazie danych. Lista przystanków pozostaje dostępna, ale zarządzanie miastami jest wyłączone do czasu synchronizacji schematu.
            </div>
        <?php endif; ?>

        <div id="city-form" style="display:none;" class="mb-2">
            <div class="form-inline" style="gap:0.75rem;">
                <input type="text" id="city-name-input" class="form-control" placeholder="Nazwa miasta" style="max-width:280px;">
                <input type="hidden" id="city-edit-id" value="">
                <button class="btn btn-success btn-sm" onclick="saveCity()">💾 Zapisz</button>
                <button class="btn btn-secondary btn-sm" onclick="hideCityForm()">Anuluj</button>
            </div>
            <div id="city-form-error" class="form-error" style="display:none;"></div>
        </div>

        <?php if (empty($cities)): ?>
            <p class="text-muted">Brak zdefiniowanych miast. Dodaj pierwsze miasto powyżej.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table" id="cities-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nazwa</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cities as $city): ?>
                        <tr id="city-row-<?php echo $city['id']; ?>">
                            <td><?php echo $city['id']; ?></td>
                            <td><strong><?php echo e($city['name']); ?></strong></td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-secondary"
                                            onclick="editCity(<?php echo $city['id']; ?>, '<?php echo addslashes(e($city['name'])); ?>')">
                                        ✏️ Edytuj
                                    </button>
                                    <button class="btn btn-sm btn-danger"
                                            onclick="deleteCity(<?php echo $city['id']; ?>)">
                                        🗑️ Usuń
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Lista przystanków -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Przystanki</h2>
    </div>
    <div class="card-body">
        <?php if (empty($stops)): ?>
            <p class="text-muted">Brak przystanków do wyświetlenia.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nazwa</th>
                            <th>Miasto</th>
                            <th>Opis</th>
                            <th>Status NZ</th>
                            <th>Status</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stops as $stop): ?>
                        <tr>
                            <td><?php echo $stop['id']; ?></td>
                            <td><strong><?php echo e($stop['name']); ?></strong></td>
                            <td><?php echo e($stop['city_name'] ?? '—'); ?></td>
                            <td><?php echo e($stop['opis'] ?? '—'); ?></td>
                            <td>
                                <?php if (!empty($stop['status_nz'])): ?>
                                    <span class="badge badge-warning">Tak</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Nie</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($stop['active']): ?>
                                    <span class="badge badge-success">Aktywny</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Nieaktywny</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="/admin/platforms/index.php?stop_id=<?php echo urlencode($stop['id']); ?>"
                                       class="btn btn-sm btn-info">🏢 Platformy</a>
                                    <?php if ($rbac->hasPermission('stops', 'update')): ?>
                                        <a href="/admin/stops/edit.php?id=<?php echo $stop['id']; ?>"
                                           class="btn btn-sm btn-secondary">✏️ Edytuj</a>
                                    <?php endif; ?>
                                    <?php if ($rbac->hasPermission('stops', 'delete')): ?>
                                        <form method="POST" action="/admin/stops/delete.php" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                            <input type="hidden" name="id" value="<?php echo $stop['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Czy na pewno chcesz usunąć ten przystanek?');">🗑️ Usuń</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <?php echo pagination($page, $total_pages, '/admin/stops/index.php?'); ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
var csrfToken = '<?php echo generateCsrfToken(); ?>';

function showCityForm(name, id) {
    document.getElementById('city-name-input').value = name || '';
    document.getElementById('city-edit-id').value = id || '';
    document.getElementById('city-form-error').style.display = 'none';
    document.getElementById('city-form').style.display = '';
    document.getElementById('city-name-input').focus();
}

function hideCityForm() {
    document.getElementById('city-form').style.display = 'none';
    document.getElementById('city-name-input').value = '';
    document.getElementById('city-edit-id').value = '';
}

function editCity(id, name) {
    showCityForm(name, id);
}

function saveCity() {
    var name = document.getElementById('city-name-input').value.trim();
    var id = document.getElementById('city-edit-id').value;
    if (!name) {
        showCityError('Podaj nazwę miasta.');
        return;
    }
    var action = id ? 'update' : 'create';
    var body = new URLSearchParams({csrf_token: csrfToken, city_action: action, name: name});
    if (id) body.append('id', id);

    fetch('/admin/stops/index.php', {method: 'POST', body: body})
        .then(r => r.json())
        .then(function(res) {
            if (!res.success) { showCityError(res.error); return; }
            hideCityForm();
            location.reload();
        })
        .catch(function() { showCityError('Błąd połączenia.'); });
}

function deleteCity(id) {
    if (!confirm('Czy na pewno chcesz usunąć to miasto?')) return;
    var body = new URLSearchParams({csrf_token: csrfToken, city_action: 'delete', id: id});
    fetch('/admin/stops/index.php', {method: 'POST', body: body})
        .then(r => r.json())
        .then(function(res) {
            if (!res.success) { alert(res.error); return; }
            var row = document.getElementById('city-row-' + id);
            if (row) row.remove();
        })
        .catch(function() { alert('Błąd połączenia.'); });
}

function showCityError(msg) {
    var el = document.getElementById('city-form-error');
    el.textContent = msg;
    el.style.display = '';
}
</script>

<?php View::partial('layouts/footer'); ?>
