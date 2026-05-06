<?php
/**
 * PharmaCRM - Controlador de Checkout y ePayco
 */
class CheckoutController {
    private $productModel;
    private $saleModel;

    public function __construct() {
        $this->productModel = new Product();
        $this->saleModel = new Sale();
    }

    public function index() {
        if (empty($_SESSION['cart'])) {
            redirect('?route=cart');
        }

        $itemsToPay = [];
        $total = 0;

        foreach ($_SESSION['cart'] as $id => $item) {
            if ($item['selected']) {
                $product = $this->productModel->findById($id);
                if ($product) {
                    $product['qty'] = $item['qty'];
                    $product['subtotal'] = $product['sale_price'] * $item['qty'];
                    $itemsToPay[] = $product;
                    $total += $product['subtotal'];
                }
            }
        }

        if ($total <= 0) {
            setFlash('error', 'No has seleccionado productos para pagar.');
            redirect('?route=cart');
        }

        $data = [
            'items' => $itemsToPay,
            'total' => $total,
            'invoice' => generateInvoiceNumber()
        ];

        $pageTitle = 'Pagar Pedido';
        $currentRoute = 'checkout';
        $content = APP_ROOT . '/views/checkout/index.php';
        include APP_ROOT . '/views/layouts/main.php';
    }

    /**
     * Procesa la respuesta de ePayco (Redirección del cliente)
     */
    public function response() {
        $ref_payco = $_GET['ref_payco'] ?? null;
        if (!$ref_payco) {
            redirect('?route=home');
        }

        // En una implementación real, aquí se consultaría el estado de la transacción vía API
        // https://api.secure.payco.co/validation/v1/reference/{ref_payco}
        
        $data = [
            'ref_payco' => $ref_payco,
            'pageTitle' => 'Estado de la Transacción'
        ];

        $pageTitle = 'Confirmación de Pago';
        $currentRoute = 'checkout';
        $content = APP_ROOT . '/views/checkout/response.php';
        include APP_ROOT . '/views/layouts/main.php';
    }

    /**
     * Webhook de ePayco (Confirmación servidor a servidor)
     */
    public function confirmation() {
        // ePayco envía los datos por POST
        $x_signature = $_POST['x_signature'] ?? null;
        $x_cod_transaction_state = $_POST['x_cod_transaction_state'] ?? null;
        $x_id_invoice = $_POST['x_id_invoice'] ?? null;

        // 1 = Aceptada, 3 = Pendiente
        if ($x_cod_transaction_state == 1) {
            // Aquí se crearía la venta en la base de datos si no existe
            // Se descontaría el inventario, etc.
        }

        http_response_code(200);
        echo "OK";
        exit;
    }
}

