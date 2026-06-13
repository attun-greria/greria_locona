<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * 管理画面ログイン（ADM-001 / SEC-002）。
 */
class LoginController extends Controller
{
    public function show()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'メールアドレスまたはパスワードが正しくありません。',
            ]);
        }

        $user = Auth::user();
        if (! $user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'このアカウントは無効化されています。',
            ]);
        }

        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->save();
        Audit::log('login', $user);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        Audit::log('logout', $request->user());
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
