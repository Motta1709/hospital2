<?php
namespace App\Services;

use App\Interfaces\CheckoutServiceInterface;
use App\Interfaces\SaleRepositoryInterface;
use App\Interfaces\ProductRepositoryInterface;
use Exception;

class CheckoutService implements CheckoutServiceInterface {
    private $saleRepository;
    private $productRepository;

    public function __construct(
        SaleRepositoryInterface $saleRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->saleRepository = $saleRepository;
        $this->productRepository = $productRepository;
    }

    public function processSale(array $saleData, array $items): int {
        // 1. Validaciones de Negocio
        if (empty($items)) {
            throw new Exception("La venta debe tener al menos un producto.");
        }

        // 2. Verificar Stock antes de procesar
        foreach ($items as $item) {
            $product = $this->productRepository->findById($item['product_id']);
            if (!$product || $product['stock'] < $item['quantity']) {
                throw new Exception("Stock insuficiente para el producto: " . ($product['name'] ?? 'ID ' . $item['product_id']));
            }
        }

        // 3. Crear la Venta
        $saleId = $this->saleRepository->create($saleData, $items);

        // 4. Actualizar Inventario (Disminuir stock)
        foreach ($items as $item) {
            // Aquí podríamos llamar a un InventoryService para manejar Kardex, lotes, etc.
            $this->productRepository->updateStock($item['product_id'], -$item['quantity']);
        }

        // 5. Gestión de Lealtad (Opcional: Inyectar LoyaltyService)
        if (!empty($saleData['client_id']) && $saleData['loyalty_points_earned'] > 0) {
            $this->updateClientLoyalty($saleData['client_id'], $saleData['loyalty_points_earned']);
        }

        return $saleId;
    }

    private function updateClientLoyalty(int $clientId, int $points): void {
        // En un refactor completo, esto iría en un LoyaltyService
        $db = \Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE clients SET loyalty_points = loyalty_points + ? WHERE id = ?");
        $stmt->execute([$points, $clientId]);
    }
}
