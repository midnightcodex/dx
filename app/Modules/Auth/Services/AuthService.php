<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function login(string $email, string $password): ?array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !$user->is_active || !Hash::check($password, $user->password)) {
            return null;
        }

        $user->forceFill(['last_login_at' => now()])->save();

        $token = app(JwtService::class)->issueAccessToken($user);

        $user->load(['roles.permissions', 'organization']);

        return [
            'access_token' => $token['token'],
            'token_type' => 'Bearer',
            'expires_in' => $token['expires_in'],
            'user' => $user,
        ];
    }

    public function logout(User $user): void
    {
        $user->token_version = (int) ($user->token_version ?? 1) + 1;
        $user->save();
    }

    public function loadUser(User $user): User
    {
        return $user->load(['roles.permissions', 'organization']);
    }
}
