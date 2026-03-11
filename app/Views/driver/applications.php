<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<h1>📋 Złóż nowy wniosek</h1>

<?php
$type_info = [
    'kzw' => [
        'label' => 'KZW',
        'desc'  => 'Wniosek o KZW składamy wtedy, gdy chcemy odrobić niewykonaną, lub niezaliczoną wcześniej służbę. Jeżeli kierowca ma dzień wolny, który chce sobie przeznaczyć na wykonanie kursu, to także może go złożyć.',
        'reqs'  => ['wniosek musi zostać złożony z minimum jednodniowym wyprzedzeniem', 'musisz posiadać uprawnienia kierowcy'],
        'req_danger' => [],
    ],
    'cancel_duty' => [
        'label' => 'Anulowanie służby',
        'desc'  => 'Wniosek o anulowanie służby składamy wtedy, gdy z jakiegoś powodu nie możemy wykonać służby danego dnia.',
        'reqs'  => [
            'minimum ranga kierowcy',
            'zaplanowana służba w przyszłości',
            'maksymalnie 3 anulowane służby w miesiącu',
            'służba anulowana nie może być z KZW',
            'minimalny etat to 2/7',
            'maksymalny czas złożenia do godz. 22 dnia służby',
            'uzasadniony powód',
        ],
        'req_danger' => [],
    ],
    'day_off' => [
        'label' => 'Dzień wolny',
        'desc'  => 'Wniosek o dzień wolny składamy wtedy, gdy wiemy, że kurs przypisany dnia następnego nie będzie możliwy do zrealizowania. Uprawnia on do jednodniowego zwolnienia z pracy.',
        'reqs'  => [
            'wniosek musi zostać złożony z minimum jednodniowym wyprzedzeniem',
            'minimum 4 zaliczone służby',
            'uzasadniony powód',
        ],
        'req_danger' => [],
    ],
    'vacation' => [
        'label' => 'Urlop',
        'desc'  => 'Wniosek o urlop składamy wtedy, gdy chcemy mieć wolne na dany okres czasu. Uprawnia on do maksymalnie dwutygodniowego zwolnienia z pracy.',
        'reqs'  => [
            'wniosek musi zostać złożony z minimum jednodniowym wyprzedzeniem',
            'minimum 10 zaliczonych służb',
            'uzasadniony powód (wyjątkiem jest urlop na żądanie)',
        ],
        'req_danger' => [],
    ],
    'permanent_vehicle' => [
        'label' => 'Stały pojazd',
        'desc'  => 'Wniosek o przydzielenie stałego pojazdu składamy wtedy, gdy chcemy uzyskać pod opiekę wybrany przez siebie lub zarząd, pojazd. Będzie on używany przez większość Twoich kursów.',
        'reqs'  => [
            'minimum 15 zaliczonych służb',
            'sumienność wykonywanej pracy',
            'zaufanie wśród zarządu',
            'nieposiadanie pojazdu stałego',
        ],
        'req_danger' => [],
    ],
    'change_vehicle' => [
        'label' => 'Zmiana stałego pojazdu',
        'desc'  => 'Wniosek o zmianę stałego pojazdu składamy gdy chcemy zmienić pojazd, który aktualnie mamy pod swoją opieką.',
        'reqs'  => [
            'posiadanie stałego pojazdu',
            'brak wniosku o przypisanie lub zmianę stałego pojazdu w ciągu ostatnich 14 dni',
        ],
        'req_danger' => ['posiadanie stałego pojazdu'],
    ],
    'no_vehicle_assign' => [
        'label' => 'Nieprzydzielanie pojazdów',
        'desc'  => 'Wniosek o nieprzydzielanie wybranych pojazdów składamy wtedy, gdy nie mamy możliwości kursowania na liniach pewnymi pojazdami. <strong>Ten wniosek jest stosowany tylko i wyłącznie w przypadku problemów technicznych związanych z uruchomieniem pojazdu na mapie.</strong>',
        'reqs'  => [],
        'req_danger' => [],
    ],
    'change_status' => [
        'label' => 'Zmiana etatu',
        'desc'  => 'Wniosek o zmianę etatu składamy wtedy, gdy chcemy zmienić aktualny etat pracy.',
        'reqs'  => [],
        'req_danger' => [],
    ],
    'resignation' => [
        'label' => 'Zwolnienie',
        'desc'  => 'Wniosek o zwolnienie składamy wtedy, gdy chcemy zrezygnować z pracy w vZTM.',
        'reqs'  => [],
        'req_danger' => [],
    ],
];
$default_type = array_key_first($type_info);
?>

