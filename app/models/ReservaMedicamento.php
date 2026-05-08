<?php
/**
 * PharmaCRM - Modelo de Reserva (Modulo Usuario)
 * RF-06: Reservas de medicamentos proximos a llegar
 * RNF-07: Validacion de prescripcion medica en backend
 */
class ReservaMedicamento {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByClient($clientId, $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        $stmt = $this->db->prepare("
            SELECT r.*, p.name as product_name, p.generic_name, p.presentation,
                   p.concentration, p.requires_prescription, p.sale_price,
                   u.full_name as aprobado_por_nombre,
                   (SELECT COUNT(*) FROM prescripciones WHERE reserva_id = r.id) as tiene_prescripcion
            FROM reservas r
            JOIN products p ON r.product_id = p.id
            LEFT JOIN users u ON r.aprobado_por = u.id
            WHERE r.client_id = ?
            ORDER BY r.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute([$clientId]);
        return $stmt->fetchAll();
    }

    public function countByClient($clientId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM reservas WHERE client_id = ?");
        $stmt->execute([$clientId]);
        return $stmt->fetch()['total'];
    }

    public function create($clientId, $data) {
        // RNF-07: Validacion de prescripcion en backend
        $product = $this->getProduct($data['product_id']);
        if (!$product) throw new Exception('Producto no encontrado.');
        if ($product['requires_prescription'] && empty($data['prescripcion_path'])) {
            throw new Exception('Este medicamento requiere prescripcion medica. Adjunte el documento.');
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO reservas (client_id, product_id, cantidad) VALUES (?, ?, ?)
            ");
            $stmt->execute([$clientId, $data['product_id'], $data['cantidad'] ?? 1]);
            $reservaId = $this->db->lastInsertId();

            if (!empty($data['prescripcion_path'])) {
                $stmt = $this->db->prepare("
                    INSERT INTO prescripciones (reserva_id, archivo_path, archivo_nombre) VALUES (?, ?, ?)
                ");
                $stmt->execute([$reservaId, $data['prescripcion_path'], $data['prescripcion_nombre'] ?? 'prescripcion.pdf']);
            }

            $this->db->commit();
            (new CustomerProfile())->logAudit($clientId, 'reserva_creada', 'reservas', null, $reservaId);
            return $reservaId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function getProduct($productId) {
        $stmt = $this->db->prepare("SELECT id, name, requires_prescription, sale_price FROM products WHERE id = ? AND is_active = 1");
        $stmt->execute([$productId]);
        return $stmt->fetch();
    }

    public function getAvailableProducts() {
        $stmt = $this->db->prepare("
            SELECT id, name, generic_name, presentation, concentration, 
                   sale_price, stock, requires_prescription
            FROM products WHERE is_active = 1 AND stock <= min_stock
            ORDER BY name LIMIT 50
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
