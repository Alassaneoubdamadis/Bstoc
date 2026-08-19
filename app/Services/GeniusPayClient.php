<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeniusPayClient
{
    public function createCheckout(array $payload): array
    {
        $response = Http::withHeaders($this->headers())
            ->acceptJson()
            ->post($this->url('/payments'), $payload);

        if (! $response->successful() || ! $response->json('success')) {
            $message = $response->json('error.message') ?: 'Impossible d’initier le paiement GeniusPay.';
            throw new RuntimeException($message);
        }

        return $response->json('data') ?: [];
    }

    public function getPayment(string $reference): array
    {
        $response = Http::withHeaders($this->headers())
            ->acceptJson()
            ->get($this->url('/payments/'.$reference));

        if (! $response->successful() || ! $response->json('success')) {
            $message = $response->json('error.message') ?: 'Paiement GeniusPay introuvable.';
            throw new RuntimeException($message);
        }

        return $response->json('data') ?: [];
    }

    public function isConfigured(): bool
    {
        return filled(config('geniuspay.api_key')) && filled(config('geniuspay.api_secret'));
    }

    private function headers(): array
    {
        return [
            'X-API-Key' => (string) config('geniuspay.api_key'),
            'X-API-Secret' => (string) config('geniuspay.api_secret'),
            'Content-Type' => 'application/json',
        ];
    }

    private function url(string $path): string
    {
        return rtrim((string) config('geniuspay.base_url'), '/').$path;
    }
}
