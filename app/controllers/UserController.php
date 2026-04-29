<?php
/**
 * PharmaCRM - Controlador de Usuarios
 */
class UserController {
    private $userModel;
    private $roleModel;

    public function __construct() {
        if (!Auth::hasPermission('view_users')) {
            setFlash('error', 'No tienes permiso para acceder a este módulo.');
            redirect('?route=dashboard');
        }
        $this->userModel = new User();
        $this->roleModel = new Role();
    }

    public function index() {
        $data = [
            'users' => $this->userModel->getAll()
        ];
        $pageTitle = 'Gestión de Usuarios';
        $currentRoute = 'users';
        $content = APP_ROOT . '/views/users/index.php';
        include APP_ROOT . '/views/layouts/main.php';
    }

    public function create() {
        if (!Auth::hasPermission('manage_users')) {
            setFlash('error', 'No tienes permiso para crear usuarios.');
            redirect('?route=users');
        }
        $data = [
            'roles' => $this->roleModel->getAll()
        ];
        $pageTitle = 'Nuevo Usuario';
        $currentRoute = 'users';
        $content = APP_ROOT . '/views/users/form.php';
        include APP_ROOT . '/views/layouts/main.php';
    }

    public function store() {
        if (!Auth::hasPermission('manage_users')) return redirect('?route=users');
        
        $v = new Validator($_POST);
        $v->required('username', 'Usuario')->required('email', 'Email')
          ->required('password', 'Contraseña')->required('role_id', 'Rol');
        
        if ($v->fails()) {
            setFlash('error', $v->firstError());
            redirect('?route=users&action=create');
        }

        try {
            $this->userModel->create($_POST);
            setFlash('success', 'Usuario creado exitosamente.');
            redirect('?route=users');
        } catch (Exception $e) {
            setFlash('error', 'Error: ' . $e->getMessage());
            redirect('?route=users&action=create');
        }
    }

    public function edit() {
        if (!Auth::hasPermission('manage_users')) {
            setFlash('error', 'No tienes permiso para editar usuarios.');
            redirect('?route=users');
        }
        $id = intval($_GET['id'] ?? 0);
        $user = $this->userModel->findById($id);
        if (!$user) { setFlash('error', 'Usuario no encontrado.'); redirect('?route=users'); }
        
        $data = [
            'user' => $user,
            'roles' => $this->roleModel->getAll()
        ];
        $pageTitle = 'Editar Usuario';
        $currentRoute = 'users';
        $content = APP_ROOT . '/views/users/form.php';
        include APP_ROOT . '/views/layouts/main.php';
    }

    public function update() {
        if (!Auth::hasPermission('manage_users')) return redirect('?route=users');
        
        $id = intval($_POST['id'] ?? 0);
        try {
            $this->userModel->update($id, $_POST);
            setFlash('success', 'Usuario actualizado.');
        } catch (Exception $e) {
            setFlash('error', 'Error: ' . $e->getMessage());
        }
        redirect('?route=users');
    }

    public function delete() {
        if (!Auth::hasPermission('manage_users')) {
            setFlash('error', 'No tienes permiso para eliminar usuarios.');
            return redirect('?route=users');
        }
        $id = intval($_GET['id'] ?? 0);
        if ($id == Auth::id()) {
            setFlash('error', 'No puedes eliminar tu propio usuario.');
        } else {
            $this->userModel->delete($id);
            setFlash('success', 'Usuario eliminado.');
        }
        redirect('?route=users');
    }
}