<div class="row" style="gap:0; align-items: flex-start;">
    <!-- Formularz -->
    <div class="col col-12 col-md-6">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="" class="form" id="application-form">
                    <?php echo csrfField(); ?>

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
                            <input type="date" name="execution_date" id="execution_date_kzw" class="form-control"
                                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                        </div>
                        <div class="form-group">
                            <label for="vehicle_id_kzw">Proponowany pojazd</label>
                            <select name="vehicle_id" id="vehicle_id_kzw" class="form-control">
                                <option value="">Proponowany pojazd</option>
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
                                <option value="">Wybierz służbę</option>
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
                            <label for="reason_cancel_duty">Powód</label>
                            <textarea name="reason" id="reason_cancel_duty" class="form-control" rows="3" placeholder="Powód"></textarea>
                        </div>
                    </div>

                    <!-- Dzień wolny -->
                    <div class="application-fields" id="fields_day_off" style="display:none;">
                        <div class="form-group">
                            <label for="execution_date_day_off">Wybierz dzień</label>
                            <input type="date" name="execution_date" id="execution_date_day_off" class="form-control"
                                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                        </div>
                        <div class="form-group">
                            <label for="reason_day_off">Powód</label>
                            <textarea name="reason" id="reason_day_off" class="form-control" rows="3" placeholder="Powód"></textarea>
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
                            <label for="reason_vacation">Powód</label>
                            <textarea name="reason" id="reason_vacation" class="form-control" rows="3" placeholder="Powód"></textarea>
                        </div>
                    </div>

                    <!-- Stały pojazd / Zmiana stałego pojazdu -->
                    <div class="application-fields" id="fields_permanent_vehicle" style="display:none;">
                        <div class="form-group">
                            <label for="vehicle_id_perm">Pojazd do objęcia</label>
                            <select name="vehicle_id" id="vehicle_id_perm" class="form-control">
                                <option value="">Pojazd do objęcia</option>
                                <?php foreach ($vehicles as $v): ?>
                                    <option value="<?php echo $v['id']; ?>"><?php echo e($v['nr_poj']); ?><?php echo $v['model'] ? ' – ' . e($v['model']) : ''; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="application-fields" id="fields_change_vehicle" style="display:none;">
                        <div class="form-group">
                            <label for="vehicle_id_change">Pojazd do objęcia</label>
                            <select name="vehicle_id" id="vehicle_id_change" class="form-control">
                                <option value="">Pojazd do objęcia</option>
                                <?php foreach ($vehicles as $v): ?>
                                    <option value="<?php echo $v['id']; ?>"><?php echo e($v['nr_poj']); ?><?php echo $v['model'] ? ' – ' . e($v['model']) : ''; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Nieprzydzielanie pojazdów -->
                    <div class="application-fields" id="fields_no_vehicle_assign" style="display:none;">
                        <div class="form-group">
                            <label for="vehicle_ids">Wybierz pojazdy</label>
                            <select name="vehicle_ids[]" id="vehicle_ids" class="form-control" multiple size="5">
                                <?php foreach ($vehicles as $v): ?>
                                    <option value="<?php echo $v['id']; ?>"><?php echo e($v['nr_poj']); ?><?php echo $v['model'] ? ' – ' . e($v['model']) : ''; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Przytrzymaj Ctrl / Cmd, aby wybrać kilka pojazdów.</small>
                        </div>
                        <div class="form-group">
                            <label for="reason_no_vehicle">Powód</label>
                            <textarea name="reason" id="reason_no_vehicle" class="form-control" rows="3" placeholder="Powód"></textarea>
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
                            <label for="reason_change_status">Powód</label>
                            <textarea name="reason" id="reason_change_status" class="form-control" rows="3" placeholder="Powód"></textarea>
                        </div>
                    </div>

                    <!-- Zwolnienie -->
                    <div class="application-fields" id="fields_resignation" style="display:none;">
                        <div class="form-group">
                            <label for="reason_resignation">Powód</label>
                            <textarea name="reason" id="reason_resignation" class="form-control" rows="3" placeholder="Powód"></textarea>
                        </div>
                    </div>

                    <!-- Uwagi zawsze widoczne -->
                    <div class="form-group">
                        <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Uwagi (opcjonalne)"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" id="submit-btn">WYŚLIJ</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Panel informacyjny -->
    <div class="col col-12 col-md-6">
        <div class="card" id="info-panel">
            <div class="card-body" id="info-content">
                <?php foreach ($type_info as $type_key => $info): ?>
                <div class="type-info" id="info_<?php echo $type_key; ?>" style="display:none;">
                    <p><?php echo $info['desc']; ?></p>
                    <?php if (!empty($info['reqs'])): ?>
                    <p><strong>Wymagania:</strong></p>
                    <ul>
                        <?php foreach ($info['reqs'] as $req): ?>
                        <li <?php echo in_array($req, $info['req_danger'], true) ? 'style="color:var(--color-danger, #dc3545);"' : ''; ?>>
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

