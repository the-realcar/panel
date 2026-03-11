<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<?php
$type_info = [
    'kzw' => [
        'label' => 'KZW (Kurs Zastępczy Własny)',
        'desc'  => 'Składasz, gdy chcesz odrobić niewykonaną lub niezaliczoną służbę albo chcesz wykonać kurs w swój dzień wolny.',
        'reqs'  => ['minimum jednodniowe wyprzedzenie', 'posiadanie uprawnień kierowcy'],
        'req_danger' => [],
    ],
    'cancel_duty' => [
        'label' => 'Anulowanie służby',
        'desc'  => 'Składasz, gdy z ważnego powodu nie możesz wykonać zaplanowanej służby.',
        'reqs'  => [
            'minimum ranga kierowcy',
            'zaplanowana służba w przyszłości',
            'maksymalnie 3 anulowane służby w miesiącu',
            'służba z KZW nie podlega anulowaniu',
            'minimalny etat to 2/7',
            'złożenie do godz. 22 dnia służby',
            'uzasadniony powód',
        ],
        'req_danger' => [],
    ],
    'day_off' => [
        'label' => 'Dzień wolny',
        'desc'  => 'Składasz z wyprzedzeniem, gdy nie możesz zrealizować kursu następnego dnia. Uprawnia do jednodniowego zwolnienia z pracy.',
        'reqs'  => [
            'minimum jednodniowe wyprzedzenie',
            'minimum 4 zaliczone służby',
            'uzasadniony powód',
        ],
        'req_danger' => [],
    ],
    'vacation' => [
        'label' => 'Urlop',
        'desc'  => 'Składasz, gdy chcesz mieć wolne na dłuższy okres. Urlop trwa maksymalnie dwa tygodnie.',
        'reqs'  => [
            'minimum jednodniowe wyprzedzenie',
            'minimum 10 zaliczonych służb',
            'uzasadniony powód (wyjątek: urlop na żądanie)',
        ],
        'req_danger' => [],
    ],
    'permanent_vehicle' => [
        'label' => 'Stały pojazd',
        'desc'  => 'Składasz, gdy chcesz objąć pod opiekę wybrany pojazd na stałe.',
        'reqs'  => [
            'minimum 15 zaliczonych służb',
            'sumienność i zaufanie zarządu',
            'brak aktualnie przypisanego pojazdu stałego',
        ],
        'req_danger' => [],
    ],
    'change_vehicle' => [
        'label' => 'Zmiana stałego pojazdu',
        'desc'  => 'Składasz, gdy chcesz zmienić pojazd, który masz obecnie pod opieką.',
        'reqs'  => [
            'posiadanie stałego pojazdu',
            'brak wniosku o zmianę pojazdu w ciągu ostatnich 14 dni',
        ],
        'req_danger' => ['posiadanie stałego pojazdu'],
    ],
    'no_vehicle_assign' => [
        'label' => 'Nieprzydzielanie pojazdów',
        'desc'  => 'Składasz wyłącznie w przypadku problemów technicznych z uruchomieniem konkretnego pojazdu na mapie.',
        'reqs'  => [],
        'req_danger' => [],
    ],
    'change_status' => [
        'label' => 'Zmiana etatu',
        'desc'  => 'Składasz, gdy chcesz zmienić aktualny wymiar etatu pracy.',
        'reqs'  => [],
        'req_danger' => [],
    ],
    'resignation' => [
        'label' => 'Zwolnienie',
        'desc'  => 'Składasz, gdy chcesz zrezygnować z pracy w vZTM.',
        'reqs'  => [],
        'req_danger' => [],
    ],
];
$default_type = array_key_first($type_info);
?>

<div class="page-header">
    <h1>📋 Złóż nowy wniosek</h1>
</div>

