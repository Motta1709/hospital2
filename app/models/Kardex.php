<?php
/**
 * PharmaCRM - Modelo de Kardex e Inventario FIFO/PEPS
 */
class Kardex {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Registra un movimiento en el Kardex y actualiza los lotes si es necesario
     */
    public function recordMovement($data) {
        $stmt = $this->db->prepare("
            INSERT INTO inventory_movements (
                product_id, branch_id, batch_id, user_id, type, quantity, 
                balance_after, reference_type, reference_id, notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['product_id'],
            $data['branch_id'] ?? $_SESSION['branch_id'] ?? 1,
            $data['batch_id'] ?? null,
            $_SESSION['user_id'] ?? 1,
            $data['type'],
            $data['quantity'],
            $data['balance_after'],
            $data['reference_type'] ?? 'ADJUSTMENT',
            $data['reference_id'] ?? null,
            $data['notes'] ?? ''
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Obtiene los lotes disponibles para un producto ordenados por FIFO (vencimiento más próximo)
     */
    public function getAvailableBatches($productId, $branchId = null) {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;
        $stmt = $this->db->prepare("
            SELECT * FROM product_batches 
            WHERE product_id = ? AND branch_id = ? AND current_quantity > 0 AND is_active = 1
            ORDER BY expiration_date ASC, created_at ASC
        ");
        $stmt->execute([$productId, $branchId]);
        return $stmt->fetchAll();
    }

    /**
     * Procesa una salida de stock siguiendo el modelo FIFO/PEPS
     */
    public function processFIFOSale($productId, $quantityRequested, $referenceId = null, $referenceType = 'SALE', $branchId = null) {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;
        $batches = $this->getAvailableBatches($productId, $branchId);
        $remainingToDeduct = $quantityRequested;
        $processedBatches = [];

        foreach ($batches as $batch) {
            if ($remainingToDeduct <= 0) break;

            $deduction = min($remainingToDeduct, $batch['current_quantity']);
            
            // Actualizar lote
            $newQuantity = $batch['current_quantity'] - $deduction;
            $stmt = $this->db->prepare("UPDATE product_batches SET current_quantity = ? WHERE id = ?");
            $stmt->execute([$newQuantity, $batch['id']]);

            // Registrar en Kardex
            $currentTotalStock = $this->getProductTotalStock($productId, $branchId);
            $this->recordMovement([
                'product_id' => $productId,
                'branch_id' => $branchId,
                'batch_id' => $batch['id'],
                'type' => 'EXIT',
                'quantity' => $deduction,
                'balance_after' => $currentTotalStock - $deduction,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => "Salida FIFO Lote: {$batch['lot_number']}"
            ]);

            // Actualizar stock general en tabla productos (para esa sucursal específica)
            $stmt = $this->db->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND branch_id = ?");
            $stmt->execute([$deduction, $productId, $branchId]);

            $remainingToDeduct -= $deduction;
            $processedBatches[] = [
                'batch_id' => $batch['id'],
                'lot_number' => $batch['lot_number'],
                'quantity' => $deduction
            ];
        }

        if ($remainingToDeduct > 0) {
            throw new Exception("Stock insuficiente para completar la salida FIFO en esta sucursal. Faltan: $remainingToDeduct unidades.");
        }

        return $processedBatches;
    }

    /**
     * Registra una entrada de stock (Nueva compra o ajuste)
     */
    public function processEntry($productId, $batchData, $referenceId = null, $referenceType = 'PURCHASE', $branchId = null) {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;

        // 1. Crear lote ligado a la sucursal
        $stmt = $this->db->prepare("
            INSERT INTO product_batches (
                product_id, branch_id, lot_number, expiration_date, purchase_price, initial_quantity, current_quantity
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $productId,
            $branchId,
            $batchData['lot_number'],
            $batchData['expiration_date'],
            $batchData['purchase_price'] ?? 0,
            $batchData['quantity'],
            $batchData['quantity']
        ]);
        $batchId = $this->db->lastInsertId();

        // 2. Actualizar stock general de la sucursal
        $stmt = $this->db->prepare("UPDATE products SET stock = stock + ? WHERE id = ? AND branch_id = ?");
        $stmt->execute([$batchData['quantity'], $productId, $branchId]);

        // 3. Registrar en Kardex
        $newTotalStock = $this->getProductTotalStock($productId, $branchId);
        $this->recordMovement([
            'product_id' => $productId,
            'branch_id' => $branchId,
            'batch_id' => $batchId,
            'type' => 'ENTRY',
            'quantity' => $batchData['quantity'],
            'balance_after' => $newTotalStock,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => "Entrada de stock Lote: {$batchData['lot_number']}"
        ]);

        return $batchId;
    }

    private function getProductTotalStock($productId, $branchId = null) {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;
        $stmt = $this->db->prepare("SELECT stock FROM products WHERE id = ? AND branch_id = ?");
        $stmt->execute([$productId, $branchId]);
        return $stmt->fetch()['stock'] ?? 0;
    }

    /**
     * Obtiene el historial de movimientos de un producto filtrado por sucursal
     */
    public function getMovementHistory($productId = null, $branchId = null, $limit = 50, $filters = []) {
        $branchId = $branchId ?? $_SESSION['branch_id'] ?? 1;
        $query = "
            SELECT k.*, p.name as product_name, b.lot_number, u.username
            FROM inventory_movements k
            JOIN products p ON k.product_id = p.id
            LEFT JOIN product_batches b ON k.batch_id = b.id
            JOIN users u ON k.user_id = u.id
            WHERE k.branch_id = ?
        ";
        $params = [$branchId];
        
        if ($productId) {
            $query .= " AND k.product_id = ?";
            $params[] = $productId;
        }

        if (!empty($filters['date_from'])) {
            $query .= " AND k.created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $query .= " AND k.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        $query .= " ORDER BY k.created_at DESC LIMIT " . (int)$limit;
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

