<?php
/**
 * PharmaCRM - Entry Point & Router
 * CRM Farmacéutico para Farmacias Independientes en Colombia
 */

// Cargar configuración
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';

// Cargar helpers
require_once __DIR__ . '/app/helpers/Auth.php';
require_once __DIR__ . '/app/helpers/Validator.php';

// Cargar modelos
require_once __DIR__ . '/app/models/User.php';
require_once __DIR__ . '/app/models/Product.php';
require_once __DIR__ . '/app/models/Client.php';
require_once __DIR__ . '/app/models/Sale.php';
require_once __DIR__ . '/app/models/LoyaltyProgram.php';
require_once __DIR__ . '/app/models/Report.php';
require_once __DIR__ . '/app/models/Permission.php';
require_once __DIR__ . '/app/models/Role.php';

// Cargar controladores
require_once __DIR__ . '/app/controllers/AuthController.php';
require_once __DIR__ . '/app/controllers/DashboardController.php';
require_once __DIR__ . '/app/controllers/InventoryController.php';
require_once __DIR__ . '/app/controllers/SalesController.php';
require_once __DIR__ . '/app/controllers/ClientController.php';
require_once __DIR__ . '/app/controllers/LoyaltyController.php';
require_once __DIR__ . '/app/controllers/ReportController.php';
require_once __DIR__ . '/app/controllers/UserController.php';
require_once __DIR__ . '/app/controllers/RbacController.php';

// Obtener la ruta
$route = $_GET['route'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';

// Rutas públicas (no requieren autenticación)
$publicRoutes = ['login', 'auth'];

// Verificar autenticación
if (!in_array($route, $publicRoutes) && !Auth::check()) {
    redirect('?route=login');
}

// Router
try {
    switch ($route) {
        // === AUTH ===
        case 'login':
            if (Auth::check()) {
                redirect('?route=dashboard');
            }
            $controller = new AuthController();
            $controller->login();
            break;

        case 'auth':
            $controller = new AuthController();
            if ($action === 'login') {
                $controller->authenticate();
            } elseif ($action === 'logout') {
                $controller->logout();
            }
            break;

        // === DASHBOARD ===
        case 'dashboard':
            $controller = new DashboardController();
            $controller->index();
            break;

        // === INVENTARIO ===
        case 'inventory':
            $controller = new InventoryController();
            switch ($action) {
                case 'index':    $controller->index(); break;
                case 'create':   $controller->create(); break;
                case 'store':    $controller->store(); break;
                case 'edit':     $controller->edit(); break;
                case 'update':   $controller->update(); break;
                case 'delete':   $controller->delete(); break;
                case 'search':   $controller->search(); break;
                case 'alerts':   $controller->alerts(); break;
                default:         $controller->index(); break;
            }
            break;

        // === VENTAS / POS ===
        case 'sales':
            $controller = new SalesController();
            switch ($action) {
                case 'index':      $controller->index(); break;
                case 'pos':        $controller->pos(); break;
                case 'process':    $controller->process(); break;
                case 'detail':     $controller->detail(); break;
                case 'cancel':     $controller->cancel(); break;
                case 'search-product': $controller->searchProduct(); break;
                default:           $controller->index(); break;
            }
            break;

        // === CLIENTES ===
        case 'clients':
            $controller = new ClientController();
            switch ($action) {
                case 'index':    $controller->index(); break;
                case 'create':   $controller->create(); break;
                case 'store':    $controller->store(); break;
                case 'profile':  $controller->profile(); break;
                case 'edit':     $controller->edit(); break;
                case 'update':   $controller->update(); break;
                case 'delete':   $controller->delete(); break;
                case 'search':   $controller->search(); break;
                default:         $controller->index(); break;
            }
            break;

        // === FIDELIZACIÓN ===
        case 'loyalty':
            $controller = new LoyaltyController();
            switch ($action) {
                case 'index':      $controller->index(); break;
                case 'redeem':     $controller->redeem(); break;
                case 'add-bonus':  $controller->addBonus(); break;
                default:           $controller->index(); break;
            }
            break;

        // === REPORTES ===
        case 'reports':
            $controller = new ReportController();
            switch ($action) {
                case 'index':       $controller->index(); break;
                case 'sales':       $controller->salesReport(); break;
                case 'inventory':   $controller->inventoryReport(); break;
                case 'clients':     $controller->clientsReport(); break;
                case 'export':      $controller->export(); break;
                default:            $controller->index(); break;
            }
            break;

        // === USUARIOS ===
        case 'users':
            $controller = new UserController();
            switch ($action) {
                case 'index':    $controller->index(); break;
                case 'create':   $controller->create(); break;
                case 'store':    $controller->store(); break;
                case 'edit':     $controller->edit(); break;
                case 'update':   $controller->update(); break;
                case 'delete':   $controller->delete(); break;
                default:         $controller->index(); break;
            }
            break;

        // === SEGURIDAD / RBAC ===
        case 'rbac':
            $controller = new RbacController();
            switch ($action) {
                case 'index':       $controller->index(); break;
                case 'edit-role':   $controller->editRole(); break;
                case 'update-role': $controller->updateRolePermissions(); break;
                case 'store-perm':  $controller->storePermission(); break;
                default:            $controller->index(); break;
            }
            break;

        // === API endpoints para AJAX ===
        case 'api':
            $entity = $_GET['entity'] ?? '';
            switch ($entity) {
                case 'dashboard':
                    $controller = new DashboardController();
                    $controller->getStats();
                    break;
                case 'products':
                    $controller = new InventoryController();
                    $controller->search();
                    break;
                case 'clients':
                    $controller = new ClientController();
                    $controller->search();
                    break;
                case 'reports':
                    $controller = new ReportController();
                    $controller->getData();
                    break;
                default:
                    jsonResponse(['error' => 'Endpoint no encontrado'], 404);
            }
            break;

        // === 404 ===
        default:
            http_response_code(404);
            include __DIR__ . '/views/layouts/main.php';
            break;
    }
} catch (Exception $e) {
    if (isAjax()) {
        jsonResponse(['error' => true, 'message' => $e->getMessage()], 500);
    } else {
        die('<h1>Error del servidor</h1><p>' . htmlspecialchars($e->getMessage()) . '</p>');
    }
}
