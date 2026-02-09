<?php
/**
 * Edit Line
 * Panel Pracowniczy Firma KOT
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/RBAC.php';
require_once __DIR__ . '/../../core/Validator.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

$rbac = new RBAC();
$rbac->requirePermission('lines', 'update');

$db = new Database();
$errors = [];

// Get line ID
$line_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$line_id) {
    setFlashMessage('error', 'Nieprawidłowy ID linii.');
    redirect('/admin/lines/index.php');
}

// Get line data
$query = "SELECT * FROM lines WHERE id = :id";
$line = $db->queryOne($query, [':id' => $line_id]);

if (!$line) {
    setFlashMessage('error', 'Linia nie została znaleziona.');
    redirect('/admin/lines/index.php');
}

$form_data = $line;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Nieprawidłowy token CSRF.');
        redirect('/admin/lines/edit.php?id=' . $line_id);
    }
    
    $form_data = array_merge($line, $_POST);
    
    $validator = new Validator($form_data);
    $validator->required('line_number', 'Numer linii jest wymagany.')
              ->required('name', 'Nazwa jest wymagana.')
              ->required('line_type', 'Typ linii jest wymagany.');
    
    if ($validator->fails()) {
        $errors = $validator->getErrors();
    }
    
    // Check if line_number is unique (excluding current line)
    if (empty($errors['line_number']) && $form_data['line_number'] !== $line['line_number']) {
        $check_query = "SELECT COUNT(*) as count FROM lines WHERE line_number = :line_number AND id != :id";
        $check_result = $db->queryOne($check_query, [
            ':line_number' => $form_data['line_number'],
            ':id' => $line_id
        ]);
        
        if ($check_result['count'] > 0) {
            $errors['line_number'] = 'Linia o tym numerze już istnieje.';
        }
    }
    
    if (empty($errors)) {
        try {
            $query = "
                UPDATE lines SET
                    line_number = :line_number,
                    name = :name,
                    route_description = :route_description,
                    line_type = :line_type,
                    active = :active,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ";
            
            $params = [
                ':line_number' => $form_data['line_number'],
                ':name' => $form_data['name'],
                ':route_description' => !empty($form_data['route_description']) ? $form_data['route_description'] : null,
                ':line_type' => $form_data['line_type'],
                ':active' => isset($form_data['active']) ? 'true' : 'false',
                ':id' => $line_id
            ];
            
            $db->execute($query, $params);
            
            setFlashMessage('success', 'Linia została zaktualizowana pomyślnie.');
            redirect('/admin/lines/index.php');
        } catch (Exception $e) {
            error_log('Error updating line: ' . $e->getMessage());
            $errors['general'] = 'Wystąpił błąd podczas aktualizacji linii.';
        }
    }
}

$page_title = 'Edytuj linię';
include __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h1>✏️ Edytuj linię</h1>
    <a href="/admin/lines/index.php" class="btn btn-secondary">← Powrót do listy</a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error"><?php echo e($errors['general']); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="/admin/lines/edit.php?id=<?php echo $line_id; ?>">
            <?php echo csrfField(); ?>
            
            <div class="form-row">
                <div class="form-group col col-12 col-md-6">
                    <label for="line_number">Numer linii *</label>
                    <input type="text" 
                           id="line_number" 
                           name="line_number" 
                           class="form-control <?php echo isset($errors['line_number']) ? 'is-invalid' : ''; ?>"
                           value="<?php echo e($form_data['line_number'] ?? ''); ?>"
                           required>
                    <?php if (isset($errors['line_number'])): ?>
                        <div class="invalid-feedback"><?php echo e($errors['line_number']); ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group col col-12 col-md-6">
                    <label for="name">Nazwa *</label>
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
            </div>
            
            <div class="form-group">
                <label for="line_type">Typ linii *</label>
                <select id="line_type" 
                        name="line_type" 
                        class="form-control <?php echo isset($errors['line_type']) ? 'is-invalid' : ''; ?>"
                        required>
                    <option value="">-- Wybierz typ --</option>
                    <option value="bus" <?php echo ($form_data['line_type'] ?? '') === 'bus' ? 'selected' : ''; ?>>Autobus</option>
                    <option value="tram" <?php echo ($form_data['line_type'] ?? '') === 'tram' ? 'selected' : ''; ?>>Tramwaj</option>
                    <option value="metro" <?php echo ($form_data['line_type'] ?? '') === 'metro' ? 'selected' : ''; ?>>Metro</option>
                </select>
                <?php if (isset($errors['line_type'])): ?>
                    <div class="invalid-feedback"><?php echo e($errors['line_type']); ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="route_description">Opis trasy</label>
                <textarea id="route_description" 
                          name="route_description" 
                          class="form-control"
                          rows="4"><?php echo e($form_data['route_description'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" 
                           name="active" 
                           <?php echo ($form_data['active'] ?? false) ? 'checked' : ''; ?>>
                    Aktywna
                </label>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Zapisz zmiany</button>
                <a href="/admin/lines/index.php" class="btn btn-secondary">Anuluj</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
