<?php

namespace App\Contracts\Auth;

interface AuthServiceInterface
{
    public function register(string $model, array $data): array;
    public function login(string $model, array $data): array;
    public function logout(string $model, $user): bool;
}
