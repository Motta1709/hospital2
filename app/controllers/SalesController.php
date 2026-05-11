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
        
        $sales = $this->saleRepository->all();

        $data = [
            'sales' => $sales,
            'totalSales' => count($sales),
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
        $data = ['invoiceNumber' => generateInvoiceNumber()];
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
}

