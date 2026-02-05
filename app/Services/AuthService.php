<?php
namespace App\Services;

use App\DTOs\AuthDTO;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function login(AuthDTO $dto): string
    {
        $token = auth('api')->attempt([
            'email'    => $dto->email,
            'password' => $dto->password,
        ]);

        if (! $token) {
            // Mensagem genérica para prevenir enumeração de usuários
            throw ValidationException::withMessages([
                'email' => ['Credenciais inválidas.'],
            ]);
        }

        return $token;
    }

    public function me(): mixed
    {
        return auth('api')->user();
    }

    public function logout(): void
    {
        auth('api')->logout();
    }

    public function refresh(): string
    {
        return auth('api')->refresh();
    }
}
