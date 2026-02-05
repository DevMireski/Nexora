<?php
namespace App\Http\Controllers\Api\V1;

use App\DTOs\AuthDTO;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;

class AuthController extends BaseApiController
{
    public function __construct(protected AuthService $authService)
    {
    }

    public function login(LoginRequest $request)
    {
        $token = $this->authService->login(AuthDTO::fromRequest($request));

        return $this->successResponse(
            ['token' => $token, 'user' => auth('api')->user()],
            'Login realizado com sucesso'
        );
    }

    public function me()
    {
        return $this->successResponse($this->authService->me());
    }

    public function logout()
    {
        $this->authService->logout();

        return $this->successResponse(null, 'Logout realizado com sucesso');
    }

    public function refresh()
    {
        $token = $this->authService->refresh();

        return $this->successResponse(['token' => $token], 'Token atualizado com sucesso');
    }
}
