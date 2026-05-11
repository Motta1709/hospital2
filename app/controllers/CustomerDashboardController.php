<?php
/**
 * PharmaCRM - Controlador del Dashboard del Cliente
 * Orquesta todos los modulos del panel del usuario:
 * Perfil, PQRSF, Compras, Devoluciones, Domicilios, Reservas
 */
class CustomerDashboardController {
    private $profileModel;
    private $clientId;

    public function __construct() {
        $this->profileModel = new CustomerProfile();
        // Usar client_id de la sesion autenticada
        $this->clientId = intval($_SESSION['client_id'] ?? 0);
        if (!$this->clientId) {
            setFlash('error', 'Debe iniciar sesion como cliente.');
            redirect('?route=login');
        }
    }

    /**
     * Dashboard principal del cliente
     */
    public function index() {
        $profile = $this->profileModel->getProfile($this->clientId);
        if (!$profile) { setFlash('error', 'Cliente no encontrado.'); redirect('?route=home'); }

        $section = $_GET['section'] ?? 'tienda';
        $summary = $this->profileModel->getDashboardSummary($this->clientId);
        
        $pqrsfModel = new PQRSF();
        $orderModel = new CustomerOrder();
        $reservaModel = new ReservaMedicamento();
        $domicilioModel = new Domicilio();
        $notifModel = new Notificacion();
        $devolucionModel = new Devolucion();
        $productModel = new Product();

        $data = [
            'profile' => $profile,
            'summary' => $summary,
            'recentOrders' => $orderModel->getByClient($this->clientId, 1, 10),
            'recentPqrsf' => $pqrsfModel->getByClient($this->clientId, 1, 5),
            'reservas' => $reservaModel->getByClient($this->clientId, 1, 10),
            'domicilios' => $domicilioModel->getByClient($this->clientId, 1, 10),
            'notifications' => $notifModel->getByClient($this->clientId, 1, 15),
            'unreadCount' => $notifModel->countUnread($this->clientId),
            'monthlySpending' => $orderModel->getMonthlySpending($this->clientId, 6),
            'pqrsfCounts' => $pqrsfModel->getStatusCounts($this->clientId),
            'addresses' => $this->profileModel->getAddresses($this->clientId),
        ];

        // Datos especificos por seccion
        if ($section === 'devoluciones') {
            $data['eligibleOrders'] = $orderModel->getEligibleForReturn($this->clientId);
            $data['misDevoluciones'] = $devolucionModel->getByClient($this->clientId);
        } elseif ($section === 'reservas') {
            $data['availableProducts'] = $reservaModel->getAvailableProducts();
        } elseif ($section === 'tienda') {
            $search = $_GET['search'] ?? '';
            $category = $_GET['category'] ?? null;
            $page = max(1, intval($_GET['page'] ?? 1));
            
            $data['products'] = $productModel->getAll($page, 12, $search, $category);
            $totalProducts = $productModel->count($search, $category);
            $data['categories'] = $productModel->getCategories();
            $data['totalPages'] = ceil($totalProducts / 12);
            $data['currentPage'] = $page;
            $data['search'] = $search;
            $data['currentCategory'] = $category;
        }

        $pageTitle = 'Mi Panel - ' . ucfirst($section);
        $currentRoute = 'customer-dashboard';
        $content = APP_ROOT . '/views/customer/dashboard.php';
        include APP_ROOT . '/views/customer/layouts/customer_main.php';
    }

    /**
     * Maneja la subida de archivos (prescripciones/evidencias)
     */
    private function handleFileUpload($fileKey, $targetDir = 'uploads/prescriptions/') {
        if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) return null;
        
        if (!is_dir(APP_ROOT . '/public/' . $targetDir)) {
            mkdir(APP_ROOT . '/public/' . $targetDir, 0777, true);
        }

        $ext = pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('DOC_') . '.' . $ext;
        $targetPath = $targetDir . $fileName;
        
