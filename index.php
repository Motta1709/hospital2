<?php
/**
 * PharmaCRM - Punto de Entrada & Enrutador Principal
 * Revertido a Endpoints Tradicionales (?route=)
 */

// 1. Inicialización y Carga de Núcleo
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';

// 2. Carga de Helpers Esenciales
require_once __DIR__ . '/app/helpers/Auth.php';
require_once __DIR__ . '/app/helpers/Validator.php';

// 3. Autocarga Automática de Modelos y Controladores
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/app/models/',
        __DIR__ . '/app/controllers/',
    ];
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// 4. Lógica de Enrutamiento Tradicional
$route = $_GET['route'] ?? 'home';
$action = $_GET['action'] ?? 'index';

// Rutas públicas
$publicRoutes = ['home', 'login', 'auth', 'cart', 'checkout', 'customer-dashboard'];

// 5. Verificación de Seguridad
if (!in_array($route, $publicRoutes) && !Auth::check()) {
    header('Location: ?route=login');
    exit;
}

// 6. Router (Switch Tradicional)
try {
    switch ($route) {
        case 'home':
            (new HomeController())->index();
            break;

        case 'login':
            if (Auth::check()) { header('Location: ?route=dashboard'); exit; }
            (new AuthController())->login();
            break;

        case 'auth':
            $controller = new AuthController();
            if ($action === 'login') $controller->authenticate();
            elseif ($action === 'logout') $controller->logout();
            break;

        case 'dashboard':
            (new DashboardController())->index();
            break;

        case 'inventory':
            $controller = new InventoryController();
            if (method_exists($controller, $action)) $controller->$action();
            else $controller->index();
            break;

        case 'sales':
            $controller = new SalesController();
            if (method_exists($controller, $action)) $controller->$action();
            else $controller->index();
            break;

        case 'clients':
            $controller = new ClientController();
            if (method_exists($controller, $action)) $controller->$action();
            else $controller->index();
            break;

        case 'loyalty':
            $controller = new LoyaltyController();
            if (method_exists($controller, $action)) $controller->$action();
            else $controller->index();
            break;

        case 'reports':
            $controller = new ReportController();
            if (method_exists($controller, $action)) $controller->$action();
            else $controller->index();
            break;

        case 'users':
            $controller = new UserController();
            if (method_exists($controller, $action)) $controller->$action();
            else $controller->index();
            break;

        case 'cart':
            $controller = new CartController();
            if (method_exists($controller, $action)) $controller->$action();
            else $controller->index();
            break;

        case 'checkout':
            $controller = new CheckoutController();
            if (method_exists($controller, $action)) $controller->$action();
            else $controller->index();
            break;

        case 'branches':
            $controller = new BranchController();
            if (method_exists($controller, $action)) $controller->$action();
            else $controller->index();
            break;

        case 'rbac':
            $controller = new RbacController();
            if (method_exists($controller, $action)) $controller->$action();
            else $controller->index();
            break;

        case 'customer-dashboard':
            $controller = new CustomerDashboardController();
            if ($action === 'getOrderItems') { // Manejo especial para AJAX
                $controller->getOrderItems();
                exit;
            }
            if (method_exists($controller, $action)) $controller->$action();
            else $controller->index();
            break;

        case 'api':
            handleApiRoute($_GET['entity'] ?? '');
            break;

        default:
            http_response_code(404);
            $pageTitle = '404 - No Encontrado';
            include __DIR__ . '/views/layouts/main.php';
            break;
    }
} catch (Exception $e) {
    if (isAjax()) {
        jsonResponse(['error' => true, 'message' => $e->getMessage()], 500);
    } else {
        die("<h1>Error</h1><p>{$e->getMessage()}</p>");
    }
}

/**
 * Manejador de API
 */
function handleApiRoute($entity) {
    switch ($entity) {
        case 'dashboard': (new DashboardController())->getStats(); break;
        case 'products':
        case 'inventory': (new InventoryController())->search(); break;
        case 'clients': (new ClientController())->search(); break;
        case 'reports': (new ReportController())->getData(); break;
        case 'sales': (new SalesController())->process(); break;
        default: jsonResponse(['error' => 'Entidad no válida'], 404);
    }
}