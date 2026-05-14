<?php
namespace App\Interfaces;

interface BaseRepositoryInterface {
    public function findById(int $id);
    public function all(array $filters = []);
    public function delete(int $id);
}
