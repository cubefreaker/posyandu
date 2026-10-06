<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            $route = Auth::user()->role === 'kader' ? 'pelayanan.index' : 'dashboard';
            return redirect()->route($route);
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'role'     => 'nullable|string|in:admin,kader',
        ]);

        $attemptData = [
            'username' => $credentials['username'],
            'password' => $credentials['password'],
        ];

        if (!empty($credentials['role'])) {
            $attemptData['role'] = $credentials['role'];
        }

        if (Auth::attempt($attemptData, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $route = Auth::user()->role === 'kader' ? 'pelayanan.index' : 'dashboard';
            return redirect()->intended(route($route));
        }

        return back()->withErrors([
            'username' => !empty($credentials['role'])
                ? 'Username, password, atau role tidak sesuai.'
                : 'Username atau password salah.',
        ])->onlyInput('username', 'role');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