<div class="row" style="gap:0; align-items: flex-start;">
    <!-- Formularz -->
    <div class="col col-12 col-md-6">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="" class="form" id="application-form">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="action" value="submit">

                    <div class="form-group">
                        <label>Typ wniosku (wniosek o):</label>
                        <?php foreach ($type_info as $type_key => $info): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="type" id="type_<?php echo $type_key; ?>"
                                   value="<?php echo $type_key; ?>"
                                   <?php echo ($type_key === $default_type) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="type_<?php echo $type_key; ?>">
                                <?php echo e($info['label']); ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- KZW -->
                    <div class="application-fields" id="fields_kzw">
                        <div class="form-group">
                            <label for="execution_date_kzw">Data wykonania</label>
                            <input type="date" name="execution_date_kzw" id="execution_date_kzw" class="form-control"
                                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                        </div>
                        <div class="form-group">
                            <label for="vehicle_id_kzw">Proponowany pojazd</label>
                            <select name="vehicle_id_kzw" id="vehicle_id_kzw" class="form-control">
                                <option value="">— Wybierz pojazd (opcjonalnie) —</option>
                                <?php foreach ($vehicles as $v): ?>
                                    <option value="<?php echo $v['id']; ?>"><?php echo e($v['nr_poj']); ?><?php echo $v['model'] ? ' – ' . e($v['model']) : ''; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Anulowanie służby -->
                    <div class="application-fields" id="fields_cancel_duty" style="display:none;">
                        <div class="form-group">
                            <label for="schedule_id">Wybierz służbę</label>
                            <select name="schedule_id" id="schedule_id" class="form-control">
                                <option value="">— Wybierz służbę —</option>
                                <?php foreach ($upcoming_schedules as $s): ?>
                                    <option value="<?php echo $s['id']; ?>">
                                        <?php echo e(date('d.m.Y', strtotime($s['schedule_date']))); ?>
                                        <?php echo e(substr($s['start_time'], 0, 5) . '–' . substr($s['end_time'], 0, 5)); ?>
                                        <?php echo !empty($s['line_number']) ? ' | Linia ' . e($s['line_number']) : ''; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="reason_cancel_duty">Powód *</label>
                            <textarea name="reason_cancel_duty" id="reason_cancel_duty" class="form-control" rows="3" placeholder="Uzasadnienie..."></textarea>
                        </div>
                    </div>

                    <!-- Dzień wolny -->
                    <div class="application-fields" id="fields_day_off" style="display:none;">
                        <div class="form-group">
                            <label for="execution_date_day_off">Wybierz dzień</label>
                            <input type="date" name="execution_date_day_off" id="execution_date_day_off" class="form-control"
                                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                        </div>
                        <div class="form-group">
                            <label for="reason_day_off">Powód *</label>
                            <textarea name="reason_day_off" id="reason_day_off" class="form-control" rows="3" placeholder="Uzasadnienie..."></textarea>
                        </div>
                    </div>

                    <!-- Urlop -->
                    <div class="application-fields" id="fields_vacation" style="display:none;">
                        <div class="form-group">
                            <label for="date_from">Od</label>
                            <input type="date" name="date_from" id="date_from" class="form-control"
                                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                        </div>
                        <div class="form-group">
                            <label for="date_to">Do</label>
                            <input type="date" name="date_to" id="date_to" class="form-control"
                                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                        </div>
                        <div class="form-group">
                            <label for="reason_vacation">Powód *</label>
                            <textarea name="reason_vacation" id="reason_vacation" class="form-control" rows="3" placeholder="Uzasadnienie..."></textarea>
                        </div>
                    </div>

                    <!-- Stały pojazd -->
                    <div class="application-fields" id="fields_permanent_vehicle" style="display:none;">
                        <div class="form-group">
                            <label for="vehicle_id_perm">Pojazd do objęcia *</label>
                            <select name="vehicle_id_perm" id="vehicle_id_perm" class="form-control">
                                <option value="">— Wybierz pojazd —</option>
                                <?php foreach ($vehicles as $v): ?>
                                    <option value="<?php echo $v['id']; ?>"><?php echo e($v['nr_poj']); ?><?php echo $v['model'] ? ' – ' . e($v['model']) : ''; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Zmiana stałego pojazdu -->
                    <div class="application-fields" id="fields_change_vehicle" style="display:none;">
                        <div class="form-group">
                            <label for="vehicle_id_change">Pojazd do objęcia *</label>
                            <select name="vehicle_id_change" id="vehicle_id_change" class="form-control">
                                <option value="">— Wybierz pojazd —</option>
                                <?php foreach ($vehicles as $v): ?>
                                    <option value="<?php echo $v['id']; ?>"><?php echo e($v['nr_poj']); ?><?php echo $v['model'] ? ' – ' . e($v['model']) : ''; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Nieprzydzielanie pojazdów -->
                    <div class="application-fields" id="fields_no_vehicle_assign" style="display:none;">
                        <div class="form-group">
                            <label for="vehicle_ids">Wybierz pojazdy *</label>
                            <select name="vehicle_ids[]" id="vehicle_ids" class="form-control" multiple size="5">
                                <?php foreach ($vehicles as $v): ?>
                                    <option value="<?php echo $v['id']; ?>"><?php echo e($v['nr_poj']); ?><?php echo $v['model'] ? ' – ' . e($v['model']) : ''; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text">Przytrzymaj Ctrl / Cmd, aby wybrać kilka pojazdów.</small>
                        </div>
                        <div class="form-group">
                            <label for="reason_no_vehicle_assign">Powód *</label>
                            <textarea name="reason_no_vehicle_assign" id="reason_no_vehicle_assign" class="form-control" rows="3" placeholder="Uzasadnienie..."></textarea>
                        </div>
                    </div>

                    <!-- Zmiana etatu -->
                    <div class="application-fields" id="fields_change_status" style="display:none;">
                        <div class="form-group">
                            <label>Nowe dni pracy</label>
                            <?php
                            $days = [
                                'monday'    => 'Poniedziałek',
                                'tuesday'   => 'Wtorek',
                                'wednesday' => 'Środa',
                                'thursday'  => 'Czwartek',
                                'friday'    => 'Piątek',
                                'saturday'  => 'Sobota',
                                'sunday'    => 'Niedziela',
                            ];
                            foreach ($days as $val => $label):
                            ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="work_days[]"
                                       id="day_<?php echo $val; ?>" value="<?php echo $val; ?>">
                                <label class="form-check-label" for="day_<?php echo $val; ?>"><?php echo $label; ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="form-group">
                            <label for="reason_change_status">Powód *</label>
                            <textarea name="reason_change_status" id="reason_change_status" class="form-control" rows="3" placeholder="Uzasadnienie..."></textarea>
                        </div>
                    </div>

                    <!-- Zwolnienie -->
                    <div class="application-fields" id="fields_resignation" style="display:none;">
                        <div class="form-group">
                            <label for="reason_resignation">Powód</label>
                            <textarea name="reason_resignation" id="reason_resignation" class="form-control" rows="3" placeholder="Uzasadnienie (opcjonalne)..."></textarea>
                        </div>
                    </div>

                    <!-- Uwagi zawsze widoczne -->
                    <div class="form-group">
                        <label for="notes">Uwagi dodatkowe</label>
                        <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Dodatkowe informacje (opcjonalne)..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" id="submit-btn">📤 Wyślij wniosek</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Panel informacyjny -->
    <div class="col col-12 col-md-6">
        <div class="card" id="info-panel">
            <div class="card-header">
                <h2 class="card-title" id="info-type-title">Informacje o wniosku</h2>
            </div>
            <div class="card-body" id="info-content">
                <?php foreach ($type_info as $type_key => $info): ?>
                <div class="type-info" id="info_<?php echo $type_key; ?>" style="display:none;">
                    <p><?php echo $info['desc']; ?></p>
                    <?php if (!empty($info['reqs'])): ?>
                    <p><strong>Wymagania:</strong></p>
                    <ul>
                        <?php foreach ($info['reqs'] as $req): ?>
                        <li <?php echo in_array($req, $info['req_danger'], true) ? 'style="color:var(--danger);"' : ''; ?>>
                            <?php echo e($req); ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Historia wniosków -->
