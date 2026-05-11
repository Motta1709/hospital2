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
    // 1. Manejo de Namespaces App\ -> app/
    if (strpos($class, 'App\\') === 0) {
        $relativeClass = substr($class, 4);
        $filePath = __DIR__ . '/app/' . str_replace('\\', '/', $relativeClass) . '.php';
        
        // Intentar carga directa (sensible a mayúsculas o no según el SO)
        if (file_exists($filePath)) {
            require_once $filePath;
            return;
        }
        
        // Intentar carga con carpetas en minúsculas (ej. app/core/ vs app/Core/)
        $parts = explode('\\', $relativeClass);
        $className = array_pop($parts);
        $folders = array_map('strtolower', $parts);
        $filePath = __DIR__ . '/app/' . implode('/', $folders) . '/' . $className . '.php';
        
        if (file_exists($filePath)) {
            require_once $filePath;
            return;
        }
    }

    // 2. Manejo de clases legadas sin namespace (Models, Controllers, etc)
    $legacyFolders = [
        'app/models',
        'app/controllers',
        'app/helpers',
        'app/core',
        'app/repositories',
        'app/services',
        'app/interfaces',
        'app/exceptions'
    ];

    foreach ($legacyFolders as $folder) {
        $file = __DIR__ . '/' . $folder . '/' . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// 4. Lógica de Enrutamiento Tradicional
$route = $_GET['route'] ?? 'home';
$action = $_GET['action'] ?? 'index';

// Rutas publicas (no requieren autenticacion)
$publicRoutes = ['home', 'login', 'auth', 'cart', 'checkout'];

// Rutas que requieren sesion de cliente
$clientRoutes = ['customer-dashboard'];

// 5. Verificacion de Seguridad
if (!in_array($route, $publicRoutes)) {
    if (in_array($route, $clientRoutes)) {
        // Rutas de cliente: requieren sesion de cliente
        if (empty($_SESSION['client_id']) && !Auth::check()) {
            header('Location: ?route=login');
            exit;
        }
    } elseif (!Auth::check()) {
        header('Location: ?route=login');
        exit;
    }
}

// 6. Router (Switch Tradicional)
try {
    switch ($route) {
        case 'home':
            (new HomeController())->index();
            break;

        case 'login':
            if (Auth::check()) { header('Location: ?route=dashboard'); exit; }
            if (!empty($_SESSION['client_id'])) { header('Location: ?route=customer-dashboard'); exit; }
            (new AuthController())->login();
            break;

        case 'auth':
            $controller = new AuthController();
            if ($action === 'login') $controller->authenticate();
            elseif ($action === 'logout') $controller->logout();
            elseif ($action === 'register') $controller->register();
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
} catch (\Throwable $e) {
    \App\Core\ErrorHandler::handle($e);
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