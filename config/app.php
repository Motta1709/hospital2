<?php
/**
 * PharmaCRM - Configuración General
 */

// Zona horaria Colombia
date_default_timezone_set('America/Bogota');

// Configuración de sesiones
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.gc_maxlifetime', 7200);
    session_start();
}

// Constantes de la aplicación
define('APP_NAME', 'PharmaCRM');
define('APP_VERSION', '1.0.0');
define('APP_URL', '/hospital');
define('APP_ROOT', dirname(__DIR__));

// Configuración de puntos de fidelización
define('LOYALTY_POINTS_PER_1000', 1); // 1 punto por cada $1000 COP
define('LOYALTY_POINT_VALUE', 100);    // Cada punto vale $100 COP

// Configuración de alertas de vencimiento (días)
define('ALERT_EXPIRY_30', 30);
define('ALERT_EXPIRY_15', 15);
define('ALERT_EXPIRY_7', 7);

// Paginación
define('ITEMS_PER_PAGE', 15);

// Moneda
define('CURRENCY_SYMBOL', '$');
define('CURRENCY_CODE', 'COP');

/**
 * Helper para formatear moneda colombiana
 */
function formatCurrency($amount) {
    return CURRENCY_SYMBOL . ' ' . number_format($amount, 0, ',', '.');
}

/**
 * Helper para generar número de factura
 */
function generateInvoiceNumber() {
    $db = Database::getInstance()->getConnection();
    $year = date('Y');
    $stmt = $db->prepare("SELECT COUNT(*) + 1 as next FROM sales WHERE YEAR(created_at) = ?");
    $stmt->execute([$year]);
    $result = $stmt->fetch();
    return 'FV-' . $year . '-' . str_pad($result['next'], 4, '0', STR_PAD_LEFT);
}

/**
 * Helper para sanitizar input
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Helper para respuesta JSON
 */
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Helper para redirección
 */
function redirect($path) {
    header('Location: ' . APP_URL . '/' . ltrim($path, '/'));
    exit;
}

/**
 * Helper para verificar si el request es AJAX
 */
function isAjax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Flash messages
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
