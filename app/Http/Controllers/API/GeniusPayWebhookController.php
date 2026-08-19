<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\SubscriptionActivator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeniusPayWebhookController extends Controller
{
    public function handle(Request $request, SubscriptionActivator $activator): JsonResponse
    {
        $secret = (string) config('geniuspay.webhook_secret');
        if ($secret !== '') {
            $signature = (string) $request->header('X-Webhook-Signature');
            $timestamp = (string) $request->header('X-Webhook-Timestamp');
            if ($signature === '' || $timestamp === '') {
                return response()->json(['status' => 401, 'detail' => 'Missing signature'], 401);
            }
            if (abs(time() - (int) $timestamp) > 300) {
                return response()->json(['status' => 400, 'detail' => 'Timestamp too old'], 400);
            }
            $expected = hash_hmac('sha256', $timestamp.'.'.$request->getContent(), $secret);
            if (! hash_equals($expected, $signature)) {
                return response()->json(['status' => 401, 'detail' => 'Invalid signature'], 401);
            }
        } elseif (app()->environment('production')) {
            return response()->json(['status' => 401, 'detail' => 'Webhook secret missing'], 401);
        }

        $event = $request->header('X-Webhook-Event') ?: $request->input('event');
        if ($event === 'payment.success') {
            $data = $request->input('data', []);
            $activator->activateFromMetadata(
                $data['metadata'] ?? [],
                $data['reference'] ?? null,
                $request->all()
            );
        }

        return response()->json(['ok' => true]);
    }
}