<?php if (!empty($history)): ?>
<div class="card" style="margin-top:2rem;">
    <div class="card-header">
        <h2 class="card-title">Historia wniosków</h2>
    </div>
    <div class="card-body">
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
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $app): ?>
                    <tr>
                        <td data-label="ID"><?php echo $app['id']; ?></td>
                        <td data-label="Typ"><?php echo e(Application::typeLabel($app['type'])); ?></td>
                        <td data-label="Status">
                            <span class="badge <?php echo Application::statusBadgeClass($app['status']); ?>">
                                <?php echo e(Application::statusLabel($app['status'])); ?>
                            </span>
                        </td>
                        <td data-label="Szczegóły">
                            <?php if ($app['execution_date']): ?>
                                Data: <?php echo e(date('d.m.Y', strtotime($app['execution_date']))); ?>
                            <?php elseif ($app['date_from']): ?>
                                <?php echo e(date('d.m.Y', strtotime($app['date_from']))); ?> – <?php echo e(date('d.m.Y', strtotime($app['date_to']))); ?>
                            <?php elseif ($app['schedule_date']): ?>
                                Służba <?php echo e(date('d.m.Y', strtotime($app['schedule_date']))); ?>
                            <?php elseif ($app['vehicle_nr']): ?>
                                Pojazd: <?php echo e($app['vehicle_nr']); ?>
                            <?php elseif ($app['work_days']): ?>
                                Dni: <?php
                                    $wd = is_string($app['work_days']) ? json_decode($app['work_days'], true) : $app['work_days'];
                                    $day_labels = ['monday'=>'Pn','tuesday'=>'Wt','wednesday'=>'Śr','thursday'=>'Czw','friday'=>'Pt','saturday'=>'Sb','sunday'=>'Nd'];
                                    echo e(implode(', ', array_map(fn($d) => $day_labels[$d] ?? $d, (array)$wd)));
                                ?>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td data-label="Uwagi rozpatrzenia">
                            <?php if (!empty($app['review_notes'])): ?>
                                <?php echo e($app['review_notes']); ?>
                            <?php elseif (!empty($app['reviewer_username'])): ?>
                                Rozpatrzył: <?php echo e(($app['reviewer_first'] ?? '') . ' ' . ($app['reviewer_last'] ?? '')); ?>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td data-label="Data złożenia"><?php echo e(date('d.m.Y H:i', strtotime($app['created_at']))); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

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
