<?php
/**
 * PharmaCRM - Controlador de Pagos con Nequi via ePayco SDK
 * 
 * Flujo de Nequi:
 * 1. Cliente selecciona "Pagar con Nequi" en checkout
 * 2. Se crea la transaccion via API de ePayco (backend)
 * 3. El usuario recibe notificacion push en su app Nequi
 * 4. El usuario aprueba en la app Nequi
 * 5. Se consulta el estado de la transaccion periodicamente
 * 6. ePayco confirma via webhook
 */
class NequiController {
    private $productModel;
    private $epayco;

    public function __construct() {
        $this->productModel = new Product();

        // Cargar autoload de Composer para el SDK de ePayco
        $autoloadPath = APP_ROOT . '/vendor/autoload.php';
        if (file_exists($autoloadPath)) {
            require_once $autoloadPath;
        }
    }

    /**
     * Inicializa la instancia del SDK de ePayco
     */
    private function getEpaycoInstance() {
        if (!$this->epayco) {
            $this->epayco = new \Epayco\Epayco(array(
                "apiKey"     => EPAYCO_PUBLIC_KEY,
                "privateKey" => EPAYCO_PRIVATE_KEY,
                "lenguage"   => EPAYCO_LANG === 'es' ? 'ES' : 'EN',
                "test"       => EPAYCO_TESTING
            ));
        }
        return $this->epayco;
    }

    /**
     * Muestra la pagina de checkout con opcion Nequi
     * Se reutiliza la misma logica de preparar los items del carrito
     */
    public function index() {
        // Verificar sesion de cliente
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

        // El precio publicado YA INCLUYE IVA (19%).
        // Se extrae el IVA del total en lugar de sumarlo.
        $baseIva     = round($total / 1.19, 2);  // base sin IVA
        $iva         = round($total - $baseIva, 2); // IVA contenido
        $totalConIva = $total;                    // total = precio final (sin cambio)

        // Generar referencia unica
        $invoiceRef = 'PCRM-NQ-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));

        // Datos del cliente
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM clients WHERE id = ?");
        $stmt->execute([$_SESSION['client_id']]);
        $client = $stmt->fetch();

        // Guardar referencia en sesion
        $_SESSION['nequi_checkout_ref']      = $invoiceRef;
        $_SESSION['nequi_checkout_total']    = $totalConIva;   // precio final (con IVA incluido)
        $_SESSION['nequi_checkout_items']    = $cartItems;
        $_SESSION['nequi_checkout_subtotal'] = $baseIva;       // base sin IVA (para desglose ePayco)
        $_SESSION['nequi_checkout_iva']      = $iva;           // IVA extraido

        $data = [
            'items'      => $cartItems,
            'subtotal'   => $baseIva,    // base sin IVA para mostrar en UI
            'iva'        => $iva,
            'total'      => $totalConIva, // precio final
            'invoiceRef' => $invoiceRef,
            'client'     => $client,
        ];

        include APP_ROOT . '/views/nequi/checkout.php';
    }

