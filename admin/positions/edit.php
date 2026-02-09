<?php
/**
 * Edit Position
 * Panel Pracowniczy Firma KOT
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/RBAC.php';
require_once __DIR__ . '/../../core/Validator.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

$rbac = new RBAC();
$rbac->requirePermission('positions', 'update');

$db = new Database();
$errors = [];

// Get position ID
$position_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$position_id) {
    setFlashMessage('error', 'Nieprawidłowy ID stanowiska.');
    redirect('/admin/positions/index.php');
}

// Get position data
$query = "SELECT * FROM positions WHERE id = :id";
$position = $db->queryOne($query, [':id' => $position_id]);

if (!$position) {
    setFlashMessage('error', 'Stanowisko nie zostało znalezione.');
    redirect('/admin/positions/index.php');
}

$form_data = $position;

// Get departments for dropdown
$departments_query = "SELECT id, name FROM departments WHERE active = TRUE ORDER BY name ASC";
$departments = $db->query($departments_query);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Nieprawidłowy token CSRF.');
        redirect('/admin/positions/edit.php?id=' . $position_id);
    }
    
    $form_data = array_merge($position, $_POST);
    
    $validator = new Validator($form_data);
    $validator->required('name', 'Nazwa stanowiska jest wymagana.');
    
    if (!empty($form_data['max_count'])) {
        $validator->integer('max_count', 'Limit musi być liczbą całkowitą.')
                  ->min('max_count', 1, 'Limit musi być większy niż 0.');
    }
    
    if ($validator->fails()) {
        $errors = $validator->getErrors();
    }
    
    // Check if position name is unique (excluding current position)
    if (empty($errors['name']) && $form_data['name'] !== $position['name']) {
        $check_query = "SELECT COUNT(*) as count FROM positions WHERE name = :name AND id != :id";
        $check_result = $db->queryOne($check_query, [
            ':name' => $form_data['name'],
            ':id' => $position_id
        ]);
        
        if ($check_result['count'] > 0) {
            $errors['name'] = 'Stanowisko o tej nazwie już istnieje.';
        }
    }
    
    if (empty($errors)) {
        try {
            $query = "
                UPDATE positions SET
                    name = :name,
                    department_id = :department_id,
                    max_count = :max_count,
                    description = :description,
                    active = :active,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ";
            
            $params = [
                ':name' => $form_data['name'],
                ':department_id' => !empty($form_data['department_id']) ? (int)$form_data['department_id'] : null,
                ':max_count' => !empty($form_data['max_count']) ? (int)$form_data['max_count'] : null,
                ':description' => !empty($form_data['description']) ? $form_data['description'] : null,
                ':active' => isset($form_data['active']) ? 'true' : 'false',
                ':id' => $position_id
            ];
            
            $db->execute($query, $params);
            
            setFlashMessage('success', 'Stanowisko zostało zaktualizowane pomyślnie.');
            redirect('/admin/positions/index.php');
        } catch (Exception $e) {
            error_log('Error updating position: ' . $e->getMessage());
            $errors['general'] = 'Wystąpił błąd podczas aktualizacji stanowiska.';
        }
    }
}

$page_title = 'Edytuj stanowisko';
include __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h1>✏️ Edytuj stanowisko</h1>
    <a href="/admin/positions/index.php" class="btn btn-secondary">← Powrót do listy</a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error"><?php echo e($errors['general']); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="/admin/positions/edit.php?id=<?php echo $position_id; ?>">
            <?php echo csrfField(); ?>
            
            <div class="form-group">
                <label for="name">Nazwa stanowiska *</label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>"
                       value="<?php echo e($form_data['name'] ?? ''); ?>"
                       required>
                <?php if (isset($errors['name'])): ?>
                    <div class="invalid-feedback"><?php echo e($errors['name']); ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-row">
                <div class="form-group col col-12 col-md-6">
                    <label for="department_id">Dział</label>
                    <select id="department_id" 
                            name="department_id" 
                            class="form-control">
                        <option value="">-- Wybierz dział --</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>" 
                                    <?php echo ($form_data['department_id'] ?? '') == $dept['id'] ? 'selected' : ''; ?>>
                                <?php echo e($dept['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group col col-12 col-md-6">
                    <label for="max_count">Limit pracowników</label>
                    <input type="number" 
                           id="max_count" 
                           name="max_count" 
                           class="form-control <?php echo isset($errors['max_count']) ? 'is-invalid' : ''; ?>"
                           value="<?php echo e($form_data['max_count'] ?? ''); ?>"
                           min="1"
                           placeholder="Pozostaw puste dla braku limitu">
                    <?php if (isset($errors['max_count'])): ?>
                        <div class="invalid-feedback"><?php echo e($errors['max_count']); ?></div>
                    <?php endif; ?>
                    <small class="form-text text-muted">
                        Pozostaw puste jeśli nie chcesz ustalać limitu pracowników na tym stanowisku.
                    </small>
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">Opis</label>
                <textarea id="description" 
                          name="description" 
                          class="form-control"
                          rows="4"><?php echo e($form_data['description'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" 
                           name="active" 
                           <?php echo ($form_data['active'] ?? false) ? 'checked' : ''; ?>>
                    Aktywne
                </label>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Zapisz zmiany</button>
                <a href="/admin/positions/index.php" class="btn btn-secondary">Anuluj</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
