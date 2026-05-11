<?php
namespace App\Interfaces;

interface BaseRepositoryInterface {
    public function findById(int $id);
    public function all();
    public function delete(int $id);
}
