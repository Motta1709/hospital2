<?php
class ReportController {
    private $reportModel;

    public function __construct() {
        if (!Auth::hasPermission('view_reports')) {
            if (isAjax()) jsonResponse(['error' => 'No autorizado'], 403);
            setFlash('error', 'No tienes permiso para ver los reportes.');
            redirect('?route=dashboard');
        }
        $this->reportModel = new Report();
    }

    public function index() {
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        $data = [
            'summary' => $this->reportModel->getSalesSummary($dateFrom, $dateTo),
            'salesByDay' => $this->reportModel->getSalesByDay($dateFrom, $dateTo),
            'salesByCategory' => $this->reportModel->getSalesByCategory($dateFrom, $dateTo),
            'inventoryValue' => $this->reportModel->getInventoryValue(),
            'expirationReport' => $this->reportModel->getExpirationReport(),
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ];
        $pageTitle = 'Reportes y Analítica';
        $currentRoute = 'reports';
        $content = APP_ROOT . '/views/reports/index.php';
        include APP_ROOT . '/views/layouts/main.php';
    }

    public function getData() {
        $type = $_GET['type'] ?? 'summary';
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');

        switch ($type) {
            case 'sales-daily': jsonResponse($this->reportModel->getSalesByDay($dateFrom, $dateTo)); break;
            case 'sales-category': jsonResponse($this->reportModel->getSalesByCategory($dateFrom, $dateTo)); break;
            case 'inventory': jsonResponse($this->reportModel->getInventoryValue()); break;
            default: jsonResponse($this->reportModel->getSalesSummary($dateFrom, $dateTo));
        }
    }

    public function salesReport() { $this->index(); }
    public function inventoryReport() { $this->index(); }
    public function clientsReport() { $this->index(); }
    public function export() { $this->index(); }
}
