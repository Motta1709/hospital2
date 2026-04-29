<?php
/**
 * PharmaCRM - Modelo de Programa de Fidelización
 */
class LoyaltyProgram {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function addTransaction($clientId, $saleId, $points, $type, $description) {
        $client = (new Client())->findById($clientId);
        $balanceAfter = ($client['loyalty_points'] ?? 0);
        if ($type === 'earned' || $type === 'bonus') $balanceAfter += $points;
        else $balanceAfter -= $points;

        $stmt = $this->db->prepare("
            INSERT INTO loyalty_transactions (client_id, sale_id, points, type, description, balance_after)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$clientId, $saleId, $points, $type, $description, $balanceAfter]);
    }

    public function getTransactions($clientId, $limit = 20) {
        $stmt = $this->db->prepare("
            SELECT lt.*, s.invoice_number 
            FROM loyalty_transactions lt
            LEFT JOIN sales s ON lt.sale_id = s.id
            WHERE lt.client_id = ? ORDER BY lt.created_at DESC LIMIT ?
        ");
        $stmt->execute([$clientId, $limit]);
        return $stmt->fetchAll();
    }

    public function redeemPoints($clientId, $points, $description = 'Canje de puntos') {
        $client = (new Client())->findById($clientId);
        if ($client['loyalty_points'] < $points) return false;

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("UPDATE clients SET loyalty_points = loyalty_points - ? WHERE id = ?");
            $stmt->execute([$points, $clientId]);
            $this->addTransaction($clientId, null, $points, 'redeemed', $description);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getTopLoyaltyClients($limit = 10) {
        $stmt = $this->db->prepare("SELECT id, first_name, last_name, loyalty_points, total_purchases, visit_count FROM clients WHERE is_active=1 AND loyalty_points>0 ORDER BY loyalty_points DESC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getTotalPointsIssued() {
        $stmt = $this->db->query("SELECT COALESCE(SUM(points),0) as total FROM loyalty_transactions WHERE type IN ('earned','bonus')");
        return $stmt->fetch()['total'];
    }

    public function getTotalPointsRedeemed() {
        $stmt = $this->db->query("SELECT COALESCE(SUM(points),0) as total FROM loyalty_transactions WHERE type='redeemed'");
        return $stmt->fetch()['total'];
    }
}
