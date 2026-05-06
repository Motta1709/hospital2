<?php
/**
 * PharmaCRM - Modelo de Subclasificación
 */
class Subclassification {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($classificationId = null) {
        $query = "SELECT sc.*, cl.name as classification_name, cl.category_id, c.name as category_name
                  FROM subclassifications sc
                  JOIN classifications cl ON sc.classification_id = cl.id
                  JOIN categories c ON cl.category_id = c.id";
        $params = [];
        if ($classificationId) {
            $query .= " WHERE sc.classification_id = ?";
            $params[] = $classificationId;
        }
        $query .= " ORDER BY sc.name";
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT sc.*, cl.category_id 
            FROM subclassifications sc
            JOIN classifications cl ON sc.classification_id = cl.id
            WHERE sc.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO subclassifications (classification_id, name, description) VALUES (?, ?, ?)");
        $stmt->execute([$data['classification_id'], $data['name'], $data['description'] ?? '']);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE subclassifications SET classification_id = ?, name = ?, description = ? WHERE id = ?");
        return $stmt->execute([$data['classification_id'], $data['name'], $data['description'] ?? '', $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM subclassifications WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

