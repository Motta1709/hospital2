<?php
/**
 * PharmaCRM - Helper de Autenticación
 */
class Auth {
    private static $service;

    private static function getService() {
        if (self::$service === null) {
            $userRepo = new \App\Repositories\UserRepository();
            self::$service = new \App\Services\AuthService($userRepo);
        }
        return self::$service;
    }

    public static function check() {
        return self::getService()->check();
    }

    public static function user() {
        return self::getService()->user();
    }

    public static function login($user) {
        self::getService()->login($user);
    }

    public static function logout() {
        self::getService()->logout();
    }

    public static function hasPermission($permission) {
        if (!self::check()) return false;
        // El admin siempre tiene todos los permisos
        if ($_SESSION['role'] === 'admin') return true;
        
        $permissions = $_SESSION['permissions'] ?? [];
        return in_array($permission, $permissions);
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