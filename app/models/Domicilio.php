<?php
/**
 * PharmaCRM - Modelo de Domicilio (Modulo Usuario)
 * RF-05: Pedidos a domicilio
 */
class Domicilio {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByClient($clientId, $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        $stmt = $this->db->prepare("
            SELECT d.*, s.invoice_number, s.total as orden_total,
                   ca.direccion as address_line, ca.ciudad as city, ca.barrio as neighborhood, ca.etiqueta as address_label
            FROM domicilios d
            LEFT JOIN ventas s ON d.venta_id = s.id
            JOIN direcciones_cliente ca ON d.direccion_id = ca.id
            WHERE d.cliente_id = ?
            ORDER BY d.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute([$clientId]);
        return $stmt->fetchAll();
    }

    public function countByClient($clientId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM domicilios WHERE cliente_id = ?");
        $stmt->execute([$clientId]);
        return $stmt->fetch()['total'];
    }

    public function create($clientId, $data) {
        $stmt = $this->db->prepare("
            INSERT INTO domicilios (cliente_id, venta_id, direccion_id, tipo_entrega, fecha_programada, franja_horaria, notas, costo_envio)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $clientId, $data['sale_id'] ?? null, $data['address_id'],
            $data['tipo_entrega'] ?? 'inmediata', $data['fecha_programada'] ?? null,
            $data['franja_horaria'] ?? null, $data['notas'] ?? null, $data['costo_envio'] ?? 5000.00
        ]);
        return $this->db->lastInsertId();
    }

    public static function getFranjasHorarias() {
        return [
            'manana' => 'Manana (8:00 AM - 12:00 PM)',
            'tarde' => 'Tarde (12:00 PM - 6:00 PM)',
            'noche' => 'Noche (6:00 PM - 9:00 PM)'
        ];
    }

    public static function getEstadosTracking() {
        return [
            'pendiente' => ['label' => 'Pendiente', 'icon' => 'fa-clock', 'color' => 'warning'],
            'confirmado' => ['label' => 'Confirmado', 'icon' => 'fa-check', 'color' => 'info'],
            'despachado' => ['label' => 'Despachado', 'icon' => 'fa-truck', 'color' => 'primary'],
            'entregado' => ['label' => 'Entregado', 'icon' => 'fa-check-double', 'color' => 'success'],
            'cancelado' => ['label' => 'Cancelado', 'icon' => 'fa-times', 'color' => 'danger']
        ];
    }
}
