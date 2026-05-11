<?php
namespace App\Interfaces;

interface UserRepositoryInterface {
    public function findByUsername(string $username);
    public function findById(int $id);
    public function updateLastLogin(int $userId): void;
}
