<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePublicRegisterEnabled
{
    public function handle(Request $request, Closure $next)
    {
        if (! config('app.public_register')) {
            return response()->json([
                'success' => false,
                'message' => 'Inscription publique désactivée. Contactez B-Stock.',
            ], 403);
        }

        return $next($request);
    }
}
