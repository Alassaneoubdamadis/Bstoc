<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Models\Company;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use App\Services\GeniusPayClient;
use App\Services\SubscriptionActivator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class SubscriptionController extends AppBaseController
{
    public function show(Request $request): JsonResponse
    {
        $company = $this->company($request);
        $plans = SubscriptionPlan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get(['id', 'name', 'price', 'currency', 'interval', 'trial_days', 'description', 'features']);

        return $this->sendResponse([
            'subscription' => company_subscription_snapshot($company),
            'plans' => $plans,
            'payment_ready' => app(GeniusPayClient::class)->isConfigured(),
        ], 'Abonnement récupéré.');
    }

    public function checkout(Request $request, GeniusPayClient $client): JsonResponse
    {
        $request->validate(['plan_id' => 'required|integer|exists:subscription_plans,id']);

        $company = $this->company($request);
        if (! $company) {
            return $this->sendError('Aucun magasin associé à ce compte.', 422);
        }

        $plan = SubscriptionPlan::query()->where('is_active', true)->findOrFail($request->integer('plan_id'));
        $user = $request->user();

        if ((float) $plan->price < 0.01) {
            $company->activatePlan($plan);

            return $this->sendResponse([
                'activated' => true,
                'checkout_url' => null,
                'subscription' => company_subscription_snapshot($company->fresh('plan')),
            ], 'Offre gratuite activée.');
        }

        if (! $client->isConfigured()) {
            return $this->sendError('Paiement GeniusPay non configuré. Ajoutez GENIUSPAY_API_KEY et GENIUSPAY_API_SECRET dans .env.', 422);
        }

        $amount = max(200, (int) round((float) $plan->price));
        $payment = SubscriptionPayment::query()->create([
            'company_id' => $company->id,
            'plan_id' => $plan->id,
            'amount' => $amount,
            'currency' => $plan->currency ?: 'XOF',
            'status' => 'pending',
        ]);

        $successUrl = rtrim((string) config('app.url'), '/').'/#/app/abonnement?paid=1';
        $errorUrl = rtrim((string) config('app.url'), '/').'/#/app/abonnement?paid=0';

        $customer = array_filter([
            'name' => trim($user->first_name.' '.$user->last_name) ?: $company->name,
            'email' => $user->email ?: $company->email,
            'phone' => $user->phone ?: $company->phone,
            'country' => 'CI',
        ]);

        try {
            $data = $client->createCheckout([
                'amount' => $amount,
                'currency' => $plan->currency ?: 'XOF',
                'description' => 'Abonnement '.$plan->name.' — '.$company->name,
                'customer' => $customer,
                'success_url' => $successUrl,
                'error_url' => $errorUrl,
                'metadata' => [
                    'company_id' => (string) $company->id,
                    'plan_id' => (string) $plan->id,
                    'user_id' => (string) $user->id,
                    'payment_id' => (string) $payment->id,
                    'product_name' => $plan->name,
                ],
            ]);
        } catch (RuntimeException $e) {
            $payment->update(['status' => 'failed', 'payload' => ['error' => $e->getMessage()]]);

            return $this->sendError($e->getMessage(), 422);
        }

        $checkoutUrl = $data['checkout_url'] ?? $data['payment_url'] ?? null;
        $payment->update([
            'genius_reference' => $data['reference'] ?? null,
            'checkout_url' => $checkoutUrl,
            'payload' => $data,
        ]);

        if (! $checkoutUrl) {
            return $this->sendError('GeniusPay n’a pas renvoyé d’URL de paiement.', 422);
        }

        return $this->sendResponse([
            'activated' => false,
            'checkout_url' => $checkoutUrl,
            'reference' => $data['reference'] ?? null,
        ], 'Redirection vers GeniusPay.');
    }

    public function verify(Request $request, GeniusPayClient $client, SubscriptionActivator $activator): JsonResponse
    {
        $reference = (string) $request->query('reference', '');
        if ($reference === '') {
            return $this->sendError('Référence manquante.', 422);
        }

        try {
            $data = $client->getPayment($reference);
        } catch (RuntimeException $e) {
            return $this->sendError($e->getMessage(), 422);
        }

        if (($data['status'] ?? '') === 'completed') {
            $activator->activateFromMetadata($data['metadata'] ?? [], $reference, $data);
        }

        $company = $this->company($request);

        return $this->sendResponse([
            'payment' => $data,
            'subscription' => company_subscription_snapshot($company?->fresh('plan')),
        ], 'Paiement vérifié.');
    }

    private function company(Request $request): ?Company
    {
        $user = $request->user();
        if (! $user?->company_id) {
            return null;
        }

        return Company::withoutGlobalScopes()->with('plan')->find($user->company_id);
    }
}
