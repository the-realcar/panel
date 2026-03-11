<?php View::partial('layouts/header', ['page_title' => $page_title]); ?>

<?php $app = $application; ?>

<div class="page-header">
    <h1>📋 Wniosek #<?php echo $app['id']; ?></h1>
    <a href="/admin/applications/index.php" class="btn btn-secondary">← Powrót do listy</a>
</div>

<div class="row" style="align-items: flex-start; gap: 1.5rem;">
    <!-- Szczegóły wniosku -->
    <div class="col col-12 col-md-7">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Szczegóły</h2>
            </div>
            <div class="card-body">
                <table class="table table-detail">
                    <tbody>
                        <tr>
                            <th style="width:40%">Pracownik</th>
                            <td>
                                <strong><?php echo e($app['username']); ?></strong>
                                <?php if (!empty($app['first_name']) || !empty($app['last_name'])): ?>
                                    (<?php echo e(trim($app['first_name'] . ' ' . $app['last_name'])); ?>)
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Typ wniosku</th>
                            <td><?php echo e(Application::typeLabel($app['type'])); ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge <?php echo Application::statusBadgeClass($app['status']); ?>">
                                    <?php echo e(Application::statusLabel($app['status'])); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Data złożenia</th>
                            <td><?php echo e(date('d.m.Y H:i', strtotime($app['created_at']))); ?></td>
                        </tr>

                        <?php if ($app['execution_date']): ?>
                        <tr>
                            <th>Data wykonania / wolnego</th>
                            <td><?php echo e(date('d.m.Y', strtotime($app['execution_date']))); ?></td>
                        </tr>
                        <?php endif; ?>

                        <?php if ($app['date_from']): ?>
                        <tr>
                            <th>Okres</th>
                            <td><?php echo e(date('d.m.Y', strtotime($app['date_from']))); ?> – <?php echo e(date('d.m.Y', strtotime($app['date_to']))); ?></td>
                        </tr>
                        <?php endif; ?>

                        <?php if ($app['schedule_date']): ?>
                        <tr>
                            <th>Anulowana służba</th>
                            <td>
                                <?php echo e(date('d.m.Y', strtotime($app['schedule_date']))); ?>
                                <?php echo e(substr($app['sched_start'], 0, 5) . '–' . substr($app['sched_end'], 0, 5)); ?>
                                <?php if ($app['sched_line_number']): ?>
                                    | Linia <?php echo e($app['sched_line_number']); ?> <?php echo e($app['sched_line_name'] ?? ''); ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>

                        <?php if ($app['vehicle_nr']): ?>
                        <tr>
                            <th>Pojazd</th>
                            <td>
                                <?php echo e($app['vehicle_nr']); ?>
                                <?php echo $app['vehicle_model'] ? ' – ' . e($app['vehicle_model']) : ''; ?>
                                <?php echo $app['vehicle_plate'] ? ' (' . e($app['vehicle_plate']) . ')' : ''; ?>
                            </td>
                        </tr>
                        <?php endif; ?>

                        <?php if ($app['vehicles_json']): ?>
                        <tr>
                            <th>Pojazdy (nieprzydzielanie)</th>
                            <td><?php echo e(implode(', ', (array)(is_string($app['vehicles_json']) ? json_decode($app['vehicles_json'], true) : $app['vehicles_json']))); ?></td>
                        </tr>
                        <?php endif; ?>

                        <?php if ($app['work_days']): ?>
                        <tr>
                            <th>Nowe dni pracy</th>
                            <td>
                                <?php
                                $wd = is_string($app['work_days']) ? json_decode($app['work_days'], true) : $app['work_days'];
                                $day_labels = ['monday'=>'Poniedziałek','tuesday'=>'Wtorek','wednesday'=>'Środa','thursday'=>'Czwartek','friday'=>'Piątek','saturday'=>'Sobota','sunday'=>'Niedziela'];
                                echo e(implode(', ', array_map(fn($d) => $day_labels[$d] ?? $d, (array)$wd)));
                                ?>
                            </td>
                        </tr>
                        <?php endif; ?>

                        <?php if (!empty($app['reason'])): ?>
                        <tr>
                            <th>Powód</th>
                            <td><?php echo nl2br(e($app['reason'])); ?></td>
                        </tr>
                        <?php endif; ?>

                        <?php if (!empty($app['notes'])): ?>
                        <tr>
                            <th>Uwagi</th>
                            <td><?php echo nl2br(e($app['notes'])); ?></td>
                        </tr>
                        <?php endif; ?>

                        <?php if ($app['reviewed_at']): ?>
                        <tr>
                            <th>Rozpatrzono</th>
                            <td>
                                <?php echo e(date('d.m.Y H:i', strtotime($app['reviewed_at']))); ?>
                                przez <?php echo e(trim(($app['reviewer_first'] ?? '') . ' ' . ($app['reviewer_last'] ?? '')) ?: $app['reviewer_username'] ?? '—'); ?>
                            </td>
                        </tr>
                        <?php if (!empty($app['review_notes'])): ?>
                        <tr>
                            <th>Notatka rozpatrzenia</th>
                            <td><?php echo nl2br(e($app['review_notes'])); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Formularz rozpatrzenia -->
    <?php if ($app['status'] === 'pending'): ?>
    <div class="col col-12 col-md-5">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Rozpatrz wniosek</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="" class="form">
                    <?php echo csrfField(); ?>

                    <div class="form-group">
                        <label for="review_notes">Notatka (opcjonalna)</label>
                        <textarea name="review_notes" id="review_notes" class="form-control" rows="4"
                                  placeholder="Uzasadnienie decyzji..."></textarea>
                    </div>

                    <div class="btn-group" style="gap: 0.5rem;">
                        <button type="submit" name="status" value="approved" class="btn btn-success">
                            ✅ Zatwierdź
                        </button>
                        <button type="submit" name="status" value="rejected" class="btn btn-danger"
                                onclick="return confirm('Czy na pewno chcesz odrzucić ten wniosek?');">
                            ❌ Odrzuć
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php View::partial('layouts/footer'); ?>
