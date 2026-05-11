<?php
/**
 * PharmaCRM - Controlador de Autenticacion Unificado
 * Maneja login de Staff (users) y Clientes (clients) desde un unico formulario.
 */
class AuthController {
    private $authService;

    public function __construct(\App\Interfaces\AuthServiceInterface $authService = null) {
        if ($authService === null) {
            $userRepo = new \App\Repositories\UserRepository();
            $this->authService = new \App\Services\AuthService($userRepo);
        } else {
            $this->authService = $authService;
        }
    }

    public function login() {
        // Si ya tiene sesion activa, redirigir segun tipo
        if ($this->authService->check()) {
            redirect('?route=dashboard');
        }
        if (!empty($_SESSION['client_id'])) {
            redirect('?route=customer-dashboard');
        }
        include APP_ROOT . '/views/auth/login.php';
    }

    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('?route=login');
        }

        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$username || !$password) {
            setFlash('error', 'Complete todos los campos.');
            redirect('?route=login');
        }

        // 1. Intentar login como Staff (tabla users)
        $user = $this->authService->authenticate($username, $password);

        if ($user) {
            $this->authService->login($user);
            setFlash('success', 'Bienvenido, ' . $user['full_name'] . '!');
            redirect('?route=dashboard');
        }

        // 2. Intentar login como Cliente (tabla clients)
        $client = $this->authenticateClient($username, $password);

        if ($client) {
            $this->loginClient($client);
            setFlash('success', 'Bienvenido, ' . $client['first_name'] . '!');
            redirect('?route=customer-dashboard');
        }

        // 3. Ninguno encontrado
        setFlash('error', 'Credenciales incorrectas.');
        redirect('?route=login');
    }

    /**
     * Registro de nuevo cliente (autoregistro)
     */
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            include APP_ROOT . '/views/auth/register.php';
            return;
        }

        $v = new Validator($_POST);
        $v->required('first_name', 'Nombre')
          ->required('last_name', 'Apellido')
          ->required('document_number', 'Documento')
          ->required('email', 'Correo')
          ->required('password', 'Contrasena')
          ->required('password_confirm', 'Confirmar contrasena');

        if ($v->fails()) {
            setFlash('error', $v->firstError());
            redirect('?route=auth&action=register');
        }

        if ($_POST['password'] !== $_POST['password_confirm']) {
            setFlash('error', 'Las contrasenas no coinciden.');
            redirect('?route=auth&action=register');
        }

        $db = Database::getInstance()->getConnection();

        // Verificar duplicados
        $stmt = $db->prepare("SELECT id FROM clients WHERE email = ? OR document_number = ?");
        $stmt->execute([$_POST['email'], $_POST['document_number']]);
        if ($stmt->fetch()) {
            setFlash('error', 'Ya existe un cliente con ese correo o documento.');
            redirect('?route=auth&action=register');
        }

        // Crear cliente
        $stmt = $db->prepare("
            INSERT INTO clients (document_type, document_number, first_name, last_name, email, password, phone, city)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_POST['document_type'] ?? 'CC',
            $_POST['document_number'],
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['email'],
            password_hash($_POST['password'], PASSWORD_BCRYPT),
            $_POST['phone'] ?? null,
            $_POST['city'] ?? null,
        ]);

        setFlash('success', 'Cuenta creada exitosamente. Inicie sesion.');
        redirect('?route=login');
    }

    public function logout() {
        $this->authService->logout();
        // Limpiar sesion de cliente tambien
        unset($_SESSION['client_id'], $_SESSION['client_name'], $_SESSION['client_email']);
        session_destroy();
        redirect('?route=login');
    }

    // --- Metodos privados para clientes ---

    private function authenticateClient(string $identifier, string $password): ?array {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT * FROM clients
            WHERE (email = ? OR document_number = ?) AND is_active = 1
        ");
        $stmt->execute([$identifier, $identifier]);
        $client = $stmt->fetch();

        if (!$client || empty($client['password'])) {
            return null;
        }

        if (!password_verify($password, $client['password'])) {
            return null;
        }

        return $client;
    }

    private function loginClient(array $client): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['client_id'] = $client['id'];
        $_SESSION['client_name'] = $client['first_name'] . ' ' . $client['last_name'];
        $_SESSION['client_email'] = $client['email'];
    }
}
