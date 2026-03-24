<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="page-header">
    <h1>📋 Generator Rozkladow - Linia <?php echo e($line['line_number']); ?></h1>
    <div class="header-actions">
        <a href="/management/schedule-generator/index.php" class="btn btn-secondary">← Powrot</a>
        <?php if ($timetable): ?>
            <button onclick="window.print()" class="btn btn-primary">🖨️ Drukuj rozklad</button>
        <?php endif; ?>
    </div>
</div>

<!-- Formularz wyboru wariantu -->
<div class="card no-print">
    <div class="card-header">
        <h2>Wybierz wariant trasy</h2>
    </div>
    <div class="card-body">
        <?php if (empty($variants)): ?>
            <div class="empty-state">
                <p>Brak aktywnych wariantow tras dla tej linii.</p>
            </div>
        <?php else: ?>
            <form method="GET" action="/management/schedule-generator/generate.php">
                <input type="hidden" name="line_id" value="<?php echo (int)$line['id']; ?>">
                <div class="form-group">
                    <label for="variant_id">Wariant trasy</label>
                    <select name="variant_id" id="variant_id" class="form-control" required>
                        <option value="">-- wybierz --</option>
                        <?php foreach ($variants as $v): ?>
                            <option value="<?php echo (int)$v['id']; ?>"
                                <?php echo ($selected_variant && (int)$selected_variant['id'] === (int)$v['id']) ? 'selected' : ''; ?>>
                                <?php echo e($v['variant_name']); ?>
                                <?php if ($v['direction']): ?>
                                    (<?php echo e($v['direction']); ?>)
                                <?php endif; ?>
                                - <?php echo (int)$v['stops_count']; ?> przystankow
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Generuj</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if ($selected_variant && empty($timetable) && !empty($stops)): ?>
    <div class="alert alert-warning">
        <?php if (!$departures_available): ?>
            Tabela odjazdow brygad nie jest dostępna w tej bazie danych. Generator nie może policzyć rozkładu dla wybranego kierunku.
        <?php else: ?>
            Brak odjazdow dla wybranego wariantu trasy. Dodaj odjazdy brygadom przypisanym do linii <?php echo e($line['line_number']); ?>.
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if ($selected_variant && empty($stops)): ?>
    <div class="alert alert-warning">
        Wybrany wariant trasy nie ma przypisanych przystankow. Dodaj przystanki w zarzadzaniu wariantami tras.
    </div>
<?php endif; ?>

<?php if ($timetable && $selected_variant && !empty($stops)): ?>
    <!-- Naglowek rozkladu (widoczny rowniez przy druku) -->
    <div class="card schedule-print-header">
        <div class="card-body" style="text-align:center;">
            <h2 style="margin:0 0 0.25rem;">
                Rozklad jazdy - Linia <?php echo e($line['line_number']); ?>
            </h2>
            <?php if ($line['name']): ?>
                <p style="margin:0 0 0.25rem;"><?php echo e($line['name']); ?></p>
            <?php endif; ?>
            <p style="margin:0; font-size:0.9rem; color:var(--text-muted);">
                Kierunek: <?php echo e($selected_variant['variant_name']); ?>
                <?php if ($selected_variant['direction']): ?>
                    (<?php echo e($selected_variant['direction']); ?>)
                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- Tabela rozkladu -->
    <div class="card">
        <div class="card-header">
            <h2>
                Rozklad jazdy
                <small style="font-weight:normal; font-size:0.85rem;">
                    (<?php echo count($timetable); ?> kursow, <?php echo count($stops); ?> przystankow)
                </small>
            </h2>
        </div>
        <div class="card-body" style="overflow-x:auto;">
            <table class="data-table schedule-table">
                <thead>
                    <tr>
                        <th>Kurs</th>
                        <?php foreach ($stops as $stop): ?>
                            <th class="stop-header <?php echo $stop['is_timing_point'] ? 'timing-point' : ''; ?>">
                                <?php echo e($stop['stop_name']); ?>
                                <?php if ($stop['platform_number'] && $stop['platform_number'] !== '01'): ?>
                                    <br><small>st. <?php echo e($stop['platform_number']); ?></small>
                                <?php endif; ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($timetable as $row): ?>
                        <tr>
                            <td class="brigade-cell">
                                <strong><?php echo e($row['departure_time']); ?></strong>
                                <?php if ($row['brigade_number']): ?>
                                    <br><small>br. <?php echo e($row['brigade_number']); ?></small>
                                <?php endif; ?>
                            </td>
                            <?php foreach ($row['stop_times'] as $time): ?>
                                <td class="time-cell"><?php echo e($time); ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Klasyczny widok: godzina | minuty (z pierwszego przystanku) -->
    <div class="card">
        <div class="card-header">
            <h2>Odjazdy z przystanku: <?php echo e($stops[0]['stop_name']); ?></h2>
        </div>
        <div class="card-body">
            <?php
            $by_hour = [];
            foreach ($timetable as $row) {
                $hour   = (int)substr($row['departure_time'], 0, 2);
                $minute = substr($row['departure_time'], 3, 2);
                $by_hour[$hour][] = $minute;
            }
            ksort($by_hour);
            ?>
            <table class="data-table hourly-table">
                <thead>
                    <tr>
                        <th>Godz.</th>
                        <th>Minuty</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($by_hour as $hour => $minutes): ?>
                        <tr>
                            <td class="hour-cell"><strong><?php echo sprintf('%02d', $hour); ?></strong></td>
                            <td class="minutes-cell"><?php echo implode('  ', $minutes); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<style>
.schedule-table th.stop-header {
    min-width: 100px;
    font-size: 0.8rem;
    text-align: center;
    vertical-align: bottom;
}
.schedule-table th.timing-point {
    background-color: var(--primary-light, #e8f0fe);
    font-weight: 700;
}
.schedule-table td.time-cell {
    text-align: center;
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
}
.schedule-table td.brigade-cell {
    white-space: nowrap;
    font-size: 0.85rem;
}
.hourly-table td.hour-cell {
    width: 60px;
    text-align: right;
    padding-right: 1rem;
    font-size: 1.1rem;
}
.hourly-table td.minutes-cell {
    font-variant-numeric: tabular-nums;
    letter-spacing: 0.1em;
    font-size: 0.95rem;
}
@media print {
    .no-print { display: none !important; }
    .btn { display: none !important; }
    .schedule-print-header { border: 1px solid #000; }
    .data-table { font-size: 0.75rem; }
    .schedule-table th.stop-header { min-width: 70px; }
}
</style>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
