<?php

namespace App\Providers;

use App\Modules\Auth\Models\User;
use App\Modules\Auth\Services\JwtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::viaRequest('jwt', function (Request $request) {
            $token = $request->bearerToken();
            if (!$token) {
                return null;
            }

            $jwt = app(JwtService::class);
            $parsed = $jwt->parseAccessToken($token);
            if (!$parsed) {
                return null;
            }

            $userId = $parsed->claims()->get('sub');
            $tokenVersion = (int) $parsed->claims()->get('tv', 1);

            $user = User::where('id', $userId)
                ->where('is_active', true)
                ->first();

            if (!$user) {
                return null;
            }

            if ((int) ($user->token_version ?? 1) !== $tokenVersion) {
                return null;
            }

            return $user;
        });
    }
}
