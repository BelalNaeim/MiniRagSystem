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
    public function register(string $model, array $data): array
    {
        DB::beginTransaction();
        try {
            $user = $model::create($data);

            DB::commit();

            return [
                'key' => 'success',
                'msg' => __('auth.registered'),
                'user' => $this->getResource($model, $user, $user->login()),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'key' => 'fail',
                'msg' => $e->getMessage(),
            ];
        }
    }

    public function login(string $model, array $data): array
    {
        $user = $model::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return [
                'key' => 'fail',
                'msg' => __(key: 'auth.incorrect_key_or_phone'),
                'user' => []
            ];
        }

        return [
            'key' => 'success',
            'msg' => __('auth.login'),
            'user' => $this->getResource($model, $user, $user->login()),
        ];
    }

    protected function getResource(string $model, $user, string $token = null)
    {
        return match($model) {
            User::class => UserResource::make($user->refresh())->setToken($token),
            default => $user,
        };
    }

    public function logout(string $model, $user): bool
    {
        return $user->tokens()->delete();
    }
}
