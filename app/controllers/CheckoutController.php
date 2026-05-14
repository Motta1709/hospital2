<?php
/**
 * PharmaCRM - Controlador de Checkout con ePayco
 * Integra el flujo de pago usando ePayco Checkout.js (modal onpage)
 */
class CheckoutController {
    private $productModel;

    public function __construct() {
        $this->productModel = new Product();
    }

    /**
     * Muestra la pagina de checkout con los productos seleccionados
     */
    public function index() {
        // Verificar que haya sesion de cliente
        if (empty($_SESSION['client_id'])) {
            setFlash('error', 'Debe iniciar sesion para realizar una compra.');
            redirect('?route=login');
        }

        // Obtener items seleccionados del carrito
        $cartItems = [];
        $total = 0;

        foreach ($_SESSION['cart'] ?? [] as $id => $item) {
            if (!$item['selected']) continue;
            $product = $this->productModel->findById($id);
            if ($product) {
                $product['qty'] = $item['qty'];
                $subtotal = $product['sale_price'] * $item['qty'];
                $product['subtotal'] = $subtotal;
                $cartItems[] = $product;
                $total += $subtotal;
            }
        }

        if (empty($cartItems)) {
            setFlash('error', 'No hay productos seleccionados para pagar.');
            redirect('?route=cart');
        }

        // Por solicitud del usuario, el IVA para ePayco se establece en 0.
        $baseIva     = $total;                    // base = total
        $iva         = 0;                         // IVA = 0
        $totalConIva = $total;                    // total = precio final (sin cambio)

        // Generar referencia unica
        $invoiceRef = 'PCRM-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));

        // Datos del cliente
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM clients WHERE id = ?");
        $stmt->execute([$_SESSION['client_id']]);
        $client = $stmt->fetch();

        // Registrar la venta en estado pendiente en la base de datos
        try {
            $db->beginTransaction();

            $stmtSale = $db->prepare("
                INSERT INTO sales (client_id, user_id, invoice_number, subtotal, tax_amount, total, payment_method, status)
                VALUES (?, NULL, ?, ?, ?, ?, 'card', 'pending')
            ");
            $stmtSale->execute([
                $_SESSION['client_id'],
                $invoiceRef,
                $baseIva,      // subtotal = base sin IVA
                $iva,
                $totalConIva
            ]);
            $saleId = $db->lastInsertId();

            $stmtItem = $db->prepare("
                INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, subtotal)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            foreach ($cartItems as $item) {
                $stmtItem->execute([
                    $saleId,
                    $item['id'],
                    $item['qty'],
                    $item['sale_price'],
                    $item['subtotal']
                ]);
            }
            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            setFlash('error', 'Error al procesar la orden: ' . $e->getMessage());
            redirect('?route=cart');
        }

        // Guardar referencia en sesion para validar despues
        $_SESSION['checkout_ref'] = $invoiceRef;
        $_SESSION['checkout_total'] = $totalConIva;

        $data = [
            'items'    => $cartItems,
            'subtotal' => $baseIva,      // base sin IVA (para desglose)
            'iva'      => $iva,
            'total'    => $totalConIva,  // precio final (ya incluye IVA)
            'invoiceRef' => $invoiceRef,
            'client'   => $client,
        ];

        include APP_ROOT . '/views/checkout/index.php';
    }

    /**
     * Pagina de respuesta despues del pago (redireccion del cliente)
     */
    public function response() {
        $refPayco = $_GET['ref_payco'] ?? null;
        $result = null;

        if ($refPayco) {
            // Consultar estado de la transaccion en ePayco
            $url = "https://secure.epayco.co/validation/v1/reference/{$refPayco}";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            curl_close($ch);

            if ($response) {
                $result = json_decode($response, true);
                
                // Limpiar carrito si fue exitoso o esta pendiente
                $status = $result['data']['x_response'] ?? '';
                if ($status === 'Aceptada' || $status === 'Pendiente') {
                    // Remover solo los seleccionados
                    foreach ($_SESSION['cart'] as $id => $item) {
                        if ($item['selected']) {
                            unset($_SESSION['cart'][$id]);
                        }
                    }
                }
            }
        }

        $data = ['result' => $result, 'ref_payco' => $refPayco];
        include APP_ROOT . '/views/checkout/response.php';
    }

    /**
     * URL de confirmacion (webhook background de ePayco)
     */
    public function confirm() {
        // ePayco envia los datos por POST o GET
        $refPayco = $_REQUEST['x_ref_payco'] ?? null;
        $response = $_REQUEST['x_response'] ?? null;
        $amount = $_REQUEST['x_amount'] ?? null;
        $signature = $_REQUEST['x_signature'] ?? null;
        $transactionId = $_REQUEST['x_transaction_id'] ?? null;

        if (!$refPayco || !$signature) {
            http_response_code(400);
            echo 'INVALID';
            exit;
        }

        // Validar firma de ePayco
        $expectedSignature = md5(
            EPAYCO_CUST_ID .
            EPAYCO_P_KEY .
            $refPayco .
            $transactionId .
            $amount .
            EPAYCO_CURRENCY
        );

        if ($signature !== $expectedSignature) {
            http_response_code(403);
            echo 'SIGNATURE_MISMATCH';
            exit;
        }

        // Procesar segun respuesta
        $db = Database::getInstance()->getConnection();

        if ($response === 'Aceptada') {
            // Registrar venta en BD
            $stmt = $db->prepare("
                UPDATE sales SET status = 'completed', epayco_ref = ?
                WHERE invoice_number = ?
            ");
            $invoiceRef = $_REQUEST['x_extra1'] ?? '';
            $stmt->execute([$refPayco, $invoiceRef]);
        }

        echo 'OK';
        exit;
    }
}
