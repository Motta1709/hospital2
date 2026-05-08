<?php
/**
 * PharmaCRM - Modelo de Perfil de Cliente (Modulo Usuario)
 * RF-01: Gestion de perfil con trazabilidad ISO 9001
 * 
 * Extiende la funcionalidad del modelo Client existente
 * para las operaciones especificas del dashboard del cliente.
 */
class CustomerProfile {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene el perfil completo del cliente por ID
     * @param int $clientId
     * @return array|false
     */
    public function getProfile($clientId) {
        $stmt = $this->db->prepare("
            SELECT c.*, 
                   (SELECT COUNT(*) FROM direcciones_cliente WHERE cliente_id = c.id AND activo = 1) as total_addresses,
                   (SELECT COUNT(*) FROM pqrsf WHERE cliente_id = c.id) as total_pqrsf,
                   (SELECT COUNT(*) FROM reservas WHERE cliente_id = c.id) as total_reservas,
                   (SELECT COUNT(*) FROM notificaciones WHERE cliente_id = c.id AND leida = 0) as unread_notifications
            FROM clientes c
            WHERE c.id = ? AND c.is_active = 1
        ");
        $stmt->execute([$clientId]);
        return $stmt->fetch();
    }

    /**
     * Actualiza los datos del perfil del cliente con auditoria
     * @param int $clientId
     * @param array $data
     * @return bool
     */
    public function updateProfile($clientId, $data) {
        $current = $this->getProfile($clientId);
        if (!$current) return false;

        $fields = ['first_name', 'last_name', 'email', 'phone', 'allergies', 'medical_notes'];
        $updates = [];
        $params = [];

        foreach ($fields as $field) {
            if (isset($data[$field]) && $data[$field] !== $current[$field]) {
                $updates[] = "{$field} = ?";
                $params[] = $data[$field];
                // Registro en bitacora ISO 9001
                $this->logAudit($clientId, 'perfil_actualizado', $field, $current[$field], $data[$field]);
            }
        }

        if (empty($updates)) return true;

        $params[] = $clientId;
        $stmt = $this->db->prepare("UPDATE clients SET " . implode(', ', $updates) . " WHERE id = ?");
        return $stmt->execute($params);
    }

    /**
     * Obtiene las direcciones del cliente
     * @param int $clientId
     * @return array
     */
    public function getAddresses($clientId) {
        $stmt = $this->db->prepare("
            SELECT * FROM direcciones_cliente 
            WHERE cliente_id = ? AND activo = 1 
            ORDER BY es_principal DESC, created_at DESC
        ");
        $stmt->execute([$clientId]);
        return $stmt->fetchAll();
    }

    /**
     * Agrega una nueva direccion al cliente
     * @param int $clientId
     * @param array $data
     * @return int
     */
    public function addAddress($clientId, $data) {
        if (!empty($data['is_default'])) {
            $this->db->prepare("UPDATE direcciones_cliente SET es_principal = 0 WHERE cliente_id = ?")->execute([$clientId]);
        }
        $stmt = $this->db->prepare("
            INSERT INTO direcciones_cliente (cliente_id, etiqueta, direccion, ciudad, barrio, codigo_postal, instrucciones, es_principal)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $clientId,
            $data['label'] ?? 'casa',
            $data['address_line'],
            $data['city'],
            $data['neighborhood'] ?? null,
            $data['postal_code'] ?? null,
            $data['instructions'] ?? null,
            $data['is_default'] ?? 0
        ]);
        $this->logAudit($clientId, 'direccion_agregada', 'direcciones_cliente', null, $data['address_line']);
        return $this->db->lastInsertId();
    }

    /**
     * Elimina (soft delete) una direccion del cliente
     * @param int $clientId
     * @param int $addressId
     * @return bool
     */
    public function removeAddress($clientId, $addressId) {
        $stmt = $this->db->prepare("UPDATE direcciones_cliente SET activo = 0 WHERE id = ? AND cliente_id = ?");
        $this->logAudit($clientId, 'direccion_eliminada', 'direcciones_cliente', $addressId, null);
        return $stmt->execute([$addressId, $clientId]);
    }

    /**
     * Obtiene el resumen estadistico para el dashboard del cliente
     * @param int $clientId
     * @return array
     */
    public function getDashboardSummary($clientId) {
        $summary = [];

        // Total de compras y ultima compra
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total_compras, 
                   COALESCE(SUM(total), 0) as total_gastado,
                   MAX(created_at) as ultima_compra
            FROM ventas WHERE cliente_id = ? AND status = 'completed'
        ");
        $stmt->execute([$clientId]);
        $summary['compras'] = $stmt->fetch();

        // Puntos de fidelizacion
        $stmt = $this->db->prepare("SELECT loyalty_points FROM clientes WHERE id = ?");
        $stmt->execute([$clientId]);
        $summary['puntos'] = $stmt->fetch()['loyalty_points'] ?? 0;

        // PQRSF abiertas
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM pqrsf WHERE cliente_id = ? AND estado IN ('abierto', 'en_proceso')");
        $stmt->execute([$clientId]);
        $summary['pqrsf_abiertas'] = $stmt->fetch()['total'];

        // Reservas pendientes
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM reservas WHERE cliente_id = ? AND estado = 'pendiente'");
        $stmt->execute([$clientId]);
        $summary['reservas_pendientes'] = $stmt->fetch()['total'];

        // Domicilios activos
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM domicilios WHERE cliente_id = ? AND estado IN ('pendiente', 'confirmado', 'despachado')");
        $stmt->execute([$clientId]);
        $summary['domicilios_activos'] = $stmt->fetch()['total'];

        // Devoluciones en proceso
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM devoluciones WHERE cliente_id = ? AND estado IN ('solicitada', 'en_revision')");
        $stmt->execute([$clientId]);
        $summary['devoluciones_proceso'] = $stmt->fetch()['total'];

        // Notificaciones no leidas
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM notificaciones WHERE cliente_id = ? AND leida = 0");
        $stmt->execute([$clientId]);
        $summary['notificaciones'] = $stmt->fetch()['total'];

        return $summary;
    }

    /**
     * Registra accion en bitacora de auditoria (ISO 9001 - RNF-06)
     * @param int $clientId
     * @param string $accion
     * @param string|null $campo
     * @param mixed $valorAnterior
     * @param mixed $valorNuevo
     */
    public function logAudit($clientId, $accion, $campo = null, $valorAnterior = null, $valorNuevo = null) {
        $stmt = $this->db->prepare("
            INSERT INTO bitacora_cliente (cliente_id, accion, campo_alterado, valor_anterior, valor_nuevo, ip_address)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $clientId,
            $accion,
            $campo,
            is_array($valorAnterior) ? json_encode($valorAnterior) : $valorAnterior,
            is_array($valorNuevo) ? json_encode($valorNuevo) : $valorNuevo,
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ]);
    }
}
