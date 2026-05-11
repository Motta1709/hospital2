<?php
namespace App\Interfaces;

interface SaleRepositoryInterface extends BaseRepositoryInterface {
    public function create(array $data, array $items): int;
    public function findByInvoice(string $invoiceNumber);
    public function getItems(int $saleId): array;
}
