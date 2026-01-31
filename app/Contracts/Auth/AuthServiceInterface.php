<?php

namespace App\Contracts\Auth;

interface AuthServiceInterface
{
    /**
     * Register a new user
     * @param array $data
     * @return array ['user' => UserResource, 'token' => string]
     * @throws \Exception
     */
    public function register(array $data): array;

    /**
     * Login a user
     * @param array $data
     * @return array ['user' => UserResource, 'token' => string]
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(array $data): array;

    /**
     * Logout a user
     * @param mixed $user
     * @return bool
     */
    public function logout($user): bool;
}
