<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::hasUser() && Auth::user()?->is_platform_admin) {
            return redirect()->route('platform.dashboard');
        }

        return view('platform.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::withoutGlobalScopes()->whereRaw('lower(email) = ?', [strtolower($request->email)])->first();

        if (! $user || ! Hash::check($request->password, $user->password) || empty($user->is_platform_admin)) {
            return back()->withErrors(['email' => 'Identifiants plateforme invalides.'])->withInput();
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('platform.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('platform.login');
    }
}
