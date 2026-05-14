<?php
class SalesController {
    private $saleRepository;
    private $productRepository;
    private $checkoutService;

    public function __construct(
        \App\Interfaces\SaleRepositoryInterface $saleRepository = null,
        \App\Interfaces\ProductRepositoryInterface $productRepository = null,
        \App\Interfaces\CheckoutServiceInterface $checkoutService = null
    ) {
        $this->saleRepository = $saleRepository ?? new \App\Repositories\SaleRepository();
        $this->productRepository = $productRepository ?? new \App\Repositories\ProductRepository();
        $this->checkoutService = $checkoutService ?? new \App\Services\CheckoutService($this->saleRepository, $this->productRepository);
    }

    public function index() {
        if (!Auth::hasPermission('view_sales')) {
            setFlash('error', 'No tienes permiso para ver el historial de ventas.');
            redirect('?route=dashboard');
        }
        
        $filters = [
            'date_from' => $_GET['date_from'] ?? null,
            'date_to'   => $_GET['date_to'] ?? null,
            'status'    => $_GET['status'] ?? null
        ];

        $sales = $this->saleRepository->all($filters);

        $data = [
            'sales' => $sales,
            'totalSales' => count($sales),
            'dateFrom' => $filters['date_from'],
            'dateTo' => $filters['date_to'],
            'status' => $filters['status']
        ];

        $pageTitle = 'Historial de Ventas';
        $currentRoute = 'sales';
        $content = APP_ROOT . '/views/sales/index.php';
        include APP_ROOT . '/views/layouts/main.php';
    }

    public function pos() {
        if (!Auth::hasPermission('process_sales')) {
            setFlash('error', 'No tienes permiso para acceder al Punto de Venta.');
            redirect('?route=dashboard');
        }

        // Pre-cargar todos los productos disponibles (stock > 0) para mostrarlos de inmediato
        $allProducts = $this->productRepository->search('');

        $data = [
            'invoiceNumber' => generateInvoiceNumber(),
            'products'      => $allProducts,
        ];
        $pageTitle = 'Punto de Venta';
        $currentRoute = 'sales';
        $content = APP_ROOT . '/views/sales/pos.php';
        include APP_ROOT . '/views/layouts/main.php';
    }

    public function process() {
        if (!Auth::hasPermission('process_sales')) {
            jsonResponse(['error' => true, 'message' => 'No autorizado'], 403);
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('?route=sales&action=pos');

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['items'])) {
            jsonResponse(['error' => true, 'message' => 'No hay productos en la venta.'], 400);
        }

        try {
            $saleData = [
                'client_id' => $input['client_id'] ?: null,
                'user_id' => Auth::id(),
                'branch_id' => $_SESSION['branch_id'] ?? 1,
                'invoice_number' => generateInvoiceNumber(),
                'subtotal' => $input['subtotal'],
                'discount_amount' => $input['discount_amount'] ?? 0,
                'total' => $input['total'],
                'payment_method' => $input['payment_method'] ?? 'cash',
                'cash_received' => $input['cash_received'] ?? $input['total'],
                'change_amount' => $input['change_amount'] ?? 0,
                'loyalty_points_earned' => floor($input['total'] / 1000),
                'notes' => $input['notes'] ?? null,
            ];

            // Delegar al servicio (SOLID: SRP)
            $saleId = $this->checkoutService->processSale($saleData, $input['items']);
            
            jsonResponse(['success' => true, 'sale_id' => $saleId, 'invoice' => $saleData['invoice_number']]);
        } catch (Exception $e) {
            jsonResponse(['error' => true, 'message' => $e->getMessage()], 500);
        }
    }

    public function detail() {
        $id = intval($_GET['id'] ?? 0);
        $sale = $this->saleRepository->findById($id);
        if (!$sale) { 
            setFlash('error', 'Venta no encontrada.'); 
            redirect('?route=sales'); 
        }

        $data = [
            'sale' => $sale, 
            'items' => $this->saleRepository->getItems($id)
        ];

        if (isAjax()) { 
            jsonResponse($data); 
        }

        $pageTitle = 'Detalle de Venta';
        $currentRoute = 'sales';
        $content = APP_ROOT . '/views/sales/index.php';
        include APP_ROOT . '/views/layouts/main.php';
    }

    public function cancel() {
        if (!Auth::hasPermission('cancel_sales')) {
            if (isAjax()) jsonResponse(['error' => 'No autorizado'], 403);
            setFlash('error', 'No tienes permiso para anular ventas.');
            redirect('?route=sales');
        }
        $id = intval($_GET['id'] ?? 0);
        try {
            $this->saleRepository->delete($id); // Status -> cancelled
            if (isAjax()) jsonResponse(['success' => true]);
            setFlash('success', 'Venta anulada.');
        } catch (Exception $e) {
            if (isAjax()) jsonResponse(['error' => $e->getMessage()], 500);
            setFlash('error', 'Error: ' . $e->getMessage());
        }
        redirect('?route=sales');
    }

    public function searchProduct() {
        $q = sanitize($_GET['q'] ?? '');
        jsonResponse($this->productRepository->search($q));
    }

    /**
     * Sincroniza manualmente un pago con ePayco
     */
    public function syncPayment() {
        if (!Auth::hasPermission('view_sales')) {
            setFlash('error', 'No tienes permiso para realizar esta acción.');
            redirect('?route=sales');
        }

        $invoice = sanitize($_GET['invoice'] ?? '');
        $refPayco = sanitize($_GET['ref_payco'] ?? '');

        if (!$invoice || !$refPayco) {
            setFlash('error', 'Datos insuficientes para la sincronización.');
            redirect('?route=sales');
        }

        // Consultar estado en ePayco
        $url = "https://secure.epayco.co/validation/v1/reference/{$refPayco}";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response && $httpCode === 200) {
            $result = json_decode($response, true);
            $txStatus = $result['data']['x_response'] ?? 'Pendiente';
            
            $db = Database::getInstance()->getConnection();
            
            if ($txStatus === 'Aceptada') {
                $stmt = $db->prepare("UPDATE sales SET status = 'completed', epayco_ref = ? WHERE invoice_number = ?");
                $stmt->execute([$refPayco, $invoice]);
                setFlash('success', "Pago confirmado exitosamente para la factura $invoice.");
            } elseif ($txStatus === 'Rechazada' || $txStatus === 'Fallida') {
                $stmt = $db->prepare("UPDATE sales SET status = 'failed', epayco_ref = ? WHERE invoice_number = ?");
                $stmt->execute([$refPayco, $invoice]);
                setFlash('error', "El pago para la factura $invoice fue rechazado o falló.");
            } else {
                // Si sigue pendiente, al menos guardamos la referencia para futuros intentos
                $stmt = $db->prepare("UPDATE sales SET epayco_ref = ? WHERE invoice_number = ?");
                $stmt->execute([$refPayco, $invoice]);
                setFlash('warning', "La transacción $refPayco sigue en estado: $txStatus.");
            }
        } else {
            setFlash('error', "No se pudo conectar con ePayco para validar la referencia $refPayco.");
        }

        redirect('?route=sales');
    }
}

