<?php
/**
 * Helper Functions
 * Panel Pracowniczy Firma KOT
 */

/**
 * Escape HTML output
 * 
 * @param string $string
 * @return string
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Format date
 * 
 * @param string $date
 * @param string $format
 * @return string
 */
function formatDate($date, $format = 'Y-m-d') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Format datetime
 * 
 * @param string $datetime
 * @param string $format
 * @return string
 */
function formatDateTime($datetime, $format = 'Y-m-d H:i:s') {
    if (empty($datetime)) return '';
    return date($format, strtotime($datetime));
}

/**
 * Format time
 * 
 * @param string $time
 * @param string $format
 * @return string
 */
function formatTime($time, $format = 'H:i') {
    if (empty($time)) return '';
    return date($format, strtotime($time));
}

/**
 * Get status badge HTML
 * 
 * @param string $status
 * @param array $status_map Custom status map
 * @return string
 */
function getStatusBadge($status, $status_map = null) {
    $default_map = [
        'available' => ['label' => 'Dostępny', 'class' => 'success'],
        'in_use' => ['label' => 'W użyciu', 'class' => 'primary'],
        'maintenance' => ['label' => 'Serwis', 'class' => 'warning'],
        'broken' => ['label' => 'Awaria', 'class' => 'danger'],
        'scheduled' => ['label' => 'Zaplanowany', 'class' => 'info'],
        'completed' => ['label' => 'Wykonany', 'class' => 'success'],
        'cancelled' => ['label' => 'Anulowany', 'class' => 'secondary'],
        'in_progress' => ['label' => 'W trakcie', 'class' => 'primary'],
        'open' => ['label' => 'Otwarte', 'class' => 'warning'],
        'resolved' => ['label' => 'Rozwiązane', 'class' => 'success'],
        'closed' => ['label' => 'Zamknięte', 'class' => 'secondary'],
    ];
    
    $map = $status_map ?? $default_map;
    $info = $map[$status] ?? ['label' => $status, 'class' => 'secondary'];
    
    return sprintf(
        '<span class="badge badge-%s">%s</span>',
        e($info['class']),
        e($info['label'])
    );
}

/**
 * Get severity badge HTML
 * 
 * @param string $severity
 * @return string
 */
function getSeverityBadge($severity) {
    $map = [
        'low' => ['label' => 'Niski', 'class' => 'info'],
        'medium' => ['label' => 'Średni', 'class' => 'warning'],
        'high' => ['label' => 'Wysoki', 'class' => 'danger'],
        'critical' => ['label' => 'Krytyczny', 'class' => 'danger'],
    ];
    
    $info = $map[$severity] ?? ['label' => $severity, 'class' => 'secondary'];
    
    return sprintf(
        '<span class="badge badge-%s">%s</span>',
        e($info['class']),
        e($info['label'])
    );
}

/**
 * Generate pagination HTML
 * 
 * @param int $current_page
 * @param int $total_pages
 * @param string $base_url
 * @return string
 */
function pagination($current_page, $total_pages, $base_url) {
    if ($total_pages <= 1) return '';
    
    $html = '<nav class="pagination"><ul>';
    
    // Previous
    if ($current_page > 1) {
        $html .= sprintf(
            '<li><a href="%s?page=%d">« Poprzednia</a></li>',
            e($base_url),
            $current_page - 1
        );
    }
    
    // Pages
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $current_page) {
            $html .= sprintf('<li class="active"><span>%d</span></li>', $i);
        } else {
            $html .= sprintf(
                '<li><a href="%s?page=%d">%d</a></li>',
                e($base_url),
                $i,
                $i
            );
        }
    }
    
    // Next
    if ($current_page < $total_pages) {
        $html .= sprintf(
            '<li><a href="%s?page=%d">Następna »</a></li>',
            e($base_url),
            $current_page + 1
        );
    }
    
    $html .= '</ul></nav>';
    
    return $html;
}

/**
 * Redirect to URL
 * 
 * @param string $url
 * @param int $status_code
 */
function redirect($url, $status_code = 302) {
    header('Location: ' . $url, true, $status_code);
    exit;
}

/**
 * Check if current page is active
 * 
 * @param string $page
 * @return bool
 */
function isActivePage($page) {
    $current = $_SERVER['REQUEST_URI'] ?? '';
    return strpos($current, $page) !== false;
}

/**
 * Get full name
 * 
 * @param string $first_name
 * @param string $last_name
 * @return string
 */
function getFullName($first_name, $last_name) {
    return trim($first_name . ' ' . $last_name);
}

/**
 * Truncate string
 * 
 * @param string $string
 * @param int $length
 * @param string $append
 * @return string
 */
function truncate($string, $length = 100, $append = '...') {
    if (strlen($string) <= $length) {
        return $string;
    }
    return substr($string, 0, $length) . $append;
}

/**
 * Generate CSRF token
 * 
 * @return string
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * 
 * @param string $token
 * @return bool
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF input field
 * 
 * @return string
 */
function csrfField() {
    $token = generateCsrfToken();
    return sprintf('<input type="hidden" name="csrf_token" value="%s">', e($token));
}
