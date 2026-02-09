<?php
/**
 * Edit Vehicle
 * Panel Pracowniczy Firma KOT
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/RBAC.php';
require_once __DIR__ . '/../../core/Validator.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

$rbac = new RBAC();
$rbac->requirePermission('vehicles', 'update');

$db = new Database();
$errors = [];

// Get vehicle ID
$vehicle_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$vehicle_id) {
    setFlashMessage('error', 'Nieprawidłowy ID pojazdu.');
    redirect('/admin/vehicles/index.php');
}

// Get vehicle data
$query = "SELECT * FROM vehicles WHERE id = :id";
$vehicle = $db->queryOne($query, [':id' => $vehicle_id]);

if (!$vehicle) {
    setFlashMessage('error', 'Pojazd nie został znaleziony.');
    redirect('/admin/vehicles/index.php');
}

$form_data = $vehicle;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Nieprawidłowy token CSRF.');
        redirect('/admin/vehicles/edit.php?id=' . $vehicle_id);
    }
    
    $form_data = array_merge($vehicle, $_POST);
    
    $validator = new Validator($form_data);
    $validator->required('vehicle_number', 'Numer pojazdu jest wymagany.')
              ->required('vehicle_type', 'Typ pojazdu jest wymagany.')
              ->required('status', 'Status jest wymagany.');
    
    if (!empty($form_data['manufacture_year'])) {
        $validator->integer('manufacture_year', 'Rok produkcji musi być liczbą całkowitą.')
                  ->min('manufacture_year', 1900, 'Rok produkcji musi być większy niż 1900.')
                  ->max('manufacture_year', date('Y') + 1, 'Rok produkcji nie może być w przyszłości.');
    }
    
    if (!empty($form_data['capacity'])) {
        $validator->integer('capacity', 'Pojemność musi być liczbą całkowitą.')
                  ->min('capacity', 1, 'Pojemność musi być większa niż 0.');
    }
    
    if (!empty($form_data['last_inspection'])) {
        $validator->date('last_inspection', 'Y-m-d', 'Data ostatniego przeglądu jest nieprawidłowa.');
    }
    
    if ($validator->fails()) {
        $errors = $validator->getErrors();
    }
    
    // Check if vehicle_number is unique (excluding current vehicle)
    if (empty($errors['vehicle_number']) && $form_data['vehicle_number'] !== $vehicle['vehicle_number']) {
        $check_query = "SELECT COUNT(*) as count FROM vehicles WHERE vehicle_number = :vehicle_number AND id != :id";
        $check_result = $db->queryOne($check_query, [
            ':vehicle_number' => $form_data['vehicle_number'],
            ':id' => $vehicle_id
        ]);
        
        if ($check_result['count'] > 0) {
            $errors['vehicle_number'] = 'Pojazd o tym numerze już istnieje.';
        }
    }
    
    // Check if registration_plate is unique (excluding current vehicle, if provided)
    if (!empty($form_data['registration_plate']) && empty($errors['registration_plate'])) {
        if ($form_data['registration_plate'] !== $vehicle['registration_plate']) {
            $check_query = "SELECT COUNT(*) as count FROM vehicles WHERE registration_plate = :registration_plate AND id != :id";
            $check_result = $db->queryOne($check_query, [
                ':registration_plate' => $form_data['registration_plate'],
                ':id' => $vehicle_id
            ]);
            
            if ($check_result['count'] > 0) {
                $errors['registration_plate'] = 'Pojazd o tej rejestracji już istnieje.';
            }
        }
    }
    
    if (empty($errors)) {
        try {
            $query = "
                UPDATE vehicles SET
                    vehicle_number = :vehicle_number,
                    registration_plate = :registration_plate,
                    vehicle_type = :vehicle_type,
                    model = :model,
                    manufacture_year = :manufacture_year,
                    capacity = :capacity,
                    status = :status,
                    last_inspection = :last_inspection,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ";
            
            $params = [
                ':vehicle_number' => $form_data['vehicle_number'],
                ':registration_plate' => !empty($form_data['registration_plate']) ? $form_data['registration_plate'] : null,
                ':vehicle_type' => $form_data['vehicle_type'],
                ':model' => !empty($form_data['model']) ? $form_data['model'] : null,
                ':manufacture_year' => !empty($form_data['manufacture_year']) ? (int)$form_data['manufacture_year'] : null,
                ':capacity' => !empty($form_data['capacity']) ? (int)$form_data['capacity'] : null,
                ':status' => $form_data['status'],
                ':last_inspection' => !empty($form_data['last_inspection']) ? $form_data['last_inspection'] : null,
                ':id' => $vehicle_id
            ];
            
            $db->execute($query, $params);
            
            setFlashMessage('success', 'Pojazd został zaktualizowany pomyślnie.');
            redirect('/admin/vehicles/index.php');
        } catch (Exception $e) {
            error_log('Error updating vehicle: ' . $e->getMessage());
            $errors['general'] = 'Wystąpił błąd podczas aktualizacji pojazdu.';
        }
    }
}

$page_title = 'Edytuj pojazd';
include __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h1>✏️ Edytuj pojazd</h1>
    <a href="/admin/vehicles/index.php" class="btn btn-secondary">← Powrót do listy</a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error"><?php echo e($errors['general']); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="/admin/vehicles/edit.php?id=<?php echo $vehicle_id; ?>">
            <?php echo csrfField(); ?>
            
            <div class="form-row">
                <div class="form-group col col-12 col-md-6">
                    <label for="vehicle_number">Numer pojazdu *</label>
                    <input type="text" 
                           id="vehicle_number" 
                           name="vehicle_number" 
                           class="form-control <?php echo isset($errors['vehicle_number']) ? 'is-invalid' : ''; ?>"
                           value="<?php echo e($form_data['vehicle_number'] ?? ''); ?>"
                           required>
                    <?php if (isset($errors['vehicle_number'])): ?>
                        <div class="invalid-feedback"><?php echo e($errors['vehicle_number']); ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group col col-12 col-md-6">
                    <label for="registration_plate">Numer rejestracyjny</label>
                    <input type="text" 
                           id="registration_plate" 
                           name="registration_plate" 
                           class="form-control <?php echo isset($errors['registration_plate']) ? 'is-invalid' : ''; ?>"
                           value="<?php echo e($form_data['registration_plate'] ?? ''); ?>">
                    <?php if (isset($errors['registration_plate'])): ?>
                        <div class="invalid-feedback"><?php echo e($errors['registration_plate']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col col-12 col-md-6">
                    <label for="vehicle_type">Typ pojazdu *</label>
                    <select id="vehicle_type" 
                            name="vehicle_type" 
                            class="form-control <?php echo isset($errors['vehicle_type']) ? 'is-invalid' : ''; ?>"
                            required>
                        <option value="">-- Wybierz typ --</option>
                        <option value="bus" <?php echo ($form_data['vehicle_type'] ?? '') === 'bus' ? 'selected' : ''; ?>>Autobus</option>
                        <option value="tram" <?php echo ($form_data['vehicle_type'] ?? '') === 'tram' ? 'selected' : ''; ?>>Tramwaj</option>
                        <option value="metro" <?php echo ($form_data['vehicle_type'] ?? '') === 'metro' ? 'selected' : ''; ?>>Metro</option>
                    </select>
                    <?php if (isset($errors['vehicle_type'])): ?>
                        <div class="invalid-feedback"><?php echo e($errors['vehicle_type']); ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group col col-12 col-md-6">
                    <label for="model">Model</label>
                    <input type="text" 
                           id="model" 
                           name="model" 
                           class="form-control"
                           value="<?php echo e($form_data['model'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col col-12 col-md-4">
                    <label for="manufacture_year">Rok produkcji</label>
                    <input type="number" 
                           id="manufacture_year" 
                           name="manufacture_year" 
                           class="form-control <?php echo isset($errors['manufacture_year']) ? 'is-invalid' : ''; ?>"
                           value="<?php echo e($form_data['manufacture_year'] ?? ''); ?>"
                           min="1900"
                           max="<?php echo date('Y') + 1; ?>">
                    <?php if (isset($errors['manufacture_year'])): ?>
                        <div class="invalid-feedback"><?php echo e($errors['manufacture_year']); ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group col col-12 col-md-4">
                    <label for="capacity">Pojemność (liczba miejsc)</label>
                    <input type="number" 
                           id="capacity" 
                           name="capacity" 
                           class="form-control <?php echo isset($errors['capacity']) ? 'is-invalid' : ''; ?>"
                           value="<?php echo e($form_data['capacity'] ?? ''); ?>"
                           min="1">
                    <?php if (isset($errors['capacity'])): ?>
                        <div class="invalid-feedback"><?php echo e($errors['capacity']); ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group col col-12 col-md-4">
                    <label for="last_inspection">Data ostatniego przeglądu</label>
                    <input type="date" 
                           id="last_inspection" 
                           name="last_inspection" 
                           class="form-control <?php echo isset($errors['last_inspection']) ? 'is-invalid' : ''; ?>"
                           value="<?php echo e($form_data['last_inspection'] ?? ''); ?>">
                    <?php if (isset($errors['last_inspection'])): ?>
                        <div class="invalid-feedback"><?php echo e($errors['last_inspection']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label for="status">Status *</label>
                <select id="status" 
                        name="status" 
                        class="form-control <?php echo isset($errors['status']) ? 'is-invalid' : ''; ?>"
                        required>
                    <option value="available" <?php echo ($form_data['status'] ?? '') === 'available' ? 'selected' : ''; ?>>Dostępny</option>
                    <option value="in_use" <?php echo ($form_data['status'] ?? '') === 'in_use' ? 'selected' : ''; ?>>W użyciu</option>
                    <option value="maintenance" <?php echo ($form_data['status'] ?? '') === 'maintenance' ? 'selected' : ''; ?>>Serwis</option>
                    <option value="broken" <?php echo ($form_data['status'] ?? '') === 'broken' ? 'selected' : ''; ?>>Awaria</option>
                </select>
                <?php if (isset($errors['status'])): ?>
                    <div class="invalid-feedback"><?php echo e($errors['status']); ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Zapisz zmiany</button>
                <a href="/admin/vehicles/index.php" class="btn btn-secondary">Anuluj</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
