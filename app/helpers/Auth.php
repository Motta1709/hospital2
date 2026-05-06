<?php
/**
 * PharmaCRM - Helper de Autenticación
 */
class Auth {

    public static function check() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    public static function user() {
        if (!self::check()) return null;
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'full_name' => $_SESSION['full_name'],
            'role' => $_SESSION['role'],
            'role_id' => $_SESSION['role_id'],
            'branch_id' => $_SESSION['branch_id'] ?? 1,
            'branch_name' => $_SESSION['branch_name'] ?? 'Sede Principal',
            'email' => $_SESSION['email'] ?? ''
        ];
    }

    public static function login($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role_name'] ?? $user['role'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['branch_id'] = $user['branch_id'] ?? 1;
        $_SESSION['branch_name'] = $user['branch_name'] ?? 'Sede Principal';

        // Cargar permisos en la sesión
        self::loadPermissions($user['role_id']);

        // Update last login
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
    }

    public static function loadPermissions($roleId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT p.name 
            FROM permissions p
            JOIN role_permissions rp ON p.id = rp.permission_id
            WHERE rp.role_id = ?
        ");
        $stmt->execute([$roleId]);
        $_SESSION['permissions'] = $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public static function hasPermission($permission) {
        if (!self::check()) return false;
        // El admin siempre tiene todos los permisos
        if ($_SESSION['role'] === 'admin') return true;
        
        $permissions = $_SESSION['permissions'] ?? [];
        return in_array($permission, $permissions);
    }

    public static function logout() {
        session_destroy();
        $_SESSION = [];
    }

    public static function isAdmin() {
        return self::check() && $_SESSION['role'] === 'admin';
    }

    public static function isSupervisor() {
        return self::check() && in_array($_SESSION['role'], ['admin', 'supervisor']);
    }

    public static function id() {
        return $_SESSION['user_id'] ?? null;
    }

    public static function hasRole($roles) {
        if (!self::check()) return false;
        if (is_string($roles)) $roles = [$roles];
        return in_array($_SESSION['role'], $roles);
    }
}