    /**
     * Procesa el pago Nequi via AJAX (llamada desde el frontend)
     * Crea la transaccion en ePayco y envia push a la app Nequi del usuario
     */
    public function create() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Metodo no permitido'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (empty($_SESSION['client_id'])) {
            echo json_encode(['success' => false, 'message' => 'Sesion expirada'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $phone = trim($input['phone'] ?? '');

        // Validar telefono colombiano (10 digitos empezando por 3)
        if (!preg_match('/^3\d{9}$/', $phone)) {
            echo json_encode(['success' => false, 'message' => 'Numero de celular Nequi invalido. Debe tener 10 digitos y empezar por 3.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Recuperar datos de la sesion
        $invoiceRef = $_SESSION['nequi_checkout_ref'] ?? null;
        $total = $_SESSION['nequi_checkout_total'] ?? 0;
        $subtotal = $_SESSION['nequi_checkout_subtotal'] ?? 0;
        $iva = $_SESSION['nequi_checkout_iva'] ?? 0;
        $cartItems = $_SESSION['nequi_checkout_items'] ?? [];

        if (!$invoiceRef || $total <= 0 || empty($cartItems)) {
            echo json_encode(['success' => false, 'message' => 'Datos de checkout invalidos. Intente de nuevo.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Obtener datos del cliente
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM clients WHERE id = ?");
        $stmt->execute([$_SESSION['client_id']]);
        $client = $stmt->fetch();

        if (!$client) {
            echo json_encode(['success' => false, 'message' => 'Cliente no encontrado.'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Registrar la venta en estado pendiente
        try {
            $db->beginTransaction();

            $stmtSale = $db->prepare("
                INSERT INTO sales (client_id, user_id, invoice_number, subtotal, tax_amount, total, payment_method, status)
                VALUES (?, NULL, ?, ?, ?, ?, 'nequi', 'pending')
            ");
            $stmtSale->execute([
                $_SESSION['client_id'],
                $invoiceRef,
                $subtotal,
                $iva,
                $total
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
            echo json_encode(['success' => false, 'message' => 'Error al registrar la orden: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Crear la transaccion Nequi via ePayco SDK
        try {
            $epayco = $this->getEpaycoInstance();

            // Obtener IP del cliente
            $clientIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            if (strpos($clientIp, ',') !== false) {
                $clientIp = trim(explode(',', $clientIp)[0]);
            }

            // URL base para respuestas (debe ser accesible desde internet en produccion)
            $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                     . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
                     . APP_URL;

            $nequiData = array(
                "phone"        => $phone,
                "doc_type"     => $client['document_type'] ?? 'CC',
                "doc_number"   => $client['document_number'] ?? '',
                "name"         => $client['first_name'] ?? 'Cliente',
                "last_name"    => $client['last_name'] ?? 'PharmaCRM',
                "email"        => $client['email'] ?? '',
                "cell_phone"   => $phone,
                "invoice"      => $invoiceRef,
                "description"  => "Pedido PharmaCRM " . $invoiceRef,
                "value"        => strval($total),
                "tax"          => strval($iva),
                "tax_base"     => strval($subtotal),
                "currency"     => EPAYCO_CURRENCY,
                "type_person"  => "0",
                "country"      => EPAYCO_COUNTRY,
                "city"         => $client['city'] ?? 'Bogota',
                "address"      => $client['address'] ?? 'N/A',
                "ip"           => $clientIp,
                "url_response"     => $baseUrl . '/?route=nequi&action=response',
                "url_confirmation" => $baseUrl . '/?route=nequi&action=confirm',
                "method_confirmation" => "POST",
                "extra1"       => $invoiceRef,
                "extra2"       => strval($_SESSION['client_id']),
                "extra3"       => strval($saleId),
            );

            // Intentar con el metodo de Daviplata/Nequi (el SDK de ePayco maneja Nequi internamente)
            // El checkout.js de ePayco ya incluye Nequi. Usaremos la API REST directa.
            $result = $this->createNequiTransaction($nequiData);

            if ($result && isset($result['success']) && $result['success']) {
                // Guardar referencia de transaccion en sesion
                $_SESSION['nequi_ref_payco'] = $result['data']['ref_payco'] ?? '';
                $_SESSION['nequi_transaction_id'] = $result['data']['transaction_id'] ?? '';
                $_SESSION['nequi_sale_id'] = $saleId;

                echo json_encode([
                    'success' => true,
                    'message' => 'Transaccion creada. Aprueba el pago en tu app Nequi.',
                    'data' => [
                        'ref_payco'      => $result['data']['ref_payco'] ?? '',
                        'transaction_id' => $result['data']['transaction_id'] ?? '',
                        'invoice'        => $invoiceRef,
                    ]
                ], JSON_UNESCAPED_UNICODE);
            } else {
                // Si la API REST falla, informar al usuario que use el checkout estandar con Nequi
                $errorMsg = $result['message'] ?? 'Error al crear transaccion Nequi';

                // Actualizar la venta a fallida
                $stmtFail = $db->prepare("UPDATE sales SET status = 'failed' WHERE id = ?");
                $stmtFail->execute([$saleId]);

                echo json_encode([
                    'success' => false,
                    'message' => $errorMsg,
                    'fallback' => true
                ], JSON_UNESCAPED_UNICODE);
            }

        } catch (\Exception $e) {
            // Si hay error, marcar la venta como fallida
            $stmtFail = $db->prepare("UPDATE sales SET status = 'failed' WHERE id = ?");
            $stmtFail->execute([$saleId]);

            echo json_encode([
                'success' => false,
                'message' => 'Error al procesar con Nequi: ' . $e->getMessage(),
                'fallback' => true
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * Crea la transaccion Nequi usando la API REST de ePayco directamente
     * Esto envia un push notification a la app Nequi del usuario
     */
    private function createNequiTransaction(array $data): array {
        try {
            $epayco = $this->getEpaycoInstance();

            // Usar cash->create con "nequi" como metodo de pago
            // ePayco maneja Nequi como un metodo de pago en efectivo/billetera digital
            $endDate = date('Y-m-d', strtotime('+1 day'));
            $data['end_date'] = $endDate;

            $response = $epayco->cash->create("nequi", $data);

            if (is_object($response)) {
                $response = json_decode(json_encode($response), true);
            }

            // Verificar si la transaccion fue exitosa
            if (isset($response['success']) && $response['success']) {
                return [
                    'success' => true,
                    'data' => [
                        'ref_payco'      => $response['data']['ref_payco'] ?? $response['refPayco'] ?? '',
                        'transaction_id' => $response['data']['transactionID'] ?? $response['data']['transaction_id'] ?? '',
                    ]
                ];
            }

            // Intentar extraer datos de la respuesta aunque no sea "success"
            if (isset($response['data']['ref_payco']) || isset($response['refPayco'])) {
                return [
                    'success' => true,
                    'data' => [
                        'ref_payco'      => $response['data']['ref_payco'] ?? $response['refPayco'] ?? '',
                        'transaction_id' => $response['data']['transactionID'] ?? $response['data']['transaction_id'] ?? '',
                    ]
                ];
            }

            return [
                'success' => false,
                'message' => $response['message'] ?? $response['titleResponse'] ?? 'Error en la transaccion Nequi'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error de comunicacion con ePayco: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Consulta el estado de la transaccion Nequi via AJAX (polling)
     */
    public function status() {
        header('Content-Type: application/json; charset=utf-8');

        $refPayco = $_GET['ref_payco'] ?? $_SESSION['nequi_ref_payco'] ?? null;

        if (!$refPayco) {
            echo json_encode(['success' => false, 'message' => 'Referencia no encontrada'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Consultar directamente al API de validacion de ePayco
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
            $codResponse = $result['data']['x_cod_response'] ?? 0;

            $statusMap = [
                'Aceptada'  => 'approved',
                'Rechazada' => 'rejected',
                'Pendiente' => 'pending',
                'Fallida'   => 'failed',
            ];

            $normalizedStatus = $statusMap[$txStatus] ?? 'pending';

            // Si esta aprobada, actualizar la venta
            if ($normalizedStatus === 'approved') {
                $this->updateSaleStatus($refPayco, 'completed');
                // Limpiar carrito
                $this->clearSelectedCartItems();
            } elseif ($normalizedStatus === 'rejected' || $normalizedStatus === 'failed') {
                $this->updateSaleStatus($refPayco, 'failed');
            }

            echo json_encode([
                'success' => true,
                'status'  => $normalizedStatus,
                'data'    => [
                    'response'       => $txStatus,
                    'ref_payco'      => $result['data']['x_ref_payco'] ?? $refPayco,
                    'transaction_id' => $result['data']['x_transaction_id'] ?? '',
                    'amount'         => $result['data']['x_amount'] ?? 0,
                    'franchise'      => $result['data']['x_franchise'] ?? 'Nequi',
                    'cod_response'   => $codResponse,
                ]
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => true,
                'status'  => 'pending',
                'data'    => ['response' => 'Consultando...']
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * Pagina de respuesta despues del pago Nequi
     */
    public function response() {
        $refPayco = $_GET['ref_payco'] ?? $_SESSION['nequi_ref_payco'] ?? null;
        $result = null;

        if ($refPayco) {
            $url = "https://secure.epayco.co/validation/v1/reference/{$refPayco}";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            curl_close($ch);

            if ($response) {
                $result = json_decode($response, true);

                $status = $result['data']['x_response'] ?? '';
                if ($status === 'Aceptada') {
                    $this->updateSaleStatus($refPayco, 'completed');
                    $this->clearSelectedCartItems();
                }
            }
        }

        $data = ['result' => $result, 'ref_payco' => $refPayco];
        include APP_ROOT . '/views/nequi/response.php';
    }

    /**
     * URL de confirmacion (webhook de ePayco para Nequi)
     */
    public function confirm() {
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
            $stmt = $db->prepare("
                UPDATE sales SET status = 'completed', epayco_ref = ?
                WHERE invoice_number = ? AND payment_method = 'nequi'
            ");
            $invoiceRef = $_REQUEST['x_extra1'] ?? '';
            $stmt->execute([$refPayco, $invoiceRef]);
        } elseif ($response === 'Rechazada' || $response === 'Fallida') {
            $invoiceRef = $_REQUEST['x_extra1'] ?? '';
            $stmt = $db->prepare("
                UPDATE sales SET status = 'failed'
                WHERE invoice_number = ? AND payment_method = 'nequi'
            ");
            $stmt->execute([$invoiceRef]);
        }

        echo 'OK';
        exit;
    }

    /**
     * Actualiza el estado de la venta por referencia ePayco
     */
    private function updateSaleStatus(string $refPayco, string $status) {
        try {
            $db = Database::getInstance()->getConnection();
            $invoiceRef = $_SESSION['nequi_checkout_ref'] ?? '';
            $saleId = $_SESSION['nequi_sale_id'] ?? 0;

            if ($saleId) {
                $stmt = $db->prepare("UPDATE sales SET status = ?, epayco_ref = ? WHERE id = ?");
                $stmt->execute([$status, $refPayco, $saleId]);
            } elseif ($invoiceRef) {
                $stmt = $db->prepare("UPDATE sales SET status = ?, epayco_ref = ? WHERE invoice_number = ?");
                $stmt->execute([$status, $refPayco, $invoiceRef]);
            }
        } catch (\Exception $e) {
            error_log("Error actualizando estado de venta Nequi: " . $e->getMessage());
        }
    }

    /**
     * Limpia los items seleccionados del carrito
     */
    private function clearSelectedCartItems() {
        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $id => $item) {
                if ($item['selected']) {
                    unset($_SESSION['cart'][$id]);
                }
            }
        }
        // Limpiar datos de sesion de nequi
        unset(
            $_SESSION['nequi_checkout_ref'],
            $_SESSION['nequi_checkout_total'],
            $_SESSION['nequi_checkout_items'],
            $_SESSION['nequi_checkout_subtotal'],
            $_SESSION['nequi_checkout_iva'],
            $_SESSION['nequi_ref_payco'],
            $_SESSION['nequi_transaction_id'],
            $_SESSION['nequi_sale_id']
        );
    }
}
