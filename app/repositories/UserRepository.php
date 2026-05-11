<?php
namespace App\Repositories;

use App\Interfaces\UserRepositoryInterface;
use Database;

class UserRepository implements UserRepositoryInterface {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByUsername(string $username) {
        try {
            $stmt = $this->db->prepare("
                SELECT u.*, r.name as role_name, b.name as branch_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                LEFT JOIN branches b ON u.branch_id = b.id
                WHERE (u.username = ? OR u.email = ?) AND u.is_active = 1
            ");
            $stmt->execute([$username, $username]);
            return $stmt->fetch();
        } catch (\PDOException $e) {
            \App\Core\ErrorHandler::handle($e);
            return false;
        }
    }

    public function findById(int $id) {
        $stmt = $this->db->prepare("
            SELECT u.*, r.name as role_name, b.name as branch_name 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            LEFT JOIN branches b ON u.branch_id = b.id
            WHERE u.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function updateLastLogin(int $userId): void {
        $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$userId]);
    }
}
