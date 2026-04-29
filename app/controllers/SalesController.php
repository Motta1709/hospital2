<?php
class SalesController {
    private $saleModel;

    public function __construct() {
        $this->saleModel = new Sale();
    }

    public function index() {
        if (!Auth::hasPermission('view_sales')) {
            setFlash('error', 'No tienes permiso para ver el historial de ventas.');
            redirect('?route=dashboard');
        }
        $page = max(1, intval($_GET['page'] ?? 1));
        $dateFrom = $_GET['date_from'] ?? null;
        $dateTo = $_GET['date_to'] ?? null;

        $data = [
            'sales' => $this->saleModel->getAll($page, ITEMS_PER_PAGE, $dateFrom, $dateTo),
            'totalSales' => $this->saleModel->count($dateFrom, $dateTo),
            'page' => $page,
            'totalPages' => ceil($this->saleModel->count($dateFrom, $dateTo) / ITEMS_PER_PAGE),
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
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
                'invoice_number' => generateInvoiceNumber(),
                'subtotal' => $input['subtotal'],
                'discount_amount' => $input['discount_amount'] ?? 0,
                'tax_amount' => 0,
                'total' => $input['total'],
                'payment_method' => $input['payment_method'] ?? 'cash',
                'cash_received' => $input['cash_received'] ?? $input['total'],
                'change_amount' => $input['change_amount'] ?? 0,
                'loyalty_points_earned' => floor($input['total'] / 1000),
                'notes' => $input['notes'] ?? null,
            ];

            $saleId = $this->saleModel->create($saleData, $input['items']);
            jsonResponse(['success' => true, 'sale_id' => $saleId, 'invoice' => $saleData['invoice_number']]);
        } catch (Exception $e) {
            jsonResponse(['error' => true, 'message' => $e->getMessage()], 500);
        }
    }

    public function detail() {
        $id = intval($_GET['id'] ?? 0);
        $sale = $this->saleModel->findById($id);
        if (!$sale) { setFlash('error', 'Venta no encontrada.'); redirect('?route=sales'); }

        $data = ['sale' => $sale, 'items' => $this->saleModel->getSaleItems($id)];

        if (isAjax()) { jsonResponse($data); }

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
            $this->saleModel->cancel($id);
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
        $productModel = new Product();
        jsonResponse($productModel->search($q));
    }
}
