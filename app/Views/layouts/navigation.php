<?php
$rbac = new RBAC();
$is_admin = $rbac->isAdmin();
$is_driver = $rbac->hasRole('Kierowca');
$is_dispatcher = $rbac->hasRole('Dyspozytor');
$is_management = $rbac->hasRole('Zarząd');
$is_nadzor = $rbac->hasRole('Nadzór Ruchu');
$is_hr = $rbac->hasRole('Kadry');
?>
<nav class="nav">
    <div class="container">
        <button class="nav-toggle" aria-label="Toggle menu" aria-expanded="true">☰</button>

        <ul class="nav-list">
            <?php if ($is_driver): ?>
                <li class="nav-item">
                    <a href="/driver/dashboard.php" class="<?php echo isActivePage('/driver/dashboard.php') ? 'active' : ''; ?>">
                        Panel Kierowcy
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/driver/schedule.php" class="<?php echo isActivePage('/driver/schedule.php') ? 'active' : ''; ?>">
                        Grafik
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/driver/route-card.php" class="<?php echo isActivePage('/driver/route-card.php') ? 'active' : ''; ?>">
                        Karta drogowa
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/driver/applications.php" class="<?php echo isActivePage('/driver/applications.php') ? 'active' : ''; ?>">
                        Wnioski
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/driver/report-incident.php" class="<?php echo isActivePage('/driver/report-incident.php') ? 'active' : ''; ?>">
                        Zglos awarie
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/driver/documentation.php" class="<?php echo isActivePage('/driver/documentation.php') ? 'active' : ''; ?>">
                        Dokumentacja
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($is_dispatcher || $is_management || $is_nadzor || $is_admin): ?>
                <li class="nav-item">
                    <a href="/dispatcher/dashboard.php" class="<?php echo isActivePage('/dispatcher/dashboard.php') ? 'active' : ''; ?>">
                        Panel Dyspozytora
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/dispatcher/fleet.php" class="<?php echo isActivePage('/dispatcher/fleet.php') ? 'active' : ''; ?>">
                        Status Floty
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/dispatcher/assign-schedule.php" class="<?php echo isActivePage('/dispatcher/assign-schedule.php') ? 'active' : ''; ?>">
                        Przydziel Grafik
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/dispatcher/schedules.php" class="<?php echo isActivePage('/dispatcher/schedules.php') ? 'active' : ''; ?>">
                        Grafiki
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/dispatcher/messages.php" class="<?php echo isActivePage('/dispatcher/messages.php') ? 'active' : ''; ?>">
                        Dyspozycje
                    </a>
                </li>
                <?php if (!$is_admin): ?>
                    <li class="nav-item">
                        <a href="/admin/vehicles/index.php" class="<?php echo isActivePage('/admin/vehicles') ? 'active' : ''; ?>">
                            Pojazdy
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/lines/index.php" class="<?php echo isActivePage('/admin/lines') ? 'active' : ''; ?>">
                            Linie
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/brigades/index.php" class="<?php echo isActivePage('/admin/brigades') ? 'active' : ''; ?>">
                            Brygady
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/brigades/generate-schedule.php" class="<?php echo isActivePage('/admin/brigades/generate-schedule') ? 'active' : ''; ?>">
                            Generator rozkładów
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/route-variants/index.php" class="<?php echo isActivePage('/admin/route-variants') ? 'active' : ''; ?>">
                            Warianty tras
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/incidents/index.php" class="<?php echo isActivePage('/admin/incidents') ? 'active' : ''; ?>">
                            Zgłoszenia
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/admin/applications/index.php" class="<?php echo isActivePage('/admin/applications') ? 'active' : ''; ?>">
                            Wnioski
                        </a>
                    </li>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($is_hr || $is_admin): ?>
                <li class="nav-item">
                    <a href="/hr/dashboard.php" class="<?php echo isActivePage('/hr/dashboard.php') ? 'active' : ''; ?>">
                        Panel Kadr
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/hr/work-hours.php" class="<?php echo isActivePage('/hr/work-hours.php') ? 'active' : ''; ?>">
                        ECP
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($is_admin): ?>
                <li class="nav-item">
                    <a href="/admin/dashboard.php" class="<?php echo isActivePage('/admin/dashboard.php') ? 'active' : ''; ?>">
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/users/index.php" class="<?php echo isActivePage('/admin/users') ? 'active' : ''; ?>">
                        Uzytkownicy
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/vehicles/index.php" class="<?php echo isActivePage('/admin/vehicles') ? 'active' : ''; ?>">
                        Pojazdy
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/lines/index.php" class="<?php echo isActivePage('/admin/lines') ? 'active' : ''; ?>">
                        Linie
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/stops/index.php" class="<?php echo isActivePage('/admin/stops') ? 'active' : ''; ?>">
                        Przystanki
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/brigades/index.php" class="<?php echo isActivePage('/admin/brigades') ? 'active' : ''; ?>">
                        Brygady
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/brigades/generate-schedule.php" class="<?php echo isActivePage('/admin/brigades/generate-schedule') ? 'active' : ''; ?>">
                        Generator rozkładów
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/route-variants/index.php" class="<?php echo isActivePage('/admin/route-variants') ? 'active' : ''; ?>">
                        Warianty tras
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/positions/index.php" class="<?php echo isActivePage('/admin/positions') ? 'active' : ''; ?>">
                        Stanowiska
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/roles/index.php" class="<?php echo isActivePage('/admin/roles') ? 'active' : ''; ?>">
                        Role
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/settings/index.php" class="<?php echo isActivePage('/admin/settings') ? 'active' : ''; ?>">
                        Ustawienia
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/logs/index.php" class="<?php echo isActivePage('/admin/logs') ? 'active' : ''; ?>">
                        Logi
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/incidents/index.php" class="<?php echo isActivePage('/admin/incidents') ? 'active' : ''; ?>">
                        Zgloszenia
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/applications/index.php" class="<?php echo isActivePage('/admin/applications') ? 'active' : ''; ?>">
                        Wnioski
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
