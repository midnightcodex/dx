<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Modules\Auth\Models\User;

class AuthController extends Controller
{
    /**
     * Handle an authentication attempt.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user()->load(['roles.permissions', 'organization']);

            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'token' => $user->createToken('api_token')->plainTextToken, // Assuming Sanctum
            ]);
        }

        return response()->json([
            'message' => 'The provided credentials do not match our records.',
        ], 401);
    }

    /**
     * Handle an authentication attempt for Web (Inertia).
     */
    public function loginWeb(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Log the user out (Web).
     */
    public function logoutWeb(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Get the authenticated user.
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load(['roles.permissions', 'organization']),
        ]);
    }

    /**
     * Log the user out (Invalidate the token).
     */
    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();

        Auth::guard('web')->logout();

        return response()->json(['message' => 'Logged out successfully']);
    }
    /**
     * Handle registration for Web.
     */
    public function registerWeb(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:auth.users'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        // For demo purposes, assign to the first organization
        $org = \App\Modules\Shared\Models\Organization::first();

        if (!$org) {
            return back()->withErrors(['email' => 'No organization found. Please seed the database first.']);
        }

        $user = User::create([
            'organization_id' => $org->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'is_active' => true,
        ]);

        // Assign default role (e.g., VIEWER or ADMIN depending on logic, let's say ADMIN for demo simplicity)
        // Check if Role exists first
        $role = \App\Modules\Auth\Models\Role::where('slug', 'ADMIN')->first();
        if ($role) {
            $user->roles()->attach($role->id);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended('dashboard');
    }

    /**
     * Handle forgot password for Web.
     */
    public function forgotPasswordWeb(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // In a real app, we would send the email here.
        // For this demo, we will just simulate success.

        return back()->with('status', 'We have emailed your password reset link! (Simulation)');
    }
}
