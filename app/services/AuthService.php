<?php
namespace App\Services;

use App\Interfaces\AuthServiceInterface;
use App\Interfaces\UserRepositoryInterface;

class AuthService implements AuthServiceInterface {
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository) {
        $this->userRepository = $userRepository;
    }

    public function authenticate(string $username, string $password): ?array {
        $user = $this->userRepository->findByUsername($username);

        if (!$user || !password_verify($password, $user['password'])) {
            return null;
        }

        return $user;
    }

    public function login(array $user): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role_name'] ?? ($user['role'] ?? 'guest');
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['branch_id'] = $user['branch_id'] ?? 1;
        $_SESSION['branch_name'] = $user['branch_name'] ?? 'Sede Principal';

        $this->loadPermissions($user['role_id']);
        $this->userRepository->updateLastLogin($user['id']);
    }

    public function logout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        $_SESSION = [];
    }

    public function check(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    public function user(): ?array {
        if (!$this->check()) return null;
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

    private function loadPermissions(int $roleId): void {
        // Podríamos mover esto a un PermissionRepository para ser más SOLID
        // pero por ahora lo mantendremos aquí o lo inyectaremos.
        $db = \Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT p.name 
            FROM permissions p
            JOIN role_permissions rp ON p.id = rp.permission_id
            WHERE rp.role_id = ?
        ");
        $stmt->execute([$roleId]);
        $_SESSION['permissions'] = $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
}
