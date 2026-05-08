<?php
/**
 * PharmaCRM - Modelo de Compras del Cliente (Modulo Usuario)
 * RF-03: Visualizacion del historial de pedidos
 * 
 * Vista de compras desde la perspectiva del cliente.
 */
class CustomerOrder {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene el historial de compras del cliente con paginacion
     * @param int $clientId
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getByClient($clientId, $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        $stmt = $this->db->prepare("
            SELECT s.id, s.invoice_number, s.subtotal, s.discount_amount, 
                   s.total, s.payment_method, s.status, s.created_at,
                   s.loyalty_points_earned,
                   (SELECT COUNT(*) FROM items_venta WHERE venta_id = s.id) as total_items
            FROM ventas s
            WHERE s.cliente_id = ? AND s.status != 'cancelled'
            ORDER BY s.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute([$clientId]);
        return $stmt->fetchAll();
    }

    /**
     * Cuenta total de compras del cliente
     * @param int $clientId
     * @return int
     */
    public function countByClient($clientId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM ventas WHERE cliente_id = ? AND status != 'cancelled'");
        $stmt->execute([$clientId]);
        return $stmt->fetch()['total'];
    }

    /**
     * Obtiene el detalle de una compra especifica del cliente
     * @param int $saleId
     * @param int $clientId
     * @return array|false
     */
    public function getDetail($saleId, $clientId) {
        $stmt = $this->db->prepare("
            SELECT s.*, u.full_name as cashier_name
            FROM ventas s
            JOIN usuarios u ON s.usuario_id = u.id
            WHERE s.id = ? AND s.cliente_id = ?
        ");
        $stmt->execute([$saleId, $clientId]);
        return $stmt->fetch();
    }

    /**
     * Obtiene los items de una compra
     * @param int $saleId
     * @return array
     */
    public function getItems($saleId) {
        $stmt = $this->db->prepare("
            SELECT si.*, p.name as product_name, p.presentation, 
                   p.concentration, p.generic_name
            FROM items_venta si
            JOIN productos p ON si.product_id = p.id
            WHERE si.venta_id = ?
            ORDER BY si.id
        ");
        $stmt->execute([$saleId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene las compras elegibles para devolucion (estado completado, < 30 dias)
     * @param int $clientId
     * @return array
     */
    public function getEligibleForReturn($clientId) {
        $stmt = $this->db->prepare("
            SELECT s.id, s.invoice_number, s.total, s.created_at,
                   (SELECT COUNT(*) FROM items_venta WHERE venta_id = s.id) as total_items
            FROM ventas s
            WHERE s.cliente_id = ? 
              AND s.status = 'completed'
              AND s.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ORDER BY s.created_at DESC
        ");
        $stmt->execute([$clientId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene el gasto mensual del cliente para grafico
     * @param int $clientId
     * @param int $months
     * @return array
     */
    public function getMonthlySpending($clientId, $months = 6) {
        $stmt = $this->db->prepare("
            SELECT DATE_FORMAT(created_at, '%Y-%m') as mes,
                   COUNT(*) as total_compras,
                   COALESCE(SUM(total), 0) as total_gastado
            FROM ventas
            WHERE cliente_id = ? AND status = 'completed'
              AND created_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY mes ASC
        ");
        $stmt->execute([$clientId, $months]);
        return $stmt->fetchAll();
    }
}
