<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * 管理画面アクセス制御（SEC-002/004 / ADM-001）。
 * 認証必須・有効アカウントのみ。role 指定があればロールも検証する。
 */
class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next, ?string $roles = null): Response
    {
        $user = Auth::user();

        if (! $user || ! $user->is_active) {
            Auth::logout();

            return redirect()->route('admin.login')->withErrors([
                'email' => 'ログインが必要です。',
            ]);
        }

        if ($roles) {
            $allowed = explode('|', $roles);
            abort_unless(in_array($user->role, $allowed, true), 403, '権限がありません。');
        }

        return $next($request);
    }
}
