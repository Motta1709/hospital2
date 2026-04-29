<?php
/**
 * PharmaCRM - Modelo de Producto
 */
class Product {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($page = 1, $perPage = ITEMS_PER_PAGE, $search = '', $category = null) {
        $offset = ($page - 1) * $perPage;
        $where = "WHERE p.is_active = 1";
        $params = [];

        if ($search) {
            $where .= " AND (p.name LIKE ? OR p.generic_name LIKE ? OR p.barcode LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        if ($category) {
            $where .= " AND p.category_id = ?";
            $params[] = $category;
        }

        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name, c.color as category_color, s.name as supplier_name,
                   DATEDIFF(p.expiration_date, CURDATE()) as days_to_expire
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN suppliers s ON p.supplier_id = s.id
            {$where}
            ORDER BY p.expiration_date ASC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function count($search = '', $category = null) {
        $where = "WHERE is_active = 1";
        $params = [];

        if ($search) {
            $where .= " AND (name LIKE ? OR generic_name LIKE ? OR barcode LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        if ($category) {
            $where .= " AND category_id = ?";
            $params[] = $category;
        }

        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM products {$where}");
        $stmt->execute($params);
        return $stmt->fetch()['total'];
    }

    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name, s.name as supplier_name,
                   DATEDIFF(p.expiration_date, CURDATE()) as days_to_expire
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN suppliers s ON p.supplier_id = s.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByBarcode($barcode) {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name, 
                   DATEDIFF(p.expiration_date, CURDATE()) as days_to_expire
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.barcode = ? AND p.is_active = 1
        ");
        $stmt->execute([$barcode]);
        return $stmt->fetch();
    }

    public function search($query) {
        $stmt = $this->db->prepare("
            SELECT p.id, p.name, p.generic_name, p.barcode, p.sale_price, p.stock, 
                   p.presentation, p.concentration, p.requires_prescription,
                   c.name as category_name, c.color as category_color,
                   DATEDIFF(p.expiration_date, CURDATE()) as days_to_expire
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.is_active = 1 AND p.stock > 0
              AND (p.name LIKE ? OR p.generic_name LIKE ? OR p.barcode LIKE ?)
            ORDER BY p.name
            LIMIT 20
        ");
        $term = "%{$query}%";
        $stmt->execute([$term, $term, $term]);
        return $stmt->fetchAll();
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO products (category_id, supplier_id, barcode, name, generic_name, description,
                presentation, concentration, lot_number, expiration_date, purchase_price, sale_price,
                stock, min_stock, max_stock, requires_prescription)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['category_id'], $data['supplier_id'] ?: null, $data['barcode'],
            $data['name'], $data['generic_name'] ?? '', $data['description'] ?? '',
            $data['presentation'] ?? '', $data['concentration'] ?? '', $data['lot_number'] ?? '',
            $data['expiration_date'], $data['purchase_price'], $data['sale_price'],
            $data['stock'], $data['min_stock'] ?? 10, $data['max_stock'] ?? 500,
            $data['requires_prescription'] ?? 0
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE products SET 
                category_id = ?, supplier_id = ?, barcode = ?, name = ?, generic_name = ?,
                description = ?, presentation = ?, concentration = ?, lot_number = ?,
                expiration_date = ?, purchase_price = ?, sale_price = ?, stock = ?,
                min_stock = ?, max_stock = ?, requires_prescription = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['category_id'], $data['supplier_id'] ?: null, $data['barcode'],
            $data['name'], $data['generic_name'] ?? '', $data['description'] ?? '',
            $data['presentation'] ?? '', $data['concentration'] ?? '', $data['lot_number'] ?? '',
            $data['expiration_date'], $data['purchase_price'], $data['sale_price'],
            $data['stock'], $data['min_stock'] ?? 10, $data['max_stock'] ?? 500,
            $data['requires_prescription'] ?? 0, $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("UPDATE products SET is_active = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function updateStock($id, $quantity) {
        $stmt = $this->db->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
        return $stmt->execute([$quantity, $id, $quantity]);
    }

    // Alertas de vencimiento
    public function getExpiringProducts($days = 30) {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name, c.color as category_color,
                   DATEDIFF(p.expiration_date, CURDATE()) as days_to_expire
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.is_active = 1 AND p.stock > 0
              AND DATEDIFF(p.expiration_date, CURDATE()) <= ?
              AND DATEDIFF(p.expiration_date, CURDATE()) >= 0
            ORDER BY p.expiration_date ASC
        ");
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }

    public function getExpiredProducts() {
        $stmt = $this->db->query("
            SELECT p.*, c.name as category_name, c.color as category_color,
                   DATEDIFF(p.expiration_date, CURDATE()) as days_to_expire
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.is_active = 1 AND p.stock > 0
              AND p.expiration_date < CURDATE()
            ORDER BY p.expiration_date ASC
        ");
        return $stmt->fetchAll();
    }

    public function getLowStockProducts() {
        $stmt = $this->db->query("
            SELECT p.*, c.name as category_name, c.color as category_color,
                   DATEDIFF(p.expiration_date, CURDATE()) as days_to_expire
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.is_active = 1 AND p.stock <= p.min_stock AND p.stock > 0
            ORDER BY p.stock ASC
        ");
        return $stmt->fetchAll();
    }

    public function getOutOfStockProducts() {
        $stmt = $this->db->query("
            SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.is_active = 1 AND p.stock = 0
            ORDER BY p.name
        ");
        return $stmt->fetchAll();
    }

    public function getTotalProducts() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM products WHERE is_active = 1");
        return $stmt->fetch()['total'];
    }

    public function getTotalStockValue() {
        $stmt = $this->db->query("SELECT SUM(stock * sale_price) as total FROM products WHERE is_active = 1");
        return $stmt->fetch()['total'] ?? 0;
    }

    public function getCategories() {
        $stmt = $this->db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");
        return $stmt->fetchAll();
    }

    public function getSuppliers() {
        $stmt = $this->db->query("SELECT * FROM suppliers WHERE is_active = 1 ORDER BY name");
        return $stmt->fetchAll();
    }

    public function getTopProducts($limit = 5) {
        $stmt = $this->db->prepare("
            SELECT p.name, p.sale_price, SUM(si.quantity) as total_sold, 
                   SUM(si.subtotal) as total_revenue
            FROM sale_items si
            JOIN products p ON si.product_id = p.id
            JOIN sales s ON si.sale_id = s.id
            WHERE s.status = 'completed'
            GROUP BY p.id
            ORDER BY total_sold DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getCategoryDistribution() {
        $stmt = $this->db->query("
            SELECT c.name, c.color, COUNT(p.id) as product_count, SUM(p.stock) as total_stock
            FROM categories c
            LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1
            WHERE c.is_active = 1
            GROUP BY c.id
            ORDER BY product_count DESC
        ");
        return $stmt->fetchAll();
    }
}
