<?php
namespace App\Interfaces;

interface AuthServiceInterface {
    public function authenticate(string $username, string $password): ?array;
    public function login(array $user): void;
    public function logout(): void;
    public function check(): bool;
    public function user(): ?array;
}
