<?php
/**
 * PharmaCRM - Modelo de Clasificación
 */
class Classification {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($categoryId = null) {
        $query = "SELECT cl.*, c.name as category_name 
                  FROM classifications cl
                  JOIN categories c ON cl.category_id = c.id";
        $params = [];
        if ($categoryId) {
            $query .= " WHERE cl.category_id = ?";
            $params[] = $categoryId;
        }
        $query .= " ORDER BY cl.name";
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM classifications WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO classifications (category_id, name, description) VALUES (?, ?, ?)");
        $stmt->execute([$data['category_id'], $data['name'], $data['description'] ?? '']);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE classifications SET category_id = ?, name = ?, description = ? WHERE id = ?");
        return $stmt->execute([$data['category_id'], $data['name'], $data['description'] ?? '', $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM classifications WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

