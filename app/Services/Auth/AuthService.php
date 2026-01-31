<?php

namespace App\Services\Auth;

use App\Contracts\Auth\AuthServiceInterface;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService implements AuthServiceInterface
{
    public function register(array $data): array
    {
        DB::beginTransaction();
        try {
            $user = User::create($data);
            $token = $user->login();

            DB::commit();

            return [
                'user' => UserResource::make($user)->setToken($token),
                'token' => $token,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function login(array $data): array
    {
        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.incorrect_key_or_phone')],
            ]);
        }

        $token = $user->login();

        return [
            'user' => UserResource::make($user)->setToken($token),
            'token' => $token,
        ];
    }

    public function logout($user): bool
    {
        return $user->tokens()->delete();
    }
}
