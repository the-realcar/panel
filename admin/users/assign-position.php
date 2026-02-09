<?php
/**
 * Assign Position to User
 * Panel Pracowniczy Firma KOT
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/Database.php';
require_once __DIR__ . '/../../core/RBAC.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

$rbac = new RBAC();
$rbac->requirePermission('users', 'update');

$db = new Database();
$errors = [];

// Get user ID
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if (!$user_id) {
    setFlashMessage('error', 'Nieprawidłowy ID użytkownika.');
    redirect('/admin/users/index.php');
}

// Get user data
$user_query = "SELECT * FROM users WHERE id = :id";
$user = $db->queryOne($user_query, [':id' => $user_id]);

if (!$user) {
    setFlashMessage('error', 'Użytkownik nie został znaleziony.');
    redirect('/admin/users/index.php');
}

// Get available positions
$positions_query = "SELECT id, name FROM positions WHERE active = TRUE ORDER BY name ASC";
$positions = $db->query($positions_query);

// Get current positions of user
$current_positions_query = "
    SELECT up.id as assignment_id, p.id as position_id, p.name
    FROM user_positions up
    INNER JOIN positions p ON up.position_id = p.id
    WHERE up.user_id = :user_id
    ORDER BY p.name ASC
";
$current_positions = $db->query($current_positions_query, [':user_id' => $user_id]);

// Handle position assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'assign') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Nieprawidłowy token CSRF.');
        redirect('/admin/users/assign-position.php?user_id=' . $user_id);
    }
    
    $position_id = isset($_POST['position_id']) ? (int)$_POST['position_id'] : 0;
    
    if (!$position_id) {
        $errors['position'] = 'Wybierz stanowisko.';
    }
    
    if (empty($errors)) {
        try {
            // Check if already assigned
            $check_query = "SELECT COUNT(*) as count FROM user_positions WHERE user_id = :user_id AND position_id = :position_id";
            $check_result = $db->queryOne($check_query, [
                ':user_id' => $user_id,
                ':position_id' => $position_id
            ]);
            
            if ($check_result['count'] > 0) {
                setFlashMessage('warning', 'Użytkownik jest już przypisany do tego stanowiska.');
            } else {
                // Assign position (trigger will check limits)
                $query = "INSERT INTO user_positions (user_id, position_id) VALUES (:user_id, :position_id)";
                $db->execute($query, [
                    ':user_id' => $user_id,
                    ':position_id' => $position_id
                ]);
                
                setFlashMessage('success', 'Stanowisko zostało przypisane pomyślnie.');
            }
            
            redirect('/admin/users/assign-position.php?user_id=' . $user_id);
        } catch (Exception $e) {
            error_log('Error assigning position: ' . $e->getMessage());
            
            // Check if it's a limit violation
            if (strpos($e->getMessage(), 'Limit') !== false || strpos($e->getMessage(), 'limit') !== false) {
                setFlashMessage('error', 'Nie można przypisać stanowiska. Osiągnięto maksymalny limit pracowników dla tego stanowiska.');
            } else {
                setFlashMessage('error', 'Wystąpił błąd podczas przypisywania stanowiska.');
            }
        }
    }
}

// Handle position removal
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['assignment_id'])) {
    if (!verifyCsrfToken($_GET['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Nieprawidłowy token CSRF.');
        redirect('/admin/users/assign-position.php?user_id=' . $user_id);
    }
    
    $assignment_id = (int)$_GET['assignment_id'];
    
    try {
        $query = "DELETE FROM user_positions WHERE id = :id AND user_id = :user_id";
        $db->execute($query, [
            ':id' => $assignment_id,
            ':user_id' => $user_id
        ]);
        
        setFlashMessage('success', 'Stanowisko zostało usunięte pomyślnie.');
        redirect('/admin/users/assign-position.php?user_id=' . $user_id);
    } catch (Exception $e) {
        error_log('Error removing position: ' . $e->getMessage());
        setFlashMessage('error', 'Wystąpił błąd podczas usuwania stanowiska.');
    }
}

$page_title = 'Przypisz stanowisko';
include __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h1>📋 Przypisz stanowisko</h1>
    <a href="/admin/users/index.php" class="btn btn-secondary">← Powrót do listy</a>
</div>

<div class="card">
    <div class="card-header">
        <h3>Użytkownik: <?php echo e($user['username']); ?></h3>
        <?php if ($user['first_name'] || $user['last_name']): ?>
            <p class="text-muted"><?php echo e(getFullName($user['first_name'], $user['last_name'])); ?></p>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <h4>Przypisz nowe stanowisko</h4>
        
        <?php if (!empty($errors['position'])): ?>
            <div class="alert alert-error"><?php echo e($errors['position']); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="/admin/users/assign-position.php?user_id=<?php echo $user_id; ?>">
            <?php echo csrfField(); ?>
            <input type="hidden" name="action" value="assign">
            
            <div class="form-row">
                <div class="form-group col col-12 col-md-8">
                    <label for="position_id">Stanowisko</label>
                    <select id="position_id" 
                            name="position_id" 
                            class="form-control"
                            required>
                        <option value="">-- Wybierz stanowisko --</option>
                        <?php foreach ($positions as $position): ?>
                            <option value="<?php echo $position['id']; ?>">
                                <?php echo e($position['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group col col-12 col-md-4">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-block">➕ Przypisz</button>
                </div>
            </div>
        </form>
        
        <hr>
        
        <h4>Aktualne stanowiska</h4>
        
        <?php if (empty($current_positions)): ?>
            <p class="text-muted">Użytkownik nie ma przypisanych stanowisk.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Stanowisko</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($current_positions as $position): ?>
                        <tr>
                            <td data-label="Stanowisko">
                                <strong><?php echo e($position['name']); ?></strong>
                            </td>
                            <td data-label="Akcje">
                                <a href="/admin/users/assign-position.php?user_id=<?php echo $user_id; ?>&action=remove&assignment_id=<?php echo $position['assignment_id']; ?>&csrf_token=<?php echo generateCsrfToken(); ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Czy na pewno chcesz usunąć to stanowisko?');">
                                    🗑️ Usuń
                                </a>
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
