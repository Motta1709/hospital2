<?php
class AuthController {
    public function login() {
        include APP_ROOT . '/views/auth/login.php';
    }

    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('?route=login'); }
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$username || !$password) {
            setFlash('error', 'Complete todos los campos.');
            redirect('?route=login');
        }

        $userModel = new User();
        $user = $userModel->findByUsername($username);

        if (!$user || !$userModel->verifyPassword($password, $user['password'])) {
            setFlash('error', 'Credenciales incorrectas.');
            redirect('?route=login');
        }

        Auth::login($user);
        setFlash('success', '¡Bienvenido, ' . $user['full_name'] . '!');
        redirect('?route=dashboard');
    }

    public function logout() {
        Auth::logout();
        redirect('?route=login');
    }
}

