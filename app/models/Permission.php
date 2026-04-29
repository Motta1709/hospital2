<?php
/**
 * PharmaCRM - Modelo de Funciones (Permisos)
 */
class Permission {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllModules() {
        $stmt = $this->db->query("SELECT * FROM modules ORDER BY sort_order, name");
        return $stmt->fetchAll();
    }

    public function getPermissionsByModule() {
        $modules = $this->getAllModules();
        $result = [];
        
        foreach ($modules as $module) {
            $stmt = $this->db->prepare("SELECT * FROM permissions WHERE module_id = ?");
            $stmt->execute([$module['id']]);
            $module['permissions'] = $stmt->fetchAll();
            $result[] = $module;
        }
        
        return $result;
    }

    public function createModule($data) {
        $stmt = $this->db->prepare("INSERT INTO modules (name, icon, sort_order) VALUES (?, ?, ?)");
        return $stmt->execute([$data['name'], $data['icon'] ?? 'fa-folder', $data['sort_order'] ?? 0]);
    }

    public function createPermission($data) {
        $stmt = $this->db->prepare("INSERT INTO permissions (module_id, name, display_name, description) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$data['module_id'], $data['name'], $data['display_name'], $data['description'] ?? null]);
    }

    public function updatePermission($id, $data) {
        $stmt = $this->db->prepare("UPDATE permissions SET display_name = ?, description = ? WHERE id = ?");
        return $stmt->execute([$data['display_name'], $data['description'] ?? null, $id]);
    }

    public function deletePermission($id) {
        $stmt = $this->db->prepare("DELETE FROM permissions WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
