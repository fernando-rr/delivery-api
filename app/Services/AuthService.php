<?php

namespace App\Services;

use App\DTOs\LoginDTO;
use App\DTOs\UserDTO;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Realiza login e retorna token + dados do usuário
     *
     * @return array ['token' => string, 'user' => UserDTO]
     *
     * @throws ValidationException
     */
    public function login(LoginDTO $dto): array
    {
        $user = User::where('email', $dto->email)->first();

        if (!$user || !Hash::check($dto->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas estão incorretas.'],
            ]);
        }

        if (!$user->active) {
            throw ValidationException::withMessages([
                'email' => ['Usuário inativo. Entre em contato com o administrador.'],
            ]);
        }

        // Remove todos os tokens anteriores
        $user->tokens()->delete();

        // Cria novo token
        $token = $user->createToken('auth_token')->plainTextToken;

        $userDTO = new UserDTO($user->toArray());

        return [
            'token' => $token,
            'user' => $userDTO,
        ];
    }

    /**
     * Retorna dados do usuário autenticado
     */
    public function me(User $user): UserDTO
    {
        return new UserDTO($user->toArray());
    }

    /**
     * Renova o token de autenticação
     *
     * @return string Novo token
     */
    public function refresh(User $user): string
    {
        // Remove o token atual
        /** @var \Laravel\Sanctum\PersonalAccessToken|null $currentToken */
        $currentToken = $user->currentAccessToken();
        if ($currentToken) {
            $currentToken->delete();
        }

        // Cria novo token
        return $user->createToken('auth_token')->plainTextToken;
    }

    /**
     * Realiza logout (revoga o token atual)
     */
    public function logout(User $user): void
    {
        /** @var \Laravel\Sanctum\PersonalAccessToken|null $currentToken */
        $currentToken = $user->currentAccessToken();
        if ($currentToken) {
            $currentToken->delete();
        }
    }
}