<div class="card" style="margin-top:1.5rem;">
    <div class="card-header">
        <h2 class="card-title">📜 Moje wnioski</h2>
    </div>
    <div class="card-body">
        <?php if (empty($history)): ?>
            <p class="text-muted">Nie złożyłeś jeszcze żadnych wniosków.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Typ</th>
                            <th>Status</th>
                            <th>Szczegóły</th>
                            <th>Uwagi rozpatrzenia</th>
                            <th>Data złożenia</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $app): ?>
                        <tr>
                            <td><?php echo $app['id']; ?></td>
                            <td><?php echo e(Application::typeLabel($app['type'])); ?></td>
                            <td>
                                <span class="badge <?php echo Application::statusBadgeClass($app['status']); ?>">
                                    <?php echo e(Application::statusLabel($app['status'])); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($app['execution_date']): ?>
                                    Data: <?php echo e(date('d.m.Y', strtotime($app['execution_date']))); ?>
                                <?php elseif ($app['date_from']): ?>
                                    <?php echo e(date('d.m.Y', strtotime($app['date_from']))); ?> – <?php echo e(date('d.m.Y', strtotime($app['date_to']))); ?>
                                <?php elseif ($app['schedule_date']): ?>
                                    Służba <?php echo e(date('d.m.Y', strtotime($app['schedule_date']))); ?>
                                <?php elseif ($app['vehicle_nr']): ?>
                                    Pojazd: <?php echo e($app['vehicle_nr']); ?>
                                <?php elseif ($app['work_days']): ?>
                                    <?php
                                    $wd = is_string($app['work_days']) ? json_decode($app['work_days'], true) : $app['work_days'];
                                    $dl = ['monday'=>'Pn','tuesday'=>'Wt','wednesday'=>'Śr','thursday'=>'Czw','friday'=>'Pt','saturday'=>'Sb','sunday'=>'Nd'];
                                    echo e(implode(', ', array_map(fn($d) => $dl[$d] ?? $d, (array)$wd)));
                                    ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($app['review_notes'])): ?>
                                    <?php echo e($app['review_notes']); ?>
                                <?php elseif (!empty($app['reviewer_username'])): ?>
                                    Rozpatrzył: <?php echo e(trim(($app['reviewer_first'] ?? '') . ' ' . ($app['reviewer_last'] ?? ''))); ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td><?php echo e(date('d.m.Y H:i', strtotime($app['created_at']))); ?></td>
                            <td>
                                <?php if ($app['status'] === 'pending'): ?>
                                <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Czy na pewno chcesz anulować ten wniosek?');">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="action" value="cancel">
                                    <input type="hidden" name="app_id" value="<?php echo $app['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">❌ Anuluj</button>
                                </form>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function () {
    var radios = document.querySelectorAll('input[name="type"]');
    var allFields = document.querySelectorAll('.application-fields');
    var allInfos = document.querySelectorAll('.type-info');

    function updateForm(selectedType) {
        allFields.forEach(function (el) { el.style.display = 'none'; });
        allInfos.forEach(function (el) { el.style.display = 'none'; });

        var fields = document.getElementById('fields_' + selectedType);
        if (fields) fields.style.display = '';

        var info = document.getElementById('info_' + selectedType);
        if (info) info.style.display = '';
    }

    radios.forEach(function (radio) {
        radio.addEventListener('change', function () {
            updateForm(this.value);
        });
    });

    // Init
    var checked = document.querySelector('input[name="type"]:checked');
    if (checked) updateForm(checked.value);
}());
</script>

<?php View::partial('layouts/footer'); ?>
