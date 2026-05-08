<?php
/**
 * PharmaCRM - Modelo PQRSF (Modulo Usuario)
 * RF-02: Peticiones, Quejas, Reclamos, Sugerencias, Felicitaciones
 * 
 * Genera radicado unico, inmutable y auditable.
 * Cumple ciclo de mejora continua ISO 9001.
 */
class PQRSF {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene todas las PQRSF de un cliente con paginacion
     * @param int $clientId
     * @param int $page
     * @param int $perPage
     * @param string|null $estado Filtro por estado
     * @return array
     */
    public function getByClient($clientId, $page = 1, $perPage = 10, $estado = null) {
        $offset = ($page - 1) * $perPage;
        $where = "WHERE cliente_id = ?";
        $params = [$clientId];
        if ($estado) {
            $where .= " AND estado = ?";
            $params[] = $estado;
        }
        $stmt = $this->db->prepare("
            SELECT p.*, u.full_name as respondido_por_nombre
            FROM pqrsf p
            LEFT JOIN usuarios u ON p.respondido_por = u.id
            {$where}
            ORDER BY p.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Cuenta total de PQRSF de un cliente
     * @param int $clientId
     * @param string|null $estado
     * @return int
     */
    public function countByClient($clientId, $estado = null) {
        $where = "WHERE cliente_id = ?";
        $params = [$clientId];
        if ($estado) {
            $where .= " AND estado = ?";
            $params[] = $estado;
        }
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM pqrsf {$where}");
        $stmt->execute($params);
        return $stmt->fetch()['total'];
    }

    /**
     * Crea una nueva PQRSF con radicado unico
     * @param int $clientId
     * @param array $data
     * @return int
     */
    public function create($clientId, $data) {
        $radicado = $this->generateRadicado();
        $fechaLimite = date('Y-m-d', strtotime('+15 days'));

        $stmt = $this->db->prepare("
            INSERT INTO pqrsf (cliente_id, radicado, tipo, asunto, descripcion, prioridad, fecha_limite)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $clientId,
            $radicado,
            $data['tipo'],
            $data['asunto'],
            $data['descripcion'],
            $data['prioridad'] ?? 'media',
            $fechaLimite
        ]);

        $id = $this->db->lastInsertId();

        // Registrar en auditoria
        (new CustomerProfile())->logAudit($clientId, 'pqrsf_creada', 'pqrsf', null, $radicado);

        return $id;
    }

    /**
     * Obtiene una PQRSF por ID validando propiedad del cliente
     * @param int $id
     * @param int $clientId
     * @return array|false
     */
    public function findByIdAndClient($id, $clientId) {
        $stmt = $this->db->prepare("
            SELECT p.*, u.full_name as respondido_por_nombre
            FROM pqrsf p
            LEFT JOIN usuarios u ON p.respondido_por = u.id
            WHERE p.id = ? AND p.cliente_id = ?
        ");
        $stmt->execute([$id, $clientId]);
        return $stmt->fetch();
    }

    /**
     * Genera un numero de radicado unico e inmutable
     * Formato: PQRSF-YYYY-NNNN
     * @return string
     */
    private function generateRadicado() {
        $year = date('Y');
        $stmt = $this->db->prepare("SELECT COUNT(*) + 1 as next FROM pqrsf WHERE YEAR(created_at) = ?");
        $stmt->execute([$year]);
        $next = $stmt->fetch()['next'];
        return 'PQRSF-' . $year . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Obtiene conteo por estado para el dashboard
     * @param int $clientId
     * @return array
     */
    public function getStatusCounts($clientId) {
        $stmt = $this->db->prepare("
            SELECT estado, COUNT(*) as total
            FROM pqrsf WHERE cliente_id = ?
            GROUP BY estado
        ");
        $stmt->execute([$clientId]);
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['estado']] = $row['total'];
        }
        return $result;
    }
}
