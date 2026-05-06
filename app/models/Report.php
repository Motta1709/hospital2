<?php
/**
 * PharmaCRM - Modelo de Reportes
 */
class Report {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getSalesSummary($dateFrom, $dateTo) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total_sales, COALESCE(SUM(total),0) as total_revenue,
                   COALESCE(AVG(total),0) as avg_sale, COALESCE(SUM(discount_amount),0) as total_discounts
            FROM sales WHERE status='completed' AND DATE(created_at) BETWEEN ? AND ?
        ");
        $stmt->execute([$dateFrom, $dateTo]);
        return $stmt->fetch();
    }

    public function getSalesByDay($dateFrom, $dateTo) {
        $stmt = $this->db->prepare("
            SELECT DATE(created_at) as date, COUNT(*) as count, SUM(total) as total
            FROM sales WHERE status='completed' AND DATE(created_at) BETWEEN ? AND ?
            GROUP BY DATE(created_at) ORDER BY date
        ");
        $stmt->execute([$dateFrom, $dateTo]);
        return $stmt->fetchAll();
    }

    public function getSalesByCategory($dateFrom, $dateTo) {
        $stmt = $this->db->prepare("
            SELECT c.name, c.color, COUNT(DISTINCT s.id) as sales_count, SUM(si.subtotal) as total
            FROM sale_items si
            JOIN sales s ON si.sale_id = s.id
            JOIN products p ON si.product_id = p.id
            JOIN categories c ON p.category_id = c.id
            WHERE s.status='completed' AND DATE(s.created_at) BETWEEN ? AND ?
            GROUP BY c.id ORDER BY total DESC
        ");
        $stmt->execute([$dateFrom, $dateTo]);
        return $stmt->fetchAll();
    }

    public function getInventoryValue() {
        $stmt = $this->db->query("
            SELECT c.name, c.color, COUNT(p.id) as products, SUM(p.stock) as total_stock,
                   SUM(p.stock * p.purchase_price) as cost_value,
                   SUM(p.stock * p.sale_price) as sale_value
            FROM products p JOIN categories c ON p.category_id = c.id
            WHERE p.is_active = 1 GROUP BY c.id ORDER BY sale_value DESC
        ");
        return $stmt->fetchAll();
    }

    public function getExpirationReport() {
        $stmt = $this->db->query("
            SELECT 
                SUM(CASE WHEN DATEDIFF(expiration_date, CURDATE()) < 0 THEN 1 ELSE 0 END) as expired,
                SUM(CASE WHEN DATEDIFF(expiration_date, CURDATE()) BETWEEN 0 AND 7 THEN 1 ELSE 0 END) as exp_7,
                SUM(CASE WHEN DATEDIFF(expiration_date, CURDATE()) BETWEEN 8 AND 15 THEN 1 ELSE 0 END) as exp_15,
                SUM(CASE WHEN DATEDIFF(expiration_date, CURDATE()) BETWEEN 16 AND 30 THEN 1 ELSE 0 END) as exp_30
            FROM products WHERE is_active = 1 AND stock > 0
        ");
        return $stmt->fetch();
    }
}

