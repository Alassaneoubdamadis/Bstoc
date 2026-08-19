<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePlatformAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user || empty($user->is_platform_admin)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Accès plateforme refusé.'], 403);
            }

            return redirect()->route('platform.login');
        }

        return $next($request);
    }
}
