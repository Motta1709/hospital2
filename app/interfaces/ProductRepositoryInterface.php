<?php
namespace App\Interfaces;

interface ProductRepositoryInterface extends BaseRepositoryInterface {
    public function search(string $query, ?int $branchId = null);
    public function getByCategory(int $categoryId, ?int $branchId = null);
    public function updateStock(int $id, int $quantity, ?int $branchId = null): bool;
    public function getExpiring(int $days, ?int $branchId = null);
}
