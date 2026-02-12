<?php
/**
 * Navigation Menu
 * Panel Pracowniczy Firma KOT
 */

require_once __DIR__ . '/../core/RBAC.php';

$rbac = new RBAC();
$is_admin = $rbac->isAdmin();
$is_driver = $rbac->hasRole('Kierowca');
$is_dispatcher = $rbac->hasRole('Dyspozytor');
?>
<nav class="nav">
    <div class="container">
        <button class="nav-toggle hide-mobile" aria-label="Toggle menu">☰</button>
        
        <ul class="nav-list">
            <?php if ($is_admin): ?>
                <li class="nav-item">
                    <a href="/admin/dashboard.php" class="<?php echo isActivePage('/admin/dashboard.php') ? 'active' : ''; ?>">
                        📊 Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/users/index.php" class="<?php echo isActivePage('/admin/users') ? 'active' : ''; ?>">
                        👥 Użytkownicy
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/vehicles/index.php" class="<?php echo isActivePage('/admin/vehicles') ? 'active' : ''; ?>">
                        🚌 Pojazdy
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/lines/index.php" class="<?php echo isActivePage('/admin/lines') ? 'active' : ''; ?>">
                        🚏 Linie
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/positions/index.php" class="<?php echo isActivePage('/admin/positions') ? 'active' : ''; ?>">
                        💼 Stanowiska
                    </a>
                </li>
            <?php endif; ?>
            
            <?php if ($is_driver): ?>
                <li class="nav-item">
                    <a href="/driver/dashboard.php" class="<?php echo isActivePage('/driver/dashboard.php') ? 'active' : ''; ?>">
                        📊 Mój Panel
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/driver/schedule.php" class="<?php echo isActivePage('/driver/schedule.php') ? 'active' : ''; ?>">
                        📅 Grafik
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/driver/route-card.php" class="<?php echo isActivePage('/driver/route-card.php') ? 'active' : ''; ?>">
                        📝 Karta drogowa
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/driver/report-incident.php" class="<?php echo isActivePage('/driver/report-incident.php') ? 'active' : ''; ?>">
                        ⚠️ Zgłoś awarię
                    </a>
                </li>
            <?php endif; ?>
            
            <?php if ($is_dispatcher): ?>
                <li class="nav-item">
                    <a href="/admin/dashboard.php" class="<?php echo isActivePage('/admin/dashboard.php') ? 'active' : ''; ?>">
                        📊 Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/vehicles/index.php" class="<?php echo isActivePage('/admin/vehicles') ? 'active' : ''; ?>">
                        🚌 Pojazdy
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/admin/lines/index.php" class="<?php echo isActivePage('/admin/lines') ? 'active' : ''; ?>">
                        🚏 Linie
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
