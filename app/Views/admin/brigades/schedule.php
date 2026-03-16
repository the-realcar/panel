<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<?php
function schedShiftMinutes($start, $end) {
    if (!$start || !$end) return null;
    $fmt = strlen((string)$start) === 5 ? 'H:i' : 'H:i:s';
    $s = DateTime::createFromFormat($fmt, (string)$start);
    $e = DateTime::createFromFormat($fmt, (string)$end);
    if (!$s || !$e) return null;
    $diff = $e->getTimestamp() - $s->getTimestamp();
    if ($diff < 0) $diff += 86400;
    return (int)($diff / 60);
}

function schedFormatTime($mins) {
    if ($mins === null || $mins <= 0) return null;
    $h = intdiv($mins, 60);
    $m = $mins % 60;
    if ($h > 0 && $m > 0) return $h . ' godz. ' . $m . ' min';
    if ($h > 0) return $h . ' godz.';
    return $m . ' min';
}

function schedHHMM($time) {
    return $time ? substr((string)$time, 0, 5) : '';
}
?>

<div class="page-header">
    <h1>📋 Plan zmian brygad</h1>
    <a href="/admin/brigades/index.php" class="btn btn-secondary">⬅️ Brygady</a>
</div>

<div class="card">
    <div class="card-header">
        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 1rem;">
            <form method="GET" class="form-inline" style="flex-shrink: 0;">
                <label for="line">Filtruj po linii:</label>
                <select name="line" id="line" class="form-control" onchange="this.form.submit()">
                    <option value="">Wszystkie linie</option>
                    <?php foreach ($lines as $line): ?>
                        <option value="<?php echo $line['id']; ?>" <?php echo $line_filter == $line['id'] ? 'selected' : ''; ?>>
                            <?php echo e($line['line_number'] . ' – ' . $line['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <div style="display: flex; gap: 1.2rem; font-size: 0.83rem; flex-shrink: 0;">
                <span style="display: inline-flex; align-items: center; gap: 0.4rem;">
                    <span style="display: inline-block; width: 16px; height: 16px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 2px;"></span>
                    Brygada szczytowa
                </span>
                <span style="display: inline-flex; align-items: center; gap: 0.4rem;">
                    <span style="display: inline-block; width: 16px; height: 16px; background: #cce5ff; border: 1px solid #74b9ff; border-radius: 2px;"></span>
                    Brygada jednozmianowa
                </span>
            </div>
        </div>
    </div>

    <div class="card-body" style="overflow-x: auto; padding: 0;">
        <?php if (empty($brigades)): ?>
            <p class="text-muted" style="padding: 1rem;">Brak brygad do wyświetlenia.</p>
        <?php else: ?>
        <table style="width: 100%; min-width: 1300px; font-size: 0.81rem; border-collapse: collapse;">
            <thead>
                <tr>
                    <th rowspan="2" style="vertical-align: middle; border: 1px solid #dee2e6; padding: 5px 8px; background: #f8f9fa; white-space: nowrap;">Linia,<br>brygada</th>
                    <th rowspan="2" style="vertical-align: middle; border: 1px solid #dee2e6; padding: 5px 8px; background: #f8f9fa; white-space: nowrap;">Łączny czas<br>pracy</th>
                    <th colspan="6" style="text-align: center; border: 1px solid #dee2e6; padding: 5px 8px; background: #d4edda; font-weight: 700;">A – Poranna zmiana</th>
                    <th rowspan="2" style="vertical-align: middle; border: 1px solid #dee2e6; padding: 5px 8px; background: #fff8e1; white-space: nowrap;">Podmiana</th>
                    <th colspan="6" style="text-align: center; border: 1px solid #dee2e6; padding: 5px 8px; background: #d1ecf1; font-weight: 700;">B – Popołudniowa zmiana</th>
                </tr>
                <tr>
                    <th style="border: 1px solid #dee2e6; padding: 5px 8px; background: #e8f5e9; white-space: nowrap;">1. Odjazd</th>
                    <th style="border: 1px solid #dee2e6; padding: 5px 8px; background: #e8f5e9;">1. Przystanek</th>
                    <th style="border: 1px solid #dee2e6; padding: 5px 8px; background: #e8f5e9; white-space: nowrap;">Pojemność</th>
                    <th style="border: 1px solid #dee2e6; padding: 5px 8px; background: #e8f5e9; white-space: nowrap;">Czas pracy</th>
                    <th style="border: 1px solid #dee2e6; padding: 5px 8px; background: #e8f5e9; white-space: nowrap;">Ostatni przyjazd</th>
                    <th style="border: 1px solid #dee2e6; padding: 5px 8px; background: #e8f5e9;">Przyst. końcowy</th>
                    <th style="border: 1px solid #dee2e6; padding: 5px 8px; background: #e3f2fd; white-space: nowrap;">1. Odjazd</th>
                    <th style="border: 1px solid #dee2e6; padding: 5px 8px; background: #e3f2fd;">1. Przystanek</th>
                    <th style="border: 1px solid #dee2e6; padding: 5px 8px; background: #e3f2fd; white-space: nowrap;">Pojemność</th>
                    <th style="border: 1px solid #dee2e6; padding: 5px 8px; background: #e3f2fd; white-space: nowrap;">Czas pracy</th>
                    <th style="border: 1px solid #dee2e6; padding: 5px 8px; background: #e3f2fd; white-space: nowrap;">Ostatni przyjazd</th>
                    <th style="border: 1px solid #dee2e6; padding: 5px 8px; background: #e3f2fd;">Przyst. końcowy</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($brigades as $brigade): ?>
                <?php
                    $hasA = !empty($brigade['shift_a_start']) && !empty($brigade['shift_a_end']);
                    $hasB = !empty($brigade['shift_b_start']) && !empty($brigade['shift_b_end']);
                    $minsA = schedShiftMinutes($brigade['shift_a_start'] ?? null, $brigade['shift_a_end'] ?? null);
                    $minsB = schedShiftMinutes($brigade['shift_b_start'] ?? null, $brigade['shift_b_end'] ?? null);
                    $totalMins = ($minsA ?? 0) + ($minsB ?? 0);

                    if (!empty($brigade['is_peak'])) {
                        $rowBg = ($brigade['peak_type'] ?? '') === 'single_shift'
                            ? 'background-color: #cce5ff;'
                            : 'background-color: #fff3cd;';
                    } else {
                        $rowBg = '';
                    }

                    if ($hasA && $hasB) {
                        $podmMins = schedShiftMinutes($brigade['shift_a_end'], $brigade['shift_b_start']);
                        $podmText = schedHHMM($brigade['shift_a_end']) . '–' . schedHHMM($brigade['shift_b_start']);
                        if ($podmMins !== null && $podmMins > 0) {
                            $podmText .= ', czyli ' . schedFormatTime($podmMins);
                        } else {
                            $podmText = 'Brak przerwy między zmianami';
                        }
                    } elseif (!$hasB) {
                        $podmText = 'Bez podmiany, tylko jeden kierowca';
                    } else {
                        $podmText = 'Tylko zmiana B';
                    }

                    $dash = '<span class="text-muted">—</span>';
                ?>
                <tr style="<?php echo $rowBg; ?>">
                    <td style="border: 1px solid #dee2e6; padding: 4px 8px; font-weight: 600; white-space: nowrap;">
                        <?php echo e($brigade['line_number'] . '/' . $brigade['brigade_number']); ?>
                    </td>
                    <td style="border: 1px solid #dee2e6; padding: 4px 8px; white-space: nowrap;">
                        <?php echo $totalMins > 0 ? e(schedFormatTime($totalMins)) : $dash; ?>
                    </td>
                    <!-- Zmiana A -->
                    <td style="border: 1px solid #dee2e6; padding: 4px 8px; white-space: nowrap; background: rgba(232,245,233,0.45);">
                        <?php echo $hasA ? e(schedHHMM($brigade['shift_a_start'])) : $dash; ?>
                    </td>
                    <td style="border: 1px solid #dee2e6; padding: 4px 8px; background: rgba(232,245,233,0.45);">
                        <?php echo !empty($brigade['shift_a_first_stop']) ? e($brigade['shift_a_first_stop']) : $dash; ?>
                    </td>
                    <td style="border: 1px solid #dee2e6; padding: 4px 8px; white-space: nowrap; background: rgba(232,245,233,0.45);">
                        <?php echo !empty($brigade['shift_a_capacity']) ? e($brigade['shift_a_capacity']) : $dash; ?>
                    </td>
                    <td style="border: 1px solid #dee2e6; padding: 4px 8px; white-space: nowrap; background: rgba(232,245,233,0.45);">
                        <?php echo $minsA !== null ? e(schedFormatTime($minsA)) : $dash; ?>
                    </td>
                    <td style="border: 1px solid #dee2e6; padding: 4px 8px; white-space: nowrap; background: rgba(232,245,233,0.45);">
                        <?php echo $hasA ? e(schedHHMM($brigade['shift_a_end'])) : $dash; ?>
                    </td>
                    <td style="border: 1px solid #dee2e6; padding: 4px 8px; background: rgba(232,245,233,0.45);">
                        <?php echo !empty($brigade['shift_a_last_stop']) ? e($brigade['shift_a_last_stop']) : $dash; ?>
                    </td>
                    <!-- Podmiana -->
                    <td style="border: 1px solid #dee2e6; padding: 4px 8px; background: rgba(255,248,225,0.7); font-size: 0.78rem;">
                        <?php echo e($podmText); ?>
                    </td>
                    <!-- Zmiana B -->
                    <td style="border: 1px solid #dee2e6; padding: 4px 8px; white-space: nowrap; background: rgba(227,242,253,0.45);">
                        <?php echo $hasB ? e(schedHHMM($brigade['shift_b_start'])) : $dash; ?>
                    </td>
                    <td style="border: 1px solid #dee2e6; padding: 4px 8px; background: rgba(227,242,253,0.45);">
                        <?php echo !empty($brigade['shift_b_first_stop']) ? e($brigade['shift_b_first_stop']) : $dash; ?>
                    </td>
                    <td style="border: 1px solid #dee2e6; padding: 4px 8px; white-space: nowrap; background: rgba(227,242,253,0.45);">
                        <?php echo !empty($brigade['shift_b_capacity']) ? e($brigade['shift_b_capacity']) : $dash; ?>
                    </td>
                    <td style="border: 1px solid #dee2e6; padding: 4px 8px; white-space: nowrap; background: rgba(227,242,253,0.45);">
                        <?php echo $minsB !== null ? e(schedFormatTime($minsB)) : $dash; ?>
                    </td>
                    <td style="border: 1px solid #dee2e6; padding: 4px 8px; white-space: nowrap; background: rgba(227,242,253,0.45);">
                        <?php echo $hasB ? e(schedHHMM($brigade['shift_b_end'])) : $dash; ?>
                    </td>
                    <td style="border: 1px solid #dee2e6; padding: 4px 8px; background: rgba(227,242,253,0.45);">
                        <?php echo !empty($brigade['shift_b_last_stop']) ? e($brigade['shift_b_last_stop']) : $dash; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php View::partial('layouts/footer'); ?>
