<?php
class DashboardController {
    public function index() {
        $saleModel = new Sale();
        $productModel = new Product();
        $clientModel = new Client();

        $data = [
            'todaySales' => $saleModel->getTodaySales(),
            'monthSales' => $saleModel->getMonthSales(),
            'totalProducts' => $productModel->getTotalProducts(),
            'totalClients' => $clientModel->getTotalClients(),
            'stockValue' => $productModel->getTotalStockValue(),
            'expiringProducts' => $productModel->getExpiringProducts(30),
            'lowStockProducts' => $productModel->getLowStockProducts(),
            'recentSales' => $saleModel->getRecentSales(5),
            'topProducts' => $productModel->getTopProducts(5),
            'dailySales' => $saleModel->getDailySalesChart(7),
            'categoryDistribution' => $productModel->getCategoryDistribution(),
        ];

        $pageTitle = 'Dashboard';
        $currentRoute = 'dashboard';
        $content = APP_ROOT . '/views/dashboard/index.php';
        include APP_ROOT . '/views/layouts/main.php';
    }

    public function getStats() {
        $saleModel = new Sale();
        $productModel = new Product();
        jsonResponse([
            'todaySales' => $saleModel->getTodaySales(),
            'monthSales' => $saleModel->getMonthSales(),
            'dailySales' => $saleModel->getDailySalesChart(7),
        ]);
    }
}
