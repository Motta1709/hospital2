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
        $stmt = $this->db->prepare("SELECT * FROM sales WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByInvoice(string $invoiceNumber) {
        $stmt = $this->db->prepare("SELECT * FROM sales WHERE invoice_number = ?");
        $stmt->execute([$invoiceNumber]);
        return $stmt->fetch();
    }

    public function all() {
        $stmt = $this->db->query("SELECT * FROM sales ORDER BY created_at DESC");
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
