<?php
namespace App\Interfaces;

interface CheckoutServiceInterface {
    public function processSale(array $saleData, array $items): int;
}
