<?php
class LoyaltyController {
    public function index() {
        if (!Auth::hasPermission('view_loyalty')) {
            setFlash('error', 'No tienes permiso para ver el programa de fidelización.');
            redirect('?route=dashboard');
        }
        $loyaltyModel = new LoyaltyProgram();
        $clientModel = new Client();
        $data = [
            'topClients' => $loyaltyModel->getTopLoyaltyClients(10),
            'totalIssued' => $loyaltyModel->getTotalPointsIssued(),
            'totalRedeemed' => $loyaltyModel->getTotalPointsRedeemed(),
            'totalClients' => $clientModel->getTotalClients(),
        ];
        $pageTitle = 'Programa de Fidelización';
        $currentRoute = 'loyalty';
        $content = APP_ROOT . '/views/loyalty/index.php';
        include APP_ROOT . '/views/layouts/main.php';
    }

    public function redeem() {
        if (!Auth::hasPermission('view_loyalty')) redirect('?route=loyalty');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('?route=loyalty');
        $clientId = intval($_POST['client_id'] ?? 0);
        $points = intval($_POST['points'] ?? 0);
        $description = sanitize($_POST['description'] ?? 'Canje de puntos');

        $loyaltyModel = new LoyaltyProgram();
        if ($loyaltyModel->redeemPoints($clientId, $points, $description)) {
            setFlash('success', "Se canjearon {$points} puntos exitosamente.");
        } else {
            setFlash('error', 'Puntos insuficientes para el canje.');
        }
        redirect('?route=loyalty');
    }

    public function addBonus() {
        if (!Auth::hasPermission('view_loyalty')) redirect('?route=loyalty');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('?route=loyalty');
        $clientId = intval($_POST['client_id'] ?? 0);
        $points = intval($_POST['points'] ?? 0);
        $description = sanitize($_POST['description'] ?? 'Bono especial');

        $loyaltyModel = new LoyaltyProgram();
        $loyaltyModel->addTransaction($clientId, null, $points, 'bonus', $description);
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE clients SET loyalty_points = loyalty_points + ? WHERE id = ?");
        $stmt->execute([$points, $clientId]);

        setFlash('success', "Se otorgaron {$points} puntos de bonificación.");
        redirect('?route=loyalty');
    }
}

