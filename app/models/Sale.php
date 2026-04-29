<?php
/**
 * PharmaCRM - Modelo de Venta
 */
class Sale {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($page = 1, $perPage = 15, $dateFrom = null, $dateTo = null) {
        $offset = ($page - 1) * $perPage;
        $where = "WHERE 1=1";
        $params = [];
        if ($dateFrom) { $where .= " AND DATE(s.created_at) >= ?"; $params[] = $dateFrom; }
        if ($dateTo) { $where .= " AND DATE(s.created_at) <= ?"; $params[] = $dateTo; }

        $stmt = $this->db->prepare("
            SELECT s.*, CONCAT(c.first_name, ' ', c.last_name) as client_name,
                   c.document_number as client_document, u.full_name as cashier_name
            FROM sales s LEFT JOIN clients c ON s.client_id = c.id
            JOIN users u ON s.user_id = u.id {$where}
            ORDER BY s.created_at DESC LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function count($dateFrom = null, $dateTo = null) {
        $where = "WHERE 1=1"; $params = [];
        if ($dateFrom) { $where .= " AND DATE(created_at) >= ?"; $params[] = $dateFrom; }
        if ($dateTo) { $where .= " AND DATE(created_at) <= ?"; $params[] = $dateTo; }
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM sales {$where}");
        $stmt->execute($params);
        return $stmt->fetch()['total'];
    }

    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT s.*, CONCAT(c.first_name, ' ', c.last_name) as client_name,
                   c.document_number as client_document, c.phone as client_phone,
                   u.full_name as cashier_name
            FROM sales s LEFT JOIN clients c ON s.client_id = c.id
            JOIN users u ON s.user_id = u.id WHERE s.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getSaleItems($saleId) {
        $stmt = $this->db->prepare("
            SELECT si.*, p.name as product_name, p.barcode, p.presentation, p.concentration
            FROM sale_items si JOIN products p ON si.product_id = p.id WHERE si.sale_id = ?
        ");
        $stmt->execute([$saleId]);
        return $stmt->fetchAll();
    }

    public function create($saleData, $items) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO sales (client_id, user_id, invoice_number, subtotal, discount_amount,
                    tax_amount, total, payment_method, cash_received, change_amount,
                    loyalty_points_earned, loyalty_points_used, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $saleData['client_id'] ?: null, $saleData['user_id'], $saleData['invoice_number'],
                $saleData['subtotal'], $saleData['discount_amount'] ?? 0, $saleData['tax_amount'] ?? 0,
                $saleData['total'], $saleData['payment_method'] ?? 'cash',
                $saleData['cash_received'] ?? $saleData['total'], $saleData['change_amount'] ?? 0,
                $saleData['loyalty_points_earned'] ?? 0, $saleData['loyalty_points_used'] ?? 0,
                $saleData['notes'] ?? null
            ]);
            $saleId = $this->db->lastInsertId();

            $stmtItem = $this->db->prepare("
                INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, discount_percent, subtotal)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $productModel = new Product();
            foreach ($items as $item) {
                $stmtItem->execute([$saleId, $item['product_id'], $item['quantity'],
                    $item['unit_price'], $item['discount_percent'] ?? 0, $item['subtotal']]);
                $productModel->updateStock($item['product_id'], $item['quantity']);
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
        $this->db->beginTransaction();
        try {
            $items = $this->getSaleItems($id);
            foreach ($items as $item) {
                $stmt = $this->db->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }
            $stmt = $this->db->prepare("UPDATE sales SET status = 'cancelled' WHERE id = ?");
            $stmt->execute([$id]);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getTodaySales() {
        $stmt = $this->db->query("SELECT COUNT(*) as count, COALESCE(SUM(total),0) as total FROM sales WHERE DATE(created_at)=CURDATE() AND status='completed'");
        return $stmt->fetch();
    }

    public function getMonthSales() {
        $stmt = $this->db->query("SELECT COUNT(*) as count, COALESCE(SUM(total),0) as total FROM sales WHERE MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE()) AND status='completed'");
        return $stmt->fetch();
    }

    public function getDailySalesChart($days = 7) {
        $stmt = $this->db->prepare("SELECT DATE(created_at) as date, COUNT(*) as count, SUM(total) as total FROM sales WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY) AND status='completed' GROUP BY DATE(created_at) ORDER BY date");
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }

    public function getPaymentMethodStats() {
        $stmt = $this->db->query("SELECT payment_method, COUNT(*) as count, SUM(total) as total FROM sales WHERE status='completed' GROUP BY payment_method");
        return $stmt->fetchAll();
    }

    public function getRecentSales($limit = 5) {
        $stmt = $this->db->prepare("SELECT s.id, s.invoice_number, s.total, s.payment_method, s.created_at, CONCAT(c.first_name,' ',c.last_name) as client_name FROM sales s LEFT JOIN clients c ON s.client_id=c.id WHERE s.status='completed' ORDER BY s.created_at DESC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
