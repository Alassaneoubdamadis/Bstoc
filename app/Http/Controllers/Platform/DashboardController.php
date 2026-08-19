<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\SubscriptionPlan;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $companies = Company::with('plan')->orderByDesc('id')->get();

        $stats = [
            'total' => $companies->count(),
            'trialing' => $companies->where('status', Company::STATUS_TRIALING)->count(),
            'active' => $companies->where('status', Company::STATUS_ACTIVE)->count(),
            'suspended' => $companies->where('is_suspended', true)->count(),
            'blocked' => $companies->filter(fn (Company $c) => ! $c->hasAccess())->count(),
            'plans' => SubscriptionPlan::count(),
        ];

        return view('platform.dashboard', compact('stats', 'companies'));
    }
}
