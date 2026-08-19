<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\CompanyProvisioner;
use App\Support\FeatureCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::with(['plan', 'owner'])->orderByDesc('id')->paginate(20);

        return view('platform.companies.index', compact('companies'));
    }

    public function create()
    {
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();

        return view('platform.companies.create', array_merge(compact('plans'), $this->permissionFormData()));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'owner_first_name' => 'required|string|max:100',
            'owner_last_name' => 'required|string|max:100',
            'owner_email' => 'required|email|unique:users,email',
            'owner_password' => 'required|string|min:6',
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $plan = SubscriptionPlan::findOrFail($data['subscription_plan_id']);

        $company = Company::startTrial([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'city' => $data['city'] ?? 'Abidjan',
            'country' => $data['country'] ?? "Côte d'Ivoire",
            'allowed_permissions' => array_values($request->input('permissions', [])),
        ], $plan);

        $owner = User::withoutGlobalScopes()->create([
            'first_name' => $data['owner_first_name'],
            'last_name' => $data['owner_last_name'],
            'email' => $data['owner_email'],
            'password' => Hash::make($data['owner_password']),
            'language' => 'fr',
            'status' => 1,
            'company_id' => $company->id,
            'is_platform_admin' => false,
        ]);
        $owner->assignRole('admin');

        $company->update(['owner_user_id' => $owner->id]);

        app(CompanyProvisioner::class)->seedDefaults($company->id, [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? '',
            'city' => $data['city'] ?? 'Abidjan',
        ]);

        return redirect()->route('platform.companies.index')->with('success', 'Magasin créé avec un essai Starter de 14 jours.');
    }

    public function edit(Company $company)
    {
        $plans = SubscriptionPlan::orderBy('sort_order')->get();

        return view('platform.companies.edit', array_merge(compact('company', 'plans'), $this->permissionFormData($company)));
    }

    public function update(Request $request, Company $company)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'status' => 'required|in:trialing,active,past_due,canceled,expired',
            'is_suspended' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'trial_ends_at' => 'nullable|date',
            'subscription_ends_at' => 'nullable|date',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ]);

        $data['is_suspended'] = $request->boolean('is_suspended');
        $data['allowed_permissions'] = array_values($request->input('permissions', []));
        unset($data['permissions']);
        $company->update($data);

        return redirect()->route('platform.companies.index')->with('success', 'Magasin mis à jour.');
    }

    public function toggleSuspend(Company $company)
    {
        $company->update(['is_suspended' => ! $company->is_suspended]);

        return back()->with('success', $company->is_suspended ? 'Magasin suspendu.' : 'Magasin réactivé.');
    }

    private function permissionFormData(?Company $company = null): array
    {
        $names = Permission::query()->orderBy('name')->pluck('name')->all();
        $options = [];
        foreach ($names as $name) {
            $options[$name] = FeatureCatalog::label($name);
        }
        asort($options, SORT_NATURAL | SORT_FLAG_CASE);

        return [
            'permissionOptions' => $options,
            'allPermissionNames' => $names,
            'selectedPermissions' => $company?->allowed_permissions ?? $names,
        ];
    }
}
