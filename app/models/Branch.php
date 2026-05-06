<?php
/**
 * PharmaCRM - Modelo de Sucursales
 */
class Branch {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($activeOnly = false) {
        $sql = "SELECT * FROM branches";
        if ($activeOnly) $sql .= " WHERE is_active = 1";
        $sql .= " ORDER BY name";
        return $this->db->query($sql)->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM branches WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $this->db->beginTransaction();
        try {
            // 1. Crear sucursal
            $stmt = $this->db->prepare("
                INSERT INTO branches (name, nit, address, city, phone, email) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['name'], $data['nit'] ?? null, $data['address'] ?? null, 
                $data['city'] ?? null, $data['phone'] ?? null, $data['email'] ?? null
            ]);
            $branchId = $this->db->lastInsertId();

            // 2. Crear usuario administrador de sucursal por defecto
            $this->createBranchAdmin($branchId, $data['name']);

            $this->db->commit();
            return $branchId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function createBranchAdmin($branchId, $branchName) {
        // Obtener ID del rol branch_admin
        $stmt = $this->db->prepare("SELECT id FROM roles WHERE name = 'branch_admin'");
        $stmt->execute();
        $roleId = $stmt->fetch()['id'] ?? null;
        if (!$roleId) throw new Exception("Rol 'branch_admin' no encontrado.");

        // Generar username: admin_nombre_sucursal (sanitizado)
        $username = 'admin_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $branchName));
        
        // Password por defecto: Branch123! (se debe cambiar luego)
        $password = password_hash('Branch123!', PASSWORD_BCRYPT, ['cost' => 12]);

        $stmt = $this->db->prepare("
            INSERT INTO users (role_id, branch_id, username, email, password, full_name) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $roleId, $branchId, $username, 
            $username . '@pharmacrm.local', $password, 
            'Admin ' . $branchName
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE branches SET name = ?, nit = ?, address = ?, city = ?, 
            phone = ?, email = ?, is_active = ? WHERE id = ?
        ");
        return $stmt->execute([
            $data['name'], $data['nit'], $data['address'], $data['city'], 
            $data['phone'], $data['email'], $data['is_active'] ?? 1, $id
        ]);
    }
}

