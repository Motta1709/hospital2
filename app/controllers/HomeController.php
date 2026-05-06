<?php
/**
 * PharmaCRM - HomeController
 */
class HomeController {
    private $productModel;

    public function __construct() {
        $this->productModel = new Product();
    }

    public function index() {
        $search = $_GET['search'] ?? '';
        $category = $_GET['category'] ?? null;
        $page = $_GET['page'] ?? 1;
        
        $products = $this->productModel->getAll($page, 12, $search, $category);
        $totalProducts = $this->productModel->count($search, $category);
        $categories = $this->productModel->getCategories();
        
        $totalPages = ceil($totalProducts / 12);

        include APP_ROOT . '/views/home.php';
    }
}

