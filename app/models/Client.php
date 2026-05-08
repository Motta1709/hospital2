<?php
/**
 * PharmaCRM - Modelo de Cliente
 */
class Client {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($page = 1, $perPage = ITEMS_PER_PAGE, $search = '') {
        $offset = ($page - 1) * $perPage;
        $where = "WHERE activo = 1";
        $params = [];

        if ($search) {
            $where .= " AND (first_name LIKE ? OR last_name LIKE ? OR document_number LIKE ? OR phone LIKE ?)";
            $params = array_fill(0, 4, "%{$search}%");
        }

        $stmt = $this->db->prepare("
            SELECT * FROM clientes {$where}
            ORDER BY last_name, first_name
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function count($search = '') {
        $where = "WHERE is_active = 1";
        $params = [];
        if ($search) {
            $where .= " AND (first_name LIKE ? OR last_name LIKE ? OR document_number LIKE ? OR phone LIKE ?)";
            $params = array_fill(0, 4, "%{$search}%");
        }
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM clientes {$where}");
        $stmt->execute($params);
        return $stmt->fetch()['total'];
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByDocument($docNumber) {
        $stmt = $this->db->prepare("SELECT * FROM clientes WHERE document_number = ?");
        $stmt->execute([$docNumber]);
        return $stmt->fetch();
    }

    public function search($query) {
        $stmt = $this->db->prepare("
            SELECT id, first_name, last_name, document_number, phone, loyalty_points, allergies
            FROM clientes
            WHERE activo = 1
              AND (first_name LIKE ? OR last_name LIKE ? OR document_number LIKE ? OR phone LIKE ?)
            ORDER BY first_name, last_name
            LIMIT 20
        ");
        $term = "%{$query}%";
        $stmt->execute([$term, $term, $term, $term]);
        return $stmt->fetchAll();
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO clientes (document_type, document_number, first_name, last_name, 
                email, phone, address, city, date_of_birth, gender, allergies, medical_notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['document_type'] ?? 'CC', $data['document_number'],
            $data['first_name'], $data['last_name'],
            $data['email'] ?? null, $data['phone'] ?? null,
            $data['address'] ?? null, $data['city'] ?? null,
            $data['date_of_birth'] ?? null, $data['gender'] ?? null,
            $data['allergies'] ?? null, $data['medical_notes'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE clientes SET 
                document_type = ?, document_number = ?, first_name = ?, last_name = ?,
                email = ?, phone = ?, address = ?, city = ?, date_of_birth = ?,
                gender = ?, allergies = ?, medical_notes = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['document_type'] ?? 'CC', $data['document_number'],
            $data['first_name'], $data['last_name'],
            $data['email'] ?? null, $data['phone'] ?? null,
            $data['address'] ?? null, $data['city'] ?? null,
            $data['date_of_birth'] ?? null, $data['gender'] ?? null,
            $data['allergies'] ?? null, $data['medical_notes'] ?? null, $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("UPDATE clients SET is_active = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function updatePurchaseStats($clientId, $amount) {
        $points = floor($amount / 1000) * LOYALTY_POINTS_PER_1000;
        $stmt = $this->db->prepare("
            UPDATE clients SET 
                total_purchases = total_purchases + ?,
                visit_count = visit_count + 1,
                loyalty_points = loyalty_points + ?
            WHERE id = ?
        ");
        $stmt->execute([$amount, $points, $clientId]);
        return $points;
    }

    public function getTopClients($limit = 10) {
        $stmt = $this->db->prepare("
            SELECT * FROM clients 
            WHERE is_active = 1 
            ORDER BY total_purchases DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getTotalClients() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM clientes WHERE activo = 1");
        return $stmt->fetch()['total'];
    }

    public function getClientPurchaseHistory($clientId, $limit = 20) {
        $stmt = $this->db->prepare("
            SELECT s.*, u.full_name as cashier_name
            FROM ventas s
            JOIN usuarios u ON s.user_id = u.id
            WHERE s.cliente_id = ? AND s.status = 'completed'
            ORDER BY s.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$clientId, $limit]);
        return $stmt->fetchAll();
    }

    public function getFrequentMedications($clientId) {
        $stmt = $this->db->prepare("
            SELECT p.name, p.generic_name, p.presentation, p.concentration,
                   SUM(si.quantity) as total_purchased, 
                   MAX(s.created_at) as last_purchase
            FROM items_venta si
            JOIN ventas s ON si.venta_id = s.id
            JOIN productos p ON si.product_id = p.id
            WHERE s.cliente_id = ? AND s.status = 'completed'
            GROUP BY p.id
            ORDER BY total_purchased DESC
            LIMIT 10
        ");
        $stmt->execute([$clientId]);
        return $stmt->fetchAll();
    }
}

