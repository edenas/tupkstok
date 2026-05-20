<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
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
            'login_identifier' => 'required|string',
            'password' => 'required',
        ]);

        $loginIdentifier = $request->input('login_identifier');
        $password = $request->input('password');
        $remember = $request->boolean('remember');

        // Check if the login identifier is a valid email
        if (filter_var($loginIdentifier, FILTER_VALIDATE_EMAIL)) {
            // Try to authenticate with email
            $credentials = ['email' => $loginIdentifier, 'password' => $password];
        } else {
            // Try to authenticate with name (username)
            $credentials = ['name' => $loginIdentifier, 'password' => $password];
        }

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended('/admin');
        }

        throw ValidationException::withMessages([
            'login_identifier' => ['The provided credentials do not match our records.'],
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

        return redirect()->route('login');
    }
}
