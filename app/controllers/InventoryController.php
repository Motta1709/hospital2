<?php
class InventoryController {
    private $productModel;

    public function __construct() {
        $this->productModel = new Product();
    }

    public function index() {
        if (!Auth::hasPermission('view_inventory')) {
            setFlash('error', 'No tienes permiso para ver el inventario.');
            redirect('?route=dashboard');
        }
        $search = sanitize($_GET['search'] ?? '');
        $category = $_GET['category'] ?? null;
        $page = max(1, intval($_GET['page'] ?? 1));

        $data = [
            'products' => $this->productModel->getAll($page, ITEMS_PER_PAGE, $search, $category),
            'totalProducts' => $this->productModel->count($search, $category),
            'categories' => $this->productModel->getCategories(),
            'search' => $search,
            'category' => $category,
            'page' => $page,
            'totalPages' => ceil($this->productModel->count($search, $category) / ITEMS_PER_PAGE),
        ];

        $pageTitle = 'Inventario';
        $currentRoute = 'inventory';
        $content = APP_ROOT . '/views/inventory/index.php';
        include APP_ROOT . '/views/layouts/main.php';
    }

    public function create() {
        if (!Auth::hasPermission('manage_inventory')) {
            setFlash('error', 'No tienes permiso para gestionar el inventario.');
            redirect('?route=inventory');
        }
        $data = [
            'categories' => $this->productModel->getCategories(),
            'suppliers' => $this->productModel->getSuppliers(),
        ];
        $pageTitle = 'Nuevo Producto';
        $currentRoute = 'inventory';
        $content = APP_ROOT . '/views/inventory/create.php';
        include APP_ROOT . '/views/layouts/main.php';
    }

    public function store() {
        if (!Auth::hasPermission('manage_inventory')) redirect('?route=inventory');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('?route=inventory');
        $v = new Validator($_POST);
        $v->required('name', 'Nombre')->required('category_id', 'Categoría')
          ->required('expiration_date', 'Fecha de vencimiento')
          ->required('purchase_price', 'Precio compra')->required('sale_price', 'Precio venta')
          ->required('stock', 'Stock');

        if ($v->fails()) {
            setFlash('error', $v->firstError());
            redirect('?route=inventory&action=create');
        }

        try {
            $this->productModel->create($_POST);
            setFlash('success', 'Producto creado exitosamente.');
            redirect('?route=inventory');
        } catch (Exception $e) {
            setFlash('error', 'Error al crear: ' . $e->getMessage());
            redirect('?route=inventory&action=create');
        }
    }

    public function edit() {
        if (!Auth::hasPermission('manage_inventory')) {
            setFlash('error', 'No tienes permiso para gestionar el inventario.');
            redirect('?route=inventory');
        }
        $id = intval($_GET['id'] ?? 0);
        $product = $this->productModel->findById($id);
        if (!$product) { setFlash('error', 'Producto no encontrado.'); redirect('?route=inventory'); }

        $data = [
            'product' => $product,
            'categories' => $this->productModel->getCategories(),
            'suppliers' => $this->productModel->getSuppliers(),
        ];
        $pageTitle = 'Editar Producto';
        $currentRoute = 'inventory';
        $content = APP_ROOT . '/views/inventory/edit.php';
        include APP_ROOT . '/views/layouts/main.php';
    }

    public function update() {
        if (!Auth::hasPermission('manage_inventory')) redirect('?route=inventory');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('?route=inventory');
        $id = intval($_POST['id'] ?? 0);
        try {
            $this->productModel->update($id, $_POST);
            setFlash('success', 'Producto actualizado exitosamente.');
        } catch (Exception $e) {
            setFlash('error', 'Error al actualizar: ' . $e->getMessage());
        }
        redirect('?route=inventory');
    }

    public function delete() {
        if (!Auth::hasPermission('delete_products')) {
            setFlash('error', 'No tienes permiso para eliminar productos.');
            redirect('?route=inventory');
        }
        $id = intval($_GET['id'] ?? 0);
        if ($id) { $this->productModel->delete($id); setFlash('success', 'Producto eliminado.'); }
        redirect('?route=inventory');
    }

    public function search() {
        $query = sanitize($_GET['q'] ?? '');
        jsonResponse($this->productModel->search($query));
    }

    public function alerts() {
        $data = [
            'expiring30' => $this->productModel->getExpiringProducts(30),
            'expired' => $this->productModel->getExpiredProducts(),
            'lowStock' => $this->productModel->getLowStockProducts(),
            'outOfStock' => $this->productModel->getOutOfStockProducts(),
        ];
        $pageTitle = 'Alertas de Inventario';
        $currentRoute = 'inventory';
        $content = APP_ROOT . '/views/inventory/index.php';
        include APP_ROOT . '/views/layouts/main.php';
    }
}
