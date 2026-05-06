<?php
/**
 * PharmaCRM - Controlador de Carrito de Compras
 */
class CartController {
    private $productModel;

    public function __construct() {
        $this->productModel = new Product();
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    public function index() {
        $cartItems = [];
        $total = 0;
        $selectedTotal = 0;

        foreach ($_SESSION['cart'] as $id => $item) {
            $product = $this->productModel->findById($id);
            if ($product) {
                $product['qty'] = $item['qty'];
                $product['selected'] = $item['selected'];
                $subtotal = $product['sale_price'] * $item['qty'];
                $product['subtotal'] = $subtotal;
                $cartItems[] = $product;
                
                $total += $subtotal;
                if ($item['selected']) {
                    $selectedTotal += $subtotal;
                }
            }
        }

        $data = [
            'cart' => $cartItems,
            'total' => $total,
            'selectedTotal' => $selectedTotal
        ];

        $pageTitle = 'Carrito de Compras';
        $currentRoute = 'cart';
        $content = APP_ROOT . '/views/cart/index.php';
        include APP_ROOT . '/views/layouts/main.php';
    }

    public function add() {
        $id = intval($_GET['id'] ?? 0);
        $qty = intval($_GET['qty'] ?? 1);

        if ($id > 0) {
            if (isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id]['qty'] += $qty;
            } else {
                $_SESSION['cart'][$id] = [
                    'qty' => $qty,
                    'selected' => true
                ];
            }
            setFlash('success', 'Producto añadido al carrito.');
        }

        redirect('?route=home');
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ids = $_POST['ids'] ?? [];
            $qtys = $_POST['qtys'] ?? [];
            $selected = $_POST['selected'] ?? [];

            foreach ($_SESSION['cart'] as $id => &$item) {
                if (isset($qtys[$id])) {
                    $item['qty'] = max(1, intval($qtys[$id]));
                }
                $item['selected'] = isset($selected[$id]);
            }
        }
        redirect('?route=cart');
    }

    public function remove() {
        $id = intval($_GET['id'] ?? 0);
        if (isset($_SESSION['cart'][$id])) {
            unset($_SESSION['cart'][$id]);
            setFlash('success', 'Producto eliminado del carrito.');
        }
        redirect('?route=cart');
    }

    public function toggle() {
        $id = intval($_GET['id'] ?? 0);
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['selected'] = !$_SESSION['cart'][$id]['selected'];
        }
        redirect('?route=cart');
    }
}

