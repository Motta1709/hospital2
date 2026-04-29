<?php
/**
 * PharmaCRM - Controlador de Roles y Permisos (RBAC)
 */
class RbacController {
    private $roleModel;
    private $permissionModel;

    public function __construct() {
        if (!Auth::hasPermission('manage_rbac')) {
            setFlash('error', 'No tienes permiso para acceder a la configuración de seguridad.');
            redirect('?route=dashboard');
        }
        $this->roleModel = new Role();
        $this->permissionModel = new Permission();
    }

    public function index() {
        $data = [
            'roles' => $this->roleModel->getAll(),
            'modules' => $this->permissionModel->getPermissionsByModule()
        ];
        $pageTitle = 'Seguridad y Permisos';
        $currentRoute = 'rbac';
        $content = APP_ROOT . '/views/rbac/index.php';
        include APP_ROOT . '/views/layouts/main.php';
    }

    public function editRole() {
        $id = intval($_GET['id'] ?? 0);
        $role = $this->roleModel->findById($id);
        if (!$role) { setFlash('error', 'Rol no encontrado.'); redirect('?route=rbac'); }

        $data = [
            'role' => $role,
            'modules' => $this->permissionModel->getPermissionsByModule(),
            'rolePermissions' => $this->roleModel->getPermissions($id)
        ];
        $pageTitle = 'Editar Permisos: ' . $role['name'];
        $currentRoute = 'rbac';
        $content = APP_ROOT . '/views/rbac/role_permissions.php';
        include APP_ROOT . '/views/layouts/main.php';
    }

    public function updateRolePermissions() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('?route=rbac');
        
        $roleId = intval($_POST['role_id'] ?? 0);
        $permissionIds = $_POST['permissions'] ?? [];
        
        try {
            $this->roleModel->syncPermissions($roleId, $permissionIds);
            setFlash('success', 'Permisos actualizados correctamente.');
        } catch (Exception $e) {
            setFlash('error', 'Error: ' . $e->getMessage());
        }
        redirect('?route=rbac');
    }

    public function storePermission() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('?route=rbac');
        
        try {
            $this->permissionModel->createPermission($_POST);
            setFlash('success', 'Función creada exitosamente.');
        } catch (Exception $e) {
            setFlash('error', 'Error: ' . $e->getMessage());
        }
        redirect('?route=rbac');
    }
}
