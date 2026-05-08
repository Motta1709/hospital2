<?php
/**
 * PharmaCRM - Modelo de Notificacion (Modulo Usuario)
 * RF-07: Registro de envios SMTP con trazabilidad
 */
class Notificacion {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getByClient($clientId, $page = 1, $perPage = 15) {
        $offset = ($page - 1) * $perPage;
        $stmt = $this->db->prepare("
            SELECT * FROM notificaciones 
            WHERE client_id = ?
            ORDER BY created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute([$clientId]);
        return $stmt->fetchAll();
    }

    public function countUnread($clientId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM notificaciones WHERE client_id = ? AND leida = 0");
        $stmt->execute([$clientId]);
        return $stmt->fetch()['total'];
    }

    public function markAsRead($id, $clientId) {
        $stmt = $this->db->prepare("UPDATE notificaciones SET leida = 1 WHERE id = ? AND client_id = ?");
        return $stmt->execute([$id, $clientId]);
    }

    public function markAllAsRead($clientId) {
        $stmt = $this->db->prepare("UPDATE notificaciones SET leida = 1 WHERE client_id = ? AND leida = 0");
        return $stmt->execute([$clientId]);
    }

    public static function create($clientId, $evento, $titulo, $mensaje, $refTipo = null, $refId = null) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO notificaciones (client_id, evento, titulo, mensaje, referencia_tipo, referencia_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$clientId, $evento, $titulo, $mensaje, $refTipo, $refId]);
        return $db->lastInsertId();
    }

    public static function getEventIcons() {
        return [
            'compra_confirmada' => ['icon' => 'fa-shopping-bag', 'color' => 'success'],
            'reserva_estado' => ['icon' => 'fa-bookmark', 'color' => 'info'],
            'domicilio_estado' => ['icon' => 'fa-truck', 'color' => 'primary'],
            'pqrsf_respuesta' => ['icon' => 'fa-comment-dots', 'color' => 'warning'],
            'devolucion_estado' => ['icon' => 'fa-rotate-left', 'color' => 'danger'],
            'sistema' => ['icon' => 'fa-bell', 'color' => 'info']
        ];
    }
}
