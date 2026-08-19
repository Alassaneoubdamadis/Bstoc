<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;

class EnsureSubscriptionActive
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('api/subscription', 'api/subscription/*', 'api/geniuspay/*')) {
            return $next($request);
        }

        $user = $request->user();
        if (! $user || $user->is_platform_admin) {
            return $next($request);
        }

        if (empty($user->company_id)) {
            if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
                return $next($request);
            }

            return response()->json([
                'success' => false,
                'message' => 'Abonnement inactif ou essai terminé. Contactez B-Stock pour réactiver votre magasin.',
                'subscription_blocked' => true,
            ], 402);
        }

        $company = Company::withoutGlobalScopes()->find($user->company_id);
        if ($company && $company->hasAccess()) {
            return $next($request);
        }

        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'Abonnement inactif ou essai terminé. Contactez B-Stock pour réactiver votre magasin.',
            'subscription_blocked' => true,
        ], 402);
    }
}
