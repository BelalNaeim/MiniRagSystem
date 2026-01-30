<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Contracts\Auth\AuthServiceInterface;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ResponseTrait;
    protected $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $this->authService->register(User::class, $request->validated());
        return $this->response($data['key'], $data['msg'], $data['user'] == [] ? [] : $data['user']);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $this->authService->login(User::class, $request->validated());

        return $this->response($data['key'], $data['msg'], $data['user'] == [] ? [] : $data['user']);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout(User::class, $request->user());
        return $this->response('success', __('apis.loggedOut'));
    }
}
