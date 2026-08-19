<?php

namespace App\Services;

use App\Models\Company;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;

class SubscriptionActivator
{
    public function activateFromMetadata(array $metadata, ?string $reference = null, array $payload = []): void
    {
        $companyId = (int) ($metadata['company_id'] ?? 0);
        $planId = (int) ($metadata['plan_id'] ?? 0);
        if ($companyId < 1 || $planId < 1) {
            return;
        }

        $company = Company::withoutGlobalScopes()->find($companyId);
        $plan = SubscriptionPlan::query()->find($planId);
        if (! $company || ! $plan) {
            return;
        }

        $company->activatePlan($plan);

        if ($reference) {
            SubscriptionPayment::query()
                ->where('genius_reference', $reference)
                ->update([
                    'status' => 'completed',
                    'payload' => $payload,
                ]);
        }
    }
}
