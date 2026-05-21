<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    private const MAX_LOGIN_ATTEMPTS = 3;
    private const LOGIN_LOCKOUT_SECONDS = 600;

    /**
     * Show the admin entry page.
     */
    public function adminEntry(Request $request)
    {
        if (! Auth::check()) {
            return $this->showLoginForm();
        }

        if ($request->user()?->role !== 'administrator') {
            abort(403);
        }

        return app(AdminDashboardController::class)->index();
    }

    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle the login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required',
        ]);

        $rateLimitKey = $this->loginRateLimitKey($request);

        if (RateLimiter::tooManyAttempts($rateLimitKey, self::MAX_LOGIN_ATTEMPTS)) {
            throw ValidationException::withMessages([
                'username' => [__('messages.auth.login_locked')],
            ]);
        }

        $username = $request->input('username');
        $password = $request->input('password');
        $remember = $request->boolean('remember');

        $credentials = ['name' => $username, 'password' => $password];

        if (Auth::attempt($credentials, $remember)) {
            RateLimiter::clear($rateLimitKey);
            $request->session()->regenerate();

            return redirect('/admin');
        }

        RateLimiter::hit($rateLimitKey, self::LOGIN_LOCKOUT_SECONDS);

        throw ValidationException::withMessages([
            'username' => ['The provided credentials do not match our records.'],
        ]);
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin');
    }

    private function loginRateLimitKey(Request $request): string
    {
        return 'admin-login-attempts:'.$request->ip();
    }
}
