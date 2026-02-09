<?php
/**
 * Report Incident Form
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
        header('Location: /public/driver/report-incident.php');
        exit;
    }
    
    // Validate input
    $validator = new Validator($_POST);
    $validator->required('incident_type', 'Typ incydentu jest wymagany.')
              ->required('severity', 'Poziom ważności jest wymagany.')
              ->required('title', 'Tytuł jest wymagany.')
              ->required('description', 'Opis incydentu jest wymagany.')
              ->required('incident_date', 'Data incydentu jest wymagana.')
              ->minLength('title', 5, 'Tytuł musi mieć co najmniej 5 znaków.')
              ->minLength('description', 10, 'Opis musi mieć co najmniej 10 znaków.');
    
    if ($validator->fails()) {
        $errors = $validator->getErrors();
        setFlashMessage('error', 'Popraw błędy w formularzu: ' . implode(', ', $errors));
    } else {
        // Validate incident type and severity
        $allowed_types = ['breakdown', 'accident', 'complaint', 'other'];
        $allowed_severities = ['low', 'medium', 'high', 'critical'];
        
        if (!in_array($_POST['incident_type'], $allowed_types)) {
            setFlashMessage('error', 'Nieprawidłowy typ incydentu.');
        } elseif (!in_array($_POST['severity'], $allowed_severities)) {
            setFlashMessage('error', 'Nieprawidłowy poziom ważności.');
        } else {
            // Insert incident
            $insert_query = "
                INSERT INTO incidents 
                (reported_by, vehicle_id, incident_type, severity, title, description, incident_date, status)
                VALUES 
                (:reported_by, :vehicle_id, :incident_type, :severity, :title, :description, :incident_date, 'open')
            ";
            
            $params = [
                ':reported_by' => $user_id,
                ':vehicle_id' => !empty($_POST['vehicle_id']) ? $_POST['vehicle_id'] : null,
                ':incident_type' => $_POST['incident_type'],
                ':severity' => $_POST['severity'],
                ':title' => trim($_POST['title']),
                ':description' => trim($_POST['description']),
                ':incident_date' => $_POST['incident_date']
            ];
            
            try {
                $db->execute($insert_query, $params);
                setFlashMessage('success', 'Zgłoszenie zostało zapisane pomyślnie. Dziękujemy za zgłoszenie.');
                header('Location: /public/driver/report-incident.php');
                exit;
            } catch (Exception $e) {
                setFlashMessage('error', 'Błąd podczas zapisywania zgłoszenia: ' . $e->getMessage());
            }
        }
    }
}

// Get vehicles list
$vehicles = $db->query("SELECT id, vehicle_number, model, registration_plate FROM vehicles ORDER BY vehicle_number");

// Get recent incidents (last 10)
$recent_incidents_query = "
    SELECT i.*, 
           v.vehicle_number, v.model
    FROM incidents i
    LEFT JOIN vehicles v ON i.vehicle_id = v.id
    WHERE i.reported_by = :user_id
    ORDER BY i.incident_date DESC, i.created_at DESC
    LIMIT 10
";
$recent_incidents = $db->query($recent_incidents_query, [':user_id' => $user_id]);

$page_title = 'Zgłoś Incydent';
include __DIR__ . '/../../includes/header.php';
?>

<h1>⚠️ Zgłoś Incydent</h1>
<p class="text-muted">Zgłoś awarię, wypadek lub inne zdarzenie</p>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Formularz zgłoszenia</h2>
    </div>
    <div class="card-body">
        <form method="POST" action="" class="form">
            <?php echo csrfField(); ?>
            
            <div class="row">
                <div class="col col-12 col-md-6">
                    <div class="form-group">
                        <label for="vehicle_id">Pojazd</label>
                        <select name="vehicle_id" id="vehicle_id" class="form-control">
                            <option value="">Nie dotyczy pojazdu</option>
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
                        <small class="text-muted">Wybierz pojazd, jeśli incydent dotyczy konkretnego pojazdu</small>
                    </div>
                </div>
                
                <div class="col col-12 col-md-6">
                    <div class="form-group">
                        <label for="incident_date">Data i godzina incydentu *</label>
                        <input type="datetime-local" name="incident_date" id="incident_date" class="form-control" 
                               value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col col-12 col-md-6">
                    <div class="form-group">
                        <label for="incident_type">Typ incydentu *</label>
                        <select name="incident_type" id="incident_type" class="form-control" required>
                            <option value="">Wybierz typ</option>
                            <option value="breakdown">Awaria techniczna</option>
                            <option value="accident">Wypadek</option>
                            <option value="complaint">Skarga pasażera</option>
                            <option value="other">Inne</option>
                        </select>
                    </div>
                </div>
                
                <div class="col col-12 col-md-6">
                    <div class="form-group">
                        <label for="severity">Poziom ważności *</label>
                        <select name="severity" id="severity" class="form-control" required>
                            <option value="">Wybierz poziom</option>
                            <option value="low">Niski</option>
                            <option value="medium">Średni</option>
                            <option value="high">Wysoki</option>
                            <option value="critical">Krytyczny</option>
                        </select>
                        <small class="text-muted">Określ wagę incydentu</small>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="title">Tytuł incydentu *</label>
                <input type="text" name="title" id="title" class="form-control" 
                       placeholder="Krótki opis problemu" maxlength="200" required>
                <small class="text-muted">Minimum 5 znaków</small>
            </div>
            
            <div class="form-group">
                <label for="description">Szczegółowy opis *</label>
                <textarea name="description" id="description" class="form-control" rows="6" 
                          placeholder="Opisz szczegółowo co się wydarzyło, kiedy i gdzie..." required></textarea>
                <small class="text-muted">Minimum 10 znaków. Im więcej szczegółów, tym szybsze rozwiązanie.</small>
            </div>
            
            <div class="alert alert-info">
                <strong>Wskazówka:</strong> W opisie incydentu podaj:
                <ul>
                    <li>Dokładną lokalizację zdarzenia</li>
                    <li>Godzinę zdarzenia</li>
                    <li>Warunki (pogoda, natężenie ruchu itp.)</li>
                    <li>Świadków (jeśli są)</li>
                    <li>Działania podjęte w związku z incydentem</li>
                </ul>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    Wyślij zgłoszenie
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
        <h2 class="card-title">Twoje ostatnie zgłoszenia</h2>
    </div>
    <div class="card-body">
        <?php if (empty($recent_incidents)): ?>
            <p class="text-muted">Brak zgłoszeń.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Pojazd</th>
                            <th>Typ</th>
                            <th>Ważność</th>
                            <th>Tytuł</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_incidents as $incident): ?>
                        <tr>
                            <td data-label="Data">
                                <?php echo formatDateTime($incident['incident_date'], 'd.m.Y H:i'); ?>
                            </td>
                            <td data-label="Pojazd">
                                <?php if ($incident['vehicle_number']): ?>
                                    <?php echo e($incident['vehicle_number']); ?>
                                    <?php if ($incident['model']): ?>
                                        <br><small class="text-muted"><?php echo e($incident['model']); ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">Brak</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Typ">
                                <?php 
                                $type_labels = [
                                    'breakdown' => 'Awaria',
                                    'accident' => 'Wypadek',
                                    'complaint' => 'Skarga',
                                    'other' => 'Inne'
                                ];
                                echo e($type_labels[$incident['incident_type']] ?? $incident['incident_type']);
                                ?>
                            </td>
                            <td data-label="Ważność">
                                <?php echo getSeverityBadge($incident['severity']); ?>
                            </td>
                            <td data-label="Tytuł">
                                <?php echo e($incident['title']); ?>
                            </td>
                            <td data-label="Status">
                                <?php echo getStatusBadge($incident['status']); ?>
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
