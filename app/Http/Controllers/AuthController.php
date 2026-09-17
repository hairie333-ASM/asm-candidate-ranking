<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $throttleKey = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        $loginInput = trim($request->input('email'));
        $resolvedUser = User::where('email', $loginInput)
            ->orWhere('username', $loginInput)
            ->orWhere('email', $loginInput.'@example.test')
            ->first();

        $credentials = [
            'email' => $resolvedUser ? $resolvedUser->email : $loginInput,
            'password' => $request->input('password'),
        ];
        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            RateLimiter::hit($throttleKey, 60);

            $this->auditService->log(
                action: 'Failed Login',
                recordType: 'User',
                description: "Failed login attempt for username/email: {$request->input('email')}"
            );

            return back()->withInput($request->only('email', 'remember'))->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        /** @var User $user */
        $user = Auth::user();

        if (! $user->active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $this->auditService->log(
                action: 'Login Blocked: Deactivated Account',
                recordType: 'User',
                recordId: $user->id,
                description: "Deactivated user {$user->email} attempted login."
            );

            return redirect()->route('login')->withErrors([
                'email' => 'Your account is deactivated. Please contact the administrator.',
            ]);
        }

        $user->update(['last_login' => now()]);
        $request->session()->regenerate();

        $this->auditService->log(
            action: 'Login',
            recordType: 'User',
            recordId: $user->id,
            description: "User {$user->email} logged in successfully.",
            user: $user
        );

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            $this->auditService->log(
                action: 'Logout',
                recordType: 'User',
                recordId: $user->id,
                description: "User {$user->email} logged out.",
                user: $user
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'You have been logged out securely.');
    }
}
