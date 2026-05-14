<?php
/**
 * PharmaCRM - Modelo de Producto (Actualizado V2)
 */
class Product {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($page = 1, $perPage = ITEMS_PER_PAGE, $search = '', $category = null, $branchId = null) {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;
        $offset = ($page - 1) * $perPage;
        $where = "WHERE p.is_active = 1 AND p.branch_id = ?";
        $params = [$branchId];

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
            SELECT p.*, 
                   c.name as category_name, c.color as category_color,
                   cl.name as classification_name,
                   sc.name as subclassification_name,
                   ps.purchase_price as primary_purchase_price,
                   s.name as primary_supplier_name,
                   DATEDIFF(p.expiration_date, CURDATE()) as days_to_expire
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN classifications cl ON p.classification_id = cl.id
            LEFT JOIN subclassifications sc ON p.subclassification_id = sc.id
            LEFT JOIN product_suppliers ps ON p.id = ps.product_id AND ps.is_primary = 1
            LEFT JOIN suppliers s ON ps.supplier_id = s.id
            {$where}
            ORDER BY p.expiration_date ASC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function count($search = '', $category = null, $branchId = null) {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;
        $where = "WHERE is_active = 1 AND branch_id = ?";
        $params = [$branchId];

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

    public function findById($id, $branchId = null) {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;
        // Obtener datos básicos del producto
        $stmt = $this->db->prepare("
            SELECT p.*, 
                   c.name as category_name,
                   cl.name as classification_name,
                   sc.name as subclassification_name,
                   DATEDIFF(p.expiration_date, CURDATE()) as days_to_expire
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN classifications cl ON p.classification_id = cl.id
            LEFT JOIN subclassifications sc ON p.subclassification_id = sc.id
            WHERE p.id = ? AND p.branch_id = ?
        ");
        $stmt->execute([$id, $branchId]);
        $product = $stmt->fetch();

        if ($product) {
            // Obtener proveedores y precios
            $product['suppliers'] = $this->getProductSuppliers($id);
        }

        return $product;
    }

    public function getProductSuppliers($productId) {
        $stmt = $this->db->prepare("
            SELECT s.*, ps.purchase_price, ps.is_primary
            FROM product_suppliers ps
            JOIN suppliers s ON ps.supplier_id = s.id
            WHERE ps.product_id = ?
            ORDER BY ps.is_primary DESC, s.name ASC
        ");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public function findByBarcode($barcode, $branchId = null) {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name, 
                   DATEDIFF(p.expiration_date, CURDATE()) as days_to_expire
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.barcode = ? AND p.branch_id = ? AND p.is_active = 1
        ");
        $stmt->execute([$barcode, $branchId]);
        return $stmt->fetch();
    }

    public function search($query, $branchId = null) {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;
        $stmt = $this->db->prepare("
            SELECT p.id, p.name, p.generic_name, p.barcode, p.sale_price, p.stock, p.lot_number, p.expiration_date,
                   c.name as category_name, DATEDIFF(p.expiration_date, CURDATE()) as days_to_expire
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.is_active = 1 AND p.branch_id = ?
              AND (p.name LIKE ? OR p.generic_name LIKE ? OR p.barcode LIKE ?)
            ORDER BY p.name ASC
            LIMIT 25
        ");
        $term = "%{$query}%";
        $stmt->execute([$branchId, $term, $term, $term]);
        return $stmt->fetchAll();
    }

    public function create($data) {
        $branchId = $_SESSION['branch_id'] ?? 1;
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO products (
                    branch_id, category_id, classification_id, subclassification_id, barcode, name, generic_name, 
                    description, usage_instructions, recommendations, presentation, concentration, 
                    unit_of_measure, quantity_per_unit, lot_number, expiration_date, sale_price, 
                    stock, min_stock, max_stock, requires_prescription
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $branchId,
                $data['category_id'], $data['classification_id'] ?? null, $data['subclassification_id'] ?? null,
                $data['barcode'], $data['name'], $data['generic_name'] ?? '', 
                $data['description'] ?? '', $data['usage_instructions'] ?? '', $data['recommendations'] ?? '',
                $data['presentation'] ?? '', $data['concentration'] ?? '', 
                $data['unit_of_measure'] ?? '', $data['quantity_per_unit'] ?? null,
                $data['lot_number'] ?? '', $data['expiration_date'], $data['sale_price'], 
                $data['stock'], $data['min_stock'] ?? 10, $data['max_stock'] ?? 500,
                $data['requires_prescription'] ?? 0
            ]);
            
            $productId = $this->db->lastInsertId();

            // Insertar lote inicial ligado a la sucursal
            $kardex = new Kardex();
            $kardex->processEntry($productId, [
                'lot_number' => $data['lot_number'] ?? 'INICIAL',
                'expiration_date' => $data['expiration_date'],
                'purchase_price' => $data['purchase_price'] ?? 0,
                'quantity' => $data['stock']
            ], $productId, 'INITIAL_STOCK', $branchId);

            // Insertar proveedores si se proporcionan
            if (!empty($data['suppliers'])) {
                $this->updateProductSuppliers($productId, $data['suppliers']);
            }

            $this->db->commit();
            return $productId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update($id, $data) {
        $branchId = $_SESSION['branch_id'] ?? 1;
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                UPDATE products SET 
                    category_id = ?, classification_id = ?, subclassification_id = ?, 
                    barcode = ?, name = ?, generic_name = ?, description = ?, 
                    usage_instructions = ?, recommendations = ?, presentation = ?, 
                    concentration = ?, unit_of_measure = ?, quantity_per_unit = ?,
                    lot_number = ?, expiration_date = ?, sale_price = ?, 
                    stock = ?, min_stock = ?, max_stock = ?, requires_prescription = ?
                WHERE id = ? AND branch_id = ?
            ");
            
            $stmt->execute([
                $data['category_id'], $data['classification_id'] ?? null, $data['subclassification_id'] ?? null,
                $data['barcode'], $data['name'], $data['generic_name'] ?? '', 
                $data['description'] ?? '', $data['usage_instructions'] ?? '', $data['recommendations'] ?? '',
                $data['presentation'] ?? '', $data['concentration'] ?? '', 
                $data['unit_of_measure'] ?? '', $data['quantity_per_unit'] ?? null,
                $data['lot_number'] ?? '', $data['expiration_date'], $data['sale_price'], 
                $data['stock'], $data['min_stock'] ?? 10, $data['max_stock'] ?? 500,
                $data['requires_prescription'] ?? 0, $id, $branchId
            ]);

            // Actualizar proveedores si se proporcionan
            if (isset($data['suppliers'])) {
                $this->updateProductSuppliers($id, $data['suppliers']);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function updateProductSuppliers($productId, $suppliers) {
        // Eliminar proveedores actuales
        $stmt = $this->db->prepare("DELETE FROM product_suppliers WHERE product_id = ?");
        $stmt->execute([$productId]);

        // Insertar nuevos proveedores
        $stmt = $this->db->prepare("
            INSERT INTO product_suppliers (product_id, supplier_id, purchase_price, is_primary)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($suppliers as $supplier) {
            $stmt->execute([
                $productId, 
                $supplier['supplier_id'], 
                $supplier['purchase_price'], 
                $supplier['is_primary'] ?? 0
            ]);
        }
    }

    public function delete($id) {
        $branchId = $_SESSION['branch_id'] ?? 1;
        $stmt = $this->db->prepare("UPDATE products SET is_active = 0 WHERE id = ? AND branch_id = ?");
        return $stmt->execute([$id, $branchId]);
    }

    public function updateStock($id, $quantity, $branchId = null) {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;
        $stmt = $this->db->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND branch_id = ? AND stock >= ?");
        return $stmt->execute([$quantity, $id, $branchId, $quantity]);
    }

    // Métodos de alertas y estadísticas permanecen similares, pero pueden ajustarse para incluir nuevos campos si es necesario
    public function getExpiringProducts($days = 30, $branchId = null) {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name, c.color as category_color,
                   DATEDIFF(p.expiration_date, CURDATE()) as days_to_expire
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.is_active = 1 AND p.stock > 0 AND p.branch_id = ?
              AND DATEDIFF(p.expiration_date, CURDATE()) <= ?
              AND DATEDIFF(p.expiration_date, CURDATE()) >= 0
            ORDER BY p.expiration_date ASC
        ");
        $stmt->execute([$branchId, $days]);
        return $stmt->fetchAll();
    }

    public function getExpiredProducts($branchId = null) {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name, c.color as category_color,
                   DATEDIFF(p.expiration_date, CURDATE()) as days_to_expire
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.is_active = 1 AND p.stock > 0 AND p.branch_id = ?
              AND p.expiration_date < CURDATE()
            ORDER BY p.expiration_date ASC
        ");
        $stmt->execute([$branchId]);
        return $stmt->fetchAll();
    }

    public function getLowStockProducts($branchId = null) {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name, c.color as category_color,
                   DATEDIFF(p.expiration_date, CURDATE()) as days_to_expire
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.is_active = 1 AND p.stock <= p.min_stock AND p.stock > 0 AND p.branch_id = ?
            ORDER BY p.stock ASC
        ");
        $stmt->execute([$branchId]);
        return $stmt->fetchAll();
    }

    public function getOutOfStockProducts($branchId = null) {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.is_active = 1 AND p.stock = 0 AND p.branch_id = ?
            ORDER BY p.name
        ");
        $stmt->execute([$branchId]);
        return $stmt->fetchAll();
    }

    public function getTotalProducts($branchId = null) {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM products WHERE is_active = 1 AND branch_id = ?");
        $stmt->execute([$branchId]);
        return $stmt->fetch()['total'];
    }

    public function getTotalStockValue($branchId = null) {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;
        $stmt = $this->db->prepare("SELECT SUM(stock * sale_price) as total FROM products WHERE is_active = 1 AND branch_id = ?");
        $stmt->execute([$branchId]);
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

    public function getClassifications($categoryId = null) {
        $query = "SELECT * FROM classifications";
        $params = [];
        if ($categoryId) {
            $query .= " WHERE category_id = ?";
            $params[] = $categoryId;
        }
        $query .= " ORDER BY name";
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getSubclassifications($classificationId = null) {
        $query = "SELECT * FROM subclassifications";
        $params = [];
        if ($classificationId) {
            $query .= " WHERE classification_id = ?";
            $params[] = $classificationId;
        }
        $query .= " ORDER BY name";
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Calcula el estado semaforizado del producto
     */
    public function getTrafficLightStatus($product) {
        $status = [
            'stock' => ['color' => 'success', 'label' => 'Óptimo'],
            'expiration' => ['color' => 'success', 'label' => 'Vigente']
        ];

        // 1. Semaforización por Stock
        if ($product['stock'] <= 0) {
            $status['stock'] = ['color' => 'danger', 'label' => 'Agotado'];
        } elseif ($product['stock'] <= $product['min_stock']) {
            $status['stock'] = ['color' => 'danger', 'label' => 'Crítico'];
        } elseif ($product['stock'] <= ($product['min_stock'] * 2)) {
            $status['stock'] = ['color' => 'warning', 'label' => 'Bajo'];
        }

        // 2. Semaforización por Vencimiento
        if ($product['days_to_expire'] <= 0) {
            $status['expiration'] = ['color' => 'danger', 'label' => 'Vencido'];
        } elseif ($product['days_to_expire'] <= ALERT_EXPIRY_CRITICAL) {
            $status['expiration'] = ['color' => 'danger', 'label' => 'Crítico (90d)'];
        } elseif ($product['days_to_expire'] <= ALERT_EXPIRY_WARNING) {
            $status['expiration'] = ['color' => 'orange', 'label' => 'Alerta (180d)'];
        } elseif ($product['days_to_expire'] <= ALERT_EXPIRY_INFO) {
            $status['expiration'] = ['color' => 'yellow', 'label' => 'Próximo (270d)'];
        } else {
            $status['expiration'] = ['color' => 'success', 'label' => 'Vigente'];
        }

        return $status;
    }

    public function getTopProducts($limit = 5, $branchId = null) {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;
        $stmt = $this->db->prepare("
            SELECT p.id, p.name, p.generic_name, p.sale_price, p.stock,
                   SUM(si.quantity) as total_sold,
                   SUM(si.subtotal) as total_revenue
            FROM sale_items si
            JOIN products p ON si.product_id = p.id
            JOIN sales s ON si.sale_id = s.id
            WHERE s.status = 'completed' AND s.branch_id = ?
            GROUP BY p.id
            ORDER BY total_sold DESC
            LIMIT ?
        ");
        $stmt->execute([$branchId, (int)$limit]);
        return $stmt->fetchAll();
    }

    public function getCategoryDistribution($branchId = null) {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;
        $stmt = $this->db->prepare("
            SELECT c.name as category, COUNT(p.id) as count
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE p.is_active = 1 AND p.branch_id = ?
            GROUP BY c.id
        ");
        $stmt->execute([$branchId]);
        return $stmt->fetchAll();
    }
}

