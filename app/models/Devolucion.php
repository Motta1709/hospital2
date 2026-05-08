<?php
/**
 * PharmaCRM - Modelo de Devolucion (Modulo Usuario)
 * RF-04: Solicitud de devoluciones sobre compras Entregadas
 * 
 * Estados: Solicitada > En revision > Aprobada/Rechazada
 */
class Devolucion {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene las devoluciones del cliente con paginacion
     * @param int $clientId
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getByClient($clientId, $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        $stmt = $this->db->prepare("
            SELECT d.*, s.invoice_number, p.name as product_name,
                   si.quantity, si.unit_price
            FROM devoluciones d
            JOIN ventas s ON d.venta_id = s.id
            JOIN items_venta si ON d.item_venta_id = si.id
            JOIN productos p ON si.product_id = p.id
            WHERE d.cliente_id = ?
            ORDER BY d.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute([$clientId]);
        return $stmt->fetchAll();
    }

    /**
     * Cuenta total de devoluciones del cliente
     * @param int $clientId
     * @return int
     */
    public function countByClient($clientId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM devoluciones WHERE cliente_id = ?");
        $stmt->execute([$clientId]);
        return $stmt->fetch()['total'];
    }

    /**
     * Crea una nueva solicitud de devolucion
     * @param int $clientId
     * @param array $data
     * @return int
     */
    public function create($clientId, $data) {
        $stmt = $this->db->prepare("
            INSERT INTO devoluciones (cliente_id, venta_id, item_venta_id, motivo, descripcion, evidencia_path)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $clientId,
            $data['sale_id'],
            $data['sale_item_id'],
            $data['motivo'],
            $data['descripcion'] ?? null,
            $data['evidencia_path'] ?? null
        ]);

        $id = $this->db->lastInsertId();
        (new CustomerProfile())->logAudit($clientId, 'devolucion_solicitada', 'devoluciones', null, $id);
        return $id;
    }

    /**
     * Obtiene los motivos disponibles para devolucion
     * @return array
     */
    public static function getMotivos() {
        return [
            'defectuoso' => 'Producto defectuoso',
            'error_pedido' => 'Error en el pedido',
            'no_satisface' => 'No cumple expectativas',
            'caducado' => 'Producto caducado',
            'otro' => 'Otro motivo'
        ];
    }
}