        if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], APP_ROOT . '/public/' . $targetPath)) {
            return [
                'path' => $targetPath,
                'name' => $_FILES[$fileKey]['name']
            ];
        }
        return null;
    }

    /**
     * Crear nueva PQRSF
     */
    public function createPqrsf() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('?route=customer-dashboard');
        $v = new Validator($_POST);
        $v->required('tipo', 'Tipo')->required('asunto', 'Asunto')->required('descripcion', 'Descripcion');
        if ($v->fails()) { setFlash('error', $v->firstError()); redirect('?route=customer-dashboard'); }

        try {
            $pqrsfModel = new PQRSF();
            $pqrsfModel->create($this->clientId, $_POST);
            setFlash('success', 'Su solicitud PQRSF ha sido radicada exitosamente.');
        } catch (Exception $e) {
            setFlash('error', 'Error: ' . $e->getMessage());
        }
        redirect('?route=customer-dashboard&section=pqrsf');
    }

    /**
     * Crear nueva solicitud de devolucion
     */
    public function createDevolucion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('?route=customer-dashboard');
        try {
            $fileData = $this->handleFileUpload('evidencia', 'uploads/returns/');
            if ($fileData) {
                $_POST['evidencia_path'] = $fileData['path'];
            }
            
            $devolucionModel = new Devolucion();
            $devolucionModel->create($this->clientId, $_POST);
            setFlash('success', 'Solicitud de devolucion registrada. Sera revisada por nuestro equipo.');
        } catch (Exception $e) {
            setFlash('error', 'Error: ' . $e->getMessage());
        }
        redirect('?route=customer-dashboard&section=devoluciones');
    }

    /**
     * Crear nueva reserva de medicamento
     */
    public function createReserva() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('?route=customer-dashboard');
        try {
            $fileData = $this->handleFileUpload('prescripcion', 'uploads/prescriptions/');
            if ($fileData) {
                $_POST['prescripcion_path'] = $fileData['path'];
                $_POST['prescripcion_nombre'] = $fileData['name'];
            }
            
            $reservaModel = new ReservaMedicamento();
            $reservaModel->create($this->clientId, $_POST);
            setFlash('success', 'Reserva de medicamento registrada exitosamente.');
        } catch (Exception $e) {
            setFlash('error', 'Error: ' . $e->getMessage());
        }
        redirect('?route=customer-dashboard&section=reservas');
    }

    /**
     * Actualizar perfil del cliente
     */
    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('?route=customer-dashboard');
        try {
            $this->profileModel->updateProfile($this->clientId, $_POST);
            setFlash('success', 'Perfil actualizado correctamente.');
        } catch (Exception $e) {
            setFlash('error', 'Error: ' . $e->getMessage());
        }
        redirect('?route=customer-dashboard&section=perfil');
    }

    /**
     * Agregar nueva direccion
     */
    public function addAddress() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('?route=customer-dashboard');
        try {
            $this->profileModel->addAddress($this->clientId, $_POST);
            setFlash('success', 'Direccion agregada correctamente.');
        } catch (Exception $e) {
            setFlash('error', 'Error: ' . $e->getMessage());
        }
        redirect('?route=customer-dashboard&section=perfil');
    }

    /**
     * Marcar notificaciones como leidas
     */
    public function markNotificationsRead() {
        $notifModel = new Notificacion();
        $notifModel->markAllAsRead($this->clientId);
        if (isAjax()) {
            jsonResponse(['success' => true]);
        }
        redirect('?route=customer-dashboard');
    }

    /**
     * API: Obtener items de una orden (AJAX)
     */
    public function getOrderItems() {
        $saleId = intval($_GET['id'] ?? 0);
        if (!$saleId) jsonResponse([]);
        
        $orderModel = new CustomerOrder();
        $items = $orderModel->getItems($saleId);
        
        // Formatear para el select
        $formatted = array_map(function($item) {
            return [
                'id' => $item['id'],
                'product_name' => $item['product_name'] . ' (' . $item['presentation'] . ')',
                'quantity' => $item['quantity'],
                'unit_price' => formatCurrency($item['unit_price'])
            ];
        }, $items);
        
        jsonResponse($formatted);
    }

    /**
     * API: Obtener datos del dashboard (AJAX)
     */
    public function getStats() {
        $summary = $this->profileModel->getDashboardSummary($this->clientId);
        jsonResponse($summary);
    }
}
