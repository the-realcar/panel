<?php
/**
 * Route Card Form
 * Panel Pracowniczy Firma KOT
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/RBAC.php';
require_once __DIR__ . '/../../core/Validator.php';
require_once __DIR__ . '/../../includes/functions.php';

// Require login
requireLogin();

// Check if user has driver role
$rbac = new RBAC();
if (!$rbac->hasRole('Kierowca') && !$rbac->isAdmin()) {
    setFlashMessage('error', 'Brak dostępu do panelu kierowcy.');
    header('Location: /public/index.php');
    exit;
}

$db = new Database();
$user_id = getCurrentUserId();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Błąd weryfikacji formularza. Spróbuj ponownie.');
        header('Location: /public/driver/route-card.php');
        exit;
    }
    
    // Validate input
    $validator = new Validator($_POST);
    $validator->required('vehicle_id', 'Pojazd jest wymagany.')
              ->required('line_id', 'Linia jest wymagana.')
              ->required('route_date', 'Data trasy jest wymagana.')
              ->required('start_time', 'Godzina rozpoczęcia jest wymagana.')
              ->required('end_time', 'Godzina zakończenia jest wymagana.')
              ->required('start_km', 'Początkowy stan licznika jest wymagany.')
              ->required('end_km', 'Końcowy stan licznika jest wymagany.')
              ->numeric('start_km', 'Początkowy stan licznika musi być liczbą.')
              ->numeric('end_km', 'Końcowy stan licznika musi być liczbą.')
              ->numeric('passengers_count', 'Liczba pasażerów musi być liczbą.');
    
    if (!empty($_POST['fuel_start'])) {
        $validator->numeric('fuel_start', 'Początkowy stan paliwa musi być liczbą.');
    }
    if (!empty($_POST['fuel_end'])) {
        $validator->numeric('fuel_end', 'Końcowy stan paliwa musi być liczbą.');
    }
    
    if ($validator->fails()) {
        $errors = $validator->getErrors();
        setFlashMessage('error', 'Popraw błędy w formularzu: ' . implode(', ', $errors));
    } else {
        // Additional validation
        if ($_POST['end_km'] < $_POST['start_km']) {
            setFlashMessage('error', 'Końcowy stan licznika nie może być mniejszy niż początkowy.');
        } elseif (!empty($_POST['fuel_start']) && !empty($_POST['fuel_end']) && $_POST['fuel_end'] > $_POST['fuel_start']) {
            setFlashMessage('error', 'Końcowy stan paliwa nie może być większy niż początkowy.');
        } else {
            // Insert route card
            $insert_query = "
                INSERT INTO route_cards 
                (user_id, vehicle_id, line_id, route_date, start_time, end_time, 
                 start_km, end_km, fuel_start, fuel_end, passengers_count, notes, status)
                VALUES 
                (:user_id, :vehicle_id, :line_id, :route_date, :start_time, :end_time,
                 :start_km, :end_km, :fuel_start, :fuel_end, :passengers_count, :notes, 'completed')
            ";
            
            $params = [
                ':user_id' => $user_id,
                ':vehicle_id' => $_POST['vehicle_id'],
                ':line_id' => $_POST['line_id'],
                ':route_date' => $_POST['route_date'],
                ':start_time' => $_POST['start_time'],
                ':end_time' => $_POST['end_time'],
                ':start_km' => $_POST['start_km'],
                ':end_km' => $_POST['end_km'],
                ':fuel_start' => !empty($_POST['fuel_start']) ? $_POST['fuel_start'] : null,
                ':fuel_end' => !empty($_POST['fuel_end']) ? $_POST['fuel_end'] : null,
                ':passengers_count' => !empty($_POST['passengers_count']) ? $_POST['passengers_count'] : 0,
                ':notes' => !empty($_POST['notes']) ? trim($_POST['notes']) : null
            ];
            
            try {
                $db->execute($insert_query, $params);
                setFlashMessage('success', 'Karta drogowa została zapisana pomyślnie.');
                header('Location: /public/driver/route-card.php');
                exit;
            } catch (Exception $e) {
                setFlashMessage('error', 'Błąd podczas zapisywania karty drogowej: ' . $e->getMessage());
            }
        }
    }
}

// Get vehicles list
$vehicles = $db->query("SELECT id, vehicle_number, model, registration_plate FROM vehicles WHERE status != 'broken' ORDER BY vehicle_number");

// Get lines list
$lines = $db->query("SELECT id, line_number, name FROM lines WHERE active = TRUE ORDER BY line_number");

// Get recent route cards (last 10)
$recent_cards_query = "
    SELECT rc.*, 
           v.vehicle_number, v.model,
           l.line_number, l.name as line_name
    FROM route_cards rc
    LEFT JOIN vehicles v ON rc.vehicle_id = v.id
    LEFT JOIN lines l ON rc.line_id = l.id
    WHERE rc.user_id = :user_id
    ORDER BY rc.route_date DESC, rc.created_at DESC
    LIMIT 10
";
$recent_cards = $db->query($recent_cards_query, [':user_id' => $user_id]);

$page_title = 'Karta Drogowa';
include __DIR__ . '/../../includes/header.php';
?>

<h1>📝 Karta Drogowa</h1>
<p class="text-muted">Wypełnij kartę drogową po zakończeniu trasy</p>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Nowa karta drogowa</h2>
    </div>
    <div class="card-body">
        <form method="POST" action="" class="form">
            <?php echo csrfField(); ?>
            
            <div class="row">
                <div class="col col-12 col-md-6">
                    <div class="form-group">
                        <label for="vehicle_id">Pojazd *</label>
                        <select name="vehicle_id" id="vehicle_id" class="form-control" required>
                            <option value="">Wybierz pojazd</option>
                            <?php foreach ($vehicles as $vehicle): ?>
                                <option value="<?php echo $vehicle['id']; ?>">
                                    <?php echo e($vehicle['vehicle_number']); ?> 
                                    <?php if ($vehicle['registration_plate']): ?>
                                        (<?php echo e($vehicle['registration_plate']); ?>)
                                    <?php endif; ?>
                                    <?php if ($vehicle['model']): ?>
                                        - <?php echo e($vehicle['model']); ?>
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col col-12 col-md-6">
                    <div class="form-group">
                        <label for="line_id">Linia *</label>
                        <select name="line_id" id="line_id" class="form-control" required>
                            <option value="">Wybierz linię</option>
                            <?php foreach ($lines as $line): ?>
                                <option value="<?php echo $line['id']; ?>">
                                    <?php echo e($line['line_number']); ?> - <?php echo e($line['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col col-12 col-md-4">
                    <div class="form-group">
                        <label for="route_date">Data trasy *</label>
                        <input type="date" name="route_date" id="route_date" class="form-control" 
                               value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                
                <div class="col col-12 col-md-4">
                    <div class="form-group">
                        <label for="start_time">Godzina rozpoczęcia *</label>
                        <input type="time" name="start_time" id="start_time" class="form-control" required>
                    </div>
                </div>
                
                <div class="col col-12 col-md-4">
                    <div class="form-group">
                        <label for="end_time">Godzina zakończenia *</label>
                        <input type="time" name="end_time" id="end_time" class="form-control" required>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col col-12 col-md-3">
                    <div class="form-group">
                        <label for="start_km">Początkowy stan licznika (km) *</label>
                        <input type="number" name="start_km" id="start_km" class="form-control" 
                               min="0" step="1" required>
                    </div>
                </div>
                
                <div class="col col-12 col-md-3">
                    <div class="form-group">
                        <label for="end_km">Końcowy stan licznika (km) *</label>
                        <input type="number" name="end_km" id="end_km" class="form-control" 
                               min="0" step="1" required>
                    </div>
                </div>
                
                <div class="col col-12 col-md-3">
                    <div class="form-group">
                        <label for="fuel_start">Początkowy stan paliwa (l)</label>
                        <input type="number" name="fuel_start" id="fuel_start" class="form-control" 
                               min="0" step="0.01">
                    </div>
                </div>
                
                <div class="col col-12 col-md-3">
                    <div class="form-group">
                        <label for="fuel_end">Końcowy stan paliwa (l)</label>
                        <input type="number" name="fuel_end" id="fuel_end" class="form-control" 
                               min="0" step="0.01">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col col-12 col-md-6">
                    <div class="form-group">
                        <label for="passengers_count">Liczba pasażerów</label>
                        <input type="number" name="passengers_count" id="passengers_count" class="form-control" 
                               min="0" step="1" value="0">
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="notes">Uwagi</label>
                <textarea name="notes" id="notes" class="form-control" rows="4" 
                          placeholder="Dodatkowe informacje o trasie..."></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    Zapisz kartę drogową
                </button>
                <a href="/public/driver/dashboard.php" class="btn btn-secondary">
                    Anuluj
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Ostatnie karty drogowe</h2>
    </div>
    <div class="card-body">
        <?php if (empty($recent_cards)): ?>
            <p class="text-muted">Brak zapisanych kart drogowych.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Pojazd</th>
                            <th>Linia</th>
                            <th>Godziny</th>
                            <th>Przejechane km</th>
                            <th>Pasażerowie</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_cards as $card): ?>
                        <tr>
                            <td data-label="Data">
                                <?php echo formatDate($card['route_date'], 'd.m.Y'); ?>
                            </td>
                            <td data-label="Pojazd">
                                <?php echo e($card['vehicle_number'] ?? 'Brak'); ?>
                                <?php if ($card['model']): ?>
                                    <br><small class="text-muted"><?php echo e($card['model']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td data-label="Linia">
                                <strong><?php echo e($card['line_number'] ?? 'Brak'); ?></strong>
                                <?php if ($card['line_name']): ?>
                                    <br><small class="text-muted"><?php echo e($card['line_name']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td data-label="Godziny">
                                <?php echo formatTime($card['start_time']); ?> - 
                                <?php echo formatTime($card['end_time']); ?>
                            </td>
                            <td data-label="Przejechane km">
                                <?php echo ($card['end_km'] - $card['start_km']); ?> km
                            </td>
                            <td data-label="Pasażerowie">
                                <?php echo $card['passengers_count'] ?? 0; ?>
                            </td>
                            <td data-label="Status">
                                <?php echo getStatusBadge($card['status']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
