<?php
namespace App\Repositories;

use App\Interfaces\ProductRepositoryInterface;
use Database;

class ProductRepository implements ProductRepositoryInterface {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findById(int $id, ?int $branchId = null) {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name 
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.id = ? AND p.branch_id = ?
        ");
        $stmt->execute([$id, $branchId]);
        return $stmt->fetch();
    }

    public function all(?int $branchId = null) {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;
        $stmt = $this->db->prepare("SELECT * FROM products WHERE branch_id = ? AND is_active = 1");
        $stmt->execute([$branchId]);
        return $stmt->fetchAll();
    }

    public function search(string $query, ?int $branchId = null) {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.branch_id = ? AND p.is_active = 1
            AND (p.name LIKE ? OR p.generic_name LIKE ? OR p.barcode LIKE ?)
        ");
        $term = "%$query%";
        $stmt->execute([$branchId, $term, $term, $term]);
        return $stmt->fetchAll();
    }

    public function getByCategory(int $categoryId, ?int $branchId = null) {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;
        $stmt = $this->db->prepare("SELECT * FROM products WHERE category_id = ? AND branch_id = ? AND is_active = 1");
        $stmt->execute([$categoryId, $branchId]);
        return $stmt->fetchAll();
    }

    public function updateStock(int $id, int $quantity, ?int $branchId = null): bool {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;
        $stmt = $this->db->prepare("UPDATE products SET stock = stock + ? WHERE id = ? AND branch_id = ?");
        return $stmt->execute([$quantity, $id, $branchId]);
    }

    public function getExpiring(int $days, ?int $branchId = null) {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;
        $stmt = $this->db->prepare("
            SELECT *, DATEDIFF(expiration_date, CURDATE()) as days_to_expire
            FROM products
            WHERE branch_id = ? AND is_active = 1
            AND DATEDIFF(expiration_date, CURDATE()) <= ?
            ORDER BY expiration_date ASC
        ");
        $stmt->execute([$branchId, $days]);
        return $stmt->fetchAll();
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("UPDATE products SET is_active = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
