<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Http\Controllers\Controller;
use App\Contracts\Auth\AuthServiceInterface;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ResponseTrait;

    protected AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->register($request->validated());
            return $this->response('success', __('auth.registered'), $result['user']);
        } catch (\Exception $e) {
            return $this->response('fail', $e->getMessage());
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->validated());
            return $this->response('success', __('auth.login'), $result['user']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->response('fail', $e->getMessage());
        }
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());
        return $this->response('success', __('auth.logout'));
    }
}
