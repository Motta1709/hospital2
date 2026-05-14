<?php
namespace App\Repositories;

use App\Interfaces\SaleRepositoryInterface;
use Database;
use Exception;

class SaleRepository implements SaleRepositoryInterface {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findById(int $id) {
        $stmt = $this->db->prepare("
            SELECT s.*, u.full_name as cashier_name, CONCAT(c.first_name, ' ', c.last_name) as client_name
            FROM sales s
            LEFT JOIN users u ON s.user_id = u.id
            LEFT JOIN clients c ON s.client_id = c.id
            WHERE s.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByInvoice(string $invoiceNumber) {
        $stmt = $this->db->prepare("
            SELECT s.*, u.full_name as cashier_name, CONCAT(c.first_name, ' ', c.last_name) as client_name
            FROM sales s
            LEFT JOIN users u ON s.user_id = u.id
            LEFT JOIN clients c ON s.client_id = c.id
            WHERE s.invoice_number = ?
        ");
        $stmt->execute([$invoiceNumber]);
        return $stmt->fetch();
    }

    public function all(array $filters = []) {
        $sql = "
            SELECT s.*, u.full_name as cashier_name, CONCAT(c.first_name, ' ', c.last_name) as client_name
            FROM sales s
            LEFT JOIN users u ON s.user_id = u.id
            LEFT JOIN clients c ON s.client_id = c.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['date_from'])) {
            $sql .= " AND s.created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND s.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        if (!empty($filters['status'])) {
            $sql .= " AND s.status = ?";
            $params[] = $filters['status'];
        }

        $sql .= " ORDER BY s.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getItems(int $saleId): array {
        $stmt = $this->db->prepare("
            SELECT si.*, p.name as product_name 
            FROM sale_items si
            JOIN products p ON si.product_id = p.id
            WHERE si.sale_id = ?
        ");
        $stmt->execute([$saleId]);
        return $stmt->fetchAll();
    }

    public function create(array $data, array $items): int {
        $this->db->beginTransaction();
        try {
            $sql = "INSERT INTO sales (client_id, user_id, branch_id, invoice_number, subtotal, discount_amount, total, payment_method, cash_received, change_amount, loyalty_points_earned, status, notes) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['client_id'] ?? null,
                $data['user_id'],
                $data['branch_id'] ?? 1,
                $data['invoice_number'],
                $data['subtotal'],
                $data['discount_amount'] ?? 0,
                $data['total'],
                $data['payment_method'] ?? 'cash',
                $data['cash_received'] ?? $data['total'],
                $data['change_amount'] ?? 0,
                $data['loyalty_points_earned'] ?? 0,
                $data['status'] ?? 'completed',
                $data['notes'] ?? null
            ]);
            
            $saleId = $this->db->lastInsertId();

            // Insert items
            $stmtItem = $this->db->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
            foreach ($items as $item) {
                $stmtItem->execute([
                    $saleId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['unit_price'],
                    $item['subtotal']
                ]);
            }

            $this->db->commit();
            return $saleId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("UPDATE sales SET status = 'cancelled' WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
