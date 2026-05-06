<?php
/**
 * PharmaCRM - Controlador de Sucursales
 */
class BranchController {
    private $branchModel;

    public function __construct() {
        if (!Auth::hasRole('admin')) {
            setFlash('error', 'No tienes permiso para gestionar sucursales.');
            redirect('?route=dashboard');
        }
        $this->branchModel = new Branch();
    }

    public function index() {
        $data = [
            'branches' => $this->branchModel->getAll()
        ];
        $pageTitle = 'Gestión de Sucursales';
        $currentRoute = 'branches';
        $content = APP_ROOT . '/views/branches/index.php';
        include APP_ROOT . '/views/layouts/main.php';
    }

    public function create() {
        $pageTitle = 'Nueva Sucursal';
        $currentRoute = 'branches';
        $content = APP_ROOT . '/views/branches/create.php';
        include APP_ROOT . '/views/layouts/main.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('?route=branches');
        
        $v = new Validator($_POST);
        $v->required('name', 'Nombre de la sucursal');

        if ($v->fails()) {
            setFlash('error', $v->firstError());
            redirect('?route=branches&action=create');
        }

        try {
            $this->branchModel->create($_POST);
            setFlash('success', 'Sucursal creada exitosamente. Se ha generado un usuario administrador por defecto.');
            redirect('?route=branches');
        } catch (Exception $e) {
            setFlash('error', 'Error al crear la sucursal: ' . $e->getMessage());
            redirect('?route=branches&action=create');
        }
    }

    public function edit() {
        $id = intval($_GET['id'] ?? 0);
        $branch = $this->branchModel->findById($id);
        if (!$branch) {
            setFlash('error', 'Sucursal no encontrada.');
            redirect('?route=branches');
        }

        $data = ['branch' => $branch];
        $pageTitle = 'Editar Sucursal';
        $currentRoute = 'branches';
        $content = APP_ROOT . '/views/branches/edit.php';
        include APP_ROOT . '/views/layouts/main.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('?route=branches');
        $id = intval($_POST['id'] ?? 0);
        
        try {
            $this->branchModel->update($id, $_POST);
            setFlash('success', 'Sucursal actualizada correctamente.');
        } catch (Exception $e) {
            setFlash('error', 'Error al actualizar: ' . $e->getMessage());
        }
        redirect('?route=branches');
    }
}

