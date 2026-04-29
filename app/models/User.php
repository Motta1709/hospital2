<?php
/**
 * PharmaCRM - Modelo de Usuario
 */
class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByUsername($username) {
        $stmt = $this->db->prepare("
            SELECT u.*, r.name as role_name 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            WHERE u.username = ? AND u.is_active = 1
        ");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT u.*, r.name as role_name 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            WHERE u.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    public function getAll() {
        $stmt = $this->db->query("
            SELECT u.*, r.name as role_name 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            ORDER BY u.full_name
        ");
        return $stmt->fetchAll();
    }

    public function countActive() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM users WHERE is_active = 1");
        return $stmt->fetch()['total'];
    }

    public function create($data) {
        $sql = "INSERT INTO users (role_id, username, email, password, full_name, phone, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        return $stmt->execute([
            $data['role_id'],
            $data['username'],
            $data['email'],
            $password,
            $data['full_name'],
            $data['phone'] ?? null,
            $data['is_active'] ?? 1
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE users SET role_id = ?, username = ?, email = ?, full_name = ?, phone = ?, is_active = ? ";
        $params = [
            $data['role_id'],
            $data['username'],
            $data['email'],
            $data['full_name'],
            $data['phone'] ?? null,
            $data['is_active'] ?? 1
        ];

        if (!empty($data['password'])) {
            $sql .= ", password = ? ";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getPermissions($roleId) {
        $stmt = $this->db->prepare("
            SELECT p.*, m.name as module_name 
            FROM permissions p
            JOIN modules m ON p.module_id = m.id
            JOIN role_permissions rp ON p.id = rp.permission_id
            WHERE rp.role_id = ?
        ");
        $stmt->execute([$roleId]);
        return $stmt->fetchAll();
    }
}
