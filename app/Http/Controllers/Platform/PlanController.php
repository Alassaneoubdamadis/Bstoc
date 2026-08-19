<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::orderBy('sort_order')->orderBy('id')->get();

        return view('platform.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('platform.plans.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['features'] = $this->parseFeatures($request->input('features_text'));
        $data['sort_order'] = $data['sort_order'] ?? ((int) SubscriptionPlan::max('sort_order') + 1);
        SubscriptionPlan::create($data);

        return redirect()->route('platform.plans.index')->with('success', 'Offre créée.');
    }

    public function edit(SubscriptionPlan $plan)
    {
        return view('platform.plans.edit', compact('plan'));
    }

    public function update(Request $request, SubscriptionPlan $plan)
    {
        $data = $this->validated($request, $plan->id);
        $data['is_active'] = $request->boolean('is_active');
        $data['features'] = $this->parseFeatures($request->input('features_text'));
        $data['sort_order'] = $data['sort_order'] ?? $plan->sort_order ?? 0;
        $plan->update($data);

        return redirect()->route('platform.plans.index')->with('success', 'Offre mise à jour.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:100|unique:subscription_plans,slug,'.($id ?: 'NULL'),
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:8',
            'interval' => 'required|in:month,year',
            'trial_days' => 'required|integer|min:0|max:365',
            'sort_order' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ]);

        if ($data['sort_order'] === null) {
            unset($data['sort_order']);
        }

        return $data;
    }

    private function parseFeatures(?string $text): array
    {
        if (! $text) {
            return [];
        }

        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $text))));
    }
}
