<?php
/**
 * PharmaCRM - Modelo de Venta
 */
class Sale {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($page = 1, $perPage = 15, $dateFrom = null, $dateTo = null, $branchId = null) {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;
        $offset = ($page - 1) * $perPage;
        $where = "WHERE s.branch_id = ?";
        $params = [$branchId];
        if ($dateFrom) { $where .= " AND DATE(s.created_at) >= ?"; $params[] = $dateFrom; }
        if ($dateTo) { $where .= " AND DATE(s.created_at) <= ?"; $params[] = $dateTo; }

        $stmt = $this->db->prepare("
            SELECT s.*, CONCAT(c.first_name, ' ', c.last_name) as client_name,
                   c.document_number as client_document, u.full_name as cashier_name
            FROM ventas s LEFT JOIN clientes c ON s.cliente_id = c.id
            JOIN usuarios u ON s.usuario_id = u.id {$where}
            ORDER BY s.created_at DESC LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function count($dateFrom = null, $dateTo = null, $branchId = null) {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;
        $where = "WHERE branch_id = ?"; $params = [$branchId];
        if ($dateFrom) { $where .= " AND DATE(created_at) >= ?"; $params[] = $dateFrom; }
        if ($dateTo) { $where .= " AND DATE(created_at) <= ?"; $params[] = $dateTo; }
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM ventas {$where}");
        $stmt->execute($params);
        return $stmt->fetch()['total'];
    }

    public function findById($id, $branchId = null) {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;
        $stmt = $this->db->prepare("
            SELECT s.*, CONCAT(c.first_name, ' ', c.last_name) as client_name,
                   c.document_number as client_document, c.phone as client_phone,
                   u.full_name as cashier_name
            FROM ventas s LEFT JOIN clientes c ON s.cliente_id = c.id
            JOIN usuarios u ON s.usuario_id = u.id WHERE s.id = ? AND s.branch_id = ?
        ");
        $stmt->execute([$id, $branchId]);
        return $stmt->fetch();
    }

    public function getSaleItems($saleId) {
        $stmt = $this->db->prepare("
            SELECT si.*, p.name as product_name, p.barcode, p.presentation, p.concentration
            FROM items_venta si JOIN productos p ON si.product_id = p.id WHERE si.venta_id = ?
        ");
        $stmt->execute([$saleId]);
        return $stmt->fetchAll();
    }

    public function create($saleData, $items) {
        $branchId = $_SESSION['branch_id'] ?? 1;
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO ventas (cliente_id, usuario_id, branch_id, invoice_number, subtotal, discount_amount,
                    tax_amount, total, payment_method, cash_received, change_amount,
                    loyalty_points_earned, loyalty_points_used, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $saleData['client_id'] ?: null, $saleData['user_id'], $branchId, $saleData['invoice_number'],
                $saleData['subtotal'], $saleData['discount_amount'] ?? 0, $saleData['tax_amount'] ?? 0,
                $saleData['total'], $saleData['payment_method'] ?? 'cash',
                $saleData['cash_received'] ?? $saleData['total'], $saleData['change_amount'] ?? 0,
                $saleData['loyalty_points_earned'] ?? 0, $saleData['loyalty_points_used'] ?? 0,
                $saleData['notes'] ?? null
            ]);
            $saleId = $this->db->lastInsertId();

            $stmtItem = $this->db->prepare("
                INSERT INTO items_venta (venta_id, product_id, quantity, unit_price, discount_percent, subtotal)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $kardexModel = new Kardex();
            foreach ($items as $item) {
                $stmtItem->execute([$saleId, $item['product_id'], $item['quantity'],
                    $item['unit_price'], $item['discount_percent'] ?? 0, $item['subtotal']]);
                
                // Procesar salida de stock mediante FIFO/PEPS en la sucursal actual
                $kardexModel->processFIFOSale($item['product_id'], $item['quantity'], $saleId, 'SALE', $branchId);
            }

            if ($saleData['client_id']) {
                $clientModel = new Client();
                $points = $clientModel->updatePurchaseStats($saleData['client_id'], $saleData['total']);
                $loyaltyModel = new LoyaltyProgram();
                $loyaltyModel->addTransaction($saleData['client_id'], $saleId, $points, 'earned',
                    'Compra ' . $saleData['invoice_number']);
            }

            $this->db->commit();
            return $saleId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function cancel($id) {
        $branchId = $_SESSION['branch_id'] ?? 1;
        $this->db->beginTransaction();
        try {
            $items = $this->getSaleItems($id);
            foreach ($items as $item) {
                $stmt = $this->db->prepare("UPDATE products SET stock = stock + ? WHERE id = ? AND branch_id = ?");
                $stmt->execute([$item['quantity'], $item['product_id'], $branchId]);
                
                // Nota: Para una reversión completa de Kardex deberíamos registrar el movimiento de entrada
                $kardex = new Kardex();
                $kardex->recordMovement([
                    'product_id' => $item['product_id'],
                    'branch_id' => $branchId,
                    'type' => 'ENTRY',
                    'quantity' => $item['quantity'],
                    'balance_after' => $this->db->query("SELECT stock FROM products WHERE id = " . $item['product_id'])->fetchColumn(),
                    'reference_type' => 'CANCELLED_SALE',
                    'reference_id' => $id,
                    'notes' => "Anulación de venta: " . $id
                ]);
            }
            $stmt = $this->db->prepare("UPDATE sales SET status = 'cancelled' WHERE id = ? AND branch_id = ?");
            $stmt->execute([$id, $branchId]);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getTodaySales($branchId = null) {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;
        $stmt = $this->db->prepare("SELECT COUNT(*) as count, COALESCE(SUM(total),0) as total FROM ventas WHERE DATE(created_at)=CURDATE() AND status='completed' AND branch_id = ?");
        $stmt->execute([$branchId]);
        return $stmt->fetch();
    }

    public function getMonthSales($branchId = null) {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;
        $stmt = $this->db->prepare("SELECT COUNT(*) as count, COALESCE(SUM(total),0) as total FROM ventas WHERE MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE()) AND status='completed' AND branch_id = ?");
        $stmt->execute([$branchId]);
        return $stmt->fetch();
    }

    public function getDailySalesChart($days = 7, $branchId = null) {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;
        $stmt = $this->db->prepare("SELECT DATE(created_at) as date, COUNT(*) as count, SUM(total) as total FROM ventas WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY) AND status='completed' AND branch_id = ? GROUP BY DATE(created_at) ORDER BY date");
        $stmt->execute([$days, $branchId]);
        return $stmt->fetchAll();
    }

    public function getPaymentMethodStats($branchId = null) {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;
        $stmt = $this->db->prepare("SELECT payment_method, COUNT(*) as count, SUM(total) as total FROM ventas WHERE status='completed' AND branch_id = ? GROUP BY payment_method");
        $stmt->execute([$branchId]);
        return $stmt->fetchAll();
    }

    public function getRecentSales($limit = 5, $branchId = null) {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;
        $stmt = $this->db->prepare("SELECT s.id, s.invoice_number, s.total, s.payment_method, s.created_at, CONCAT(c.first_name,' ',c.last_name) as client_name FROM ventas s LEFT JOIN clientes c ON s.client_id=c.id WHERE s.status='completed' AND s.branch_id = ? ORDER BY s.created_at DESC LIMIT ?");
        $stmt->execute([$branchId, $limit]);
        return $stmt->fetchAll();
    }
}

