<?php

namespace Tests\Feature;

use Tests\TestCase;

class PreDeploySecurityTest extends TestCase
{
    public function test_home_page_loads(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_platform_login_page_loads(): void
    {
        $this->get('/platform/login')->assertOk();
    }

    public function test_platform_dashboard_requires_auth(): void
    {
        $response = $this->get('/platform');
        $this->assertTrue(in_array($response->status(), [401, 302], true));
    }

    public function test_unauthenticated_api_is_rejected(): void
    {
        $this->getJson('/api/products')->assertUnauthorized();
    }

    public function test_upgrade_route_is_closed(): void
    {
        $this->get('/upgrade-to-v1-2-0')->assertNotFound();
        $this->get('/upgrade/database')->assertNotFound();
    }

    public function test_dotenv_editor_is_disabled(): void
    {
        $this->get('/admin/env')->assertNotFound();
    }

    public function test_public_register_is_disabled(): void
    {
        $this->postJson('/api/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'audit-register@example.com',
            'password' => '123456',
        ])->assertStatus(403);
    }

    public function test_platform_admin_cannot_login_to_pos_api(): void
    {
        $this->postJson('/api/login', [
            'email' => 'platform@bstock.ci',
            'password' => '123456',
        ])->assertStatus(403);
    }

    public function test_shop_admin_can_login_to_pos_api(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'admin@bstock.ci',
            'password' => '123456',
        ]);

        $response->assertOk()->assertJsonPath('message', 'Logged in successfully.');
        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_invalid_login_is_rejected(): void
    {
        $this->postJson('/api/login', [
            'email' => 'nobody@example.com',
            'password' => 'wrong',
        ])->assertStatus(422);
    }

    public function test_cors_is_not_wildcard(): void
    {
        $this->assertNotContains('*', config('cors.allowed_origins'));
    }

    public function test_branding_page_requires_auth(): void
    {
        $response = $this->get('/platform/branding');
        $this->assertTrue(in_array($response->status(), [401, 302], true));
    }

    public function test_geniuspay_webhook_activates_subscription(): void
    {
        $company = \App\Models\Company::withoutGlobalScopes()->first();
        $plan = \App\Models\SubscriptionPlan::query()->first();
        $this->assertNotNull($company);
        $this->assertNotNull($plan);

        $this->postJson('/api/geniuspay/webhook', [
            'event' => 'payment.success',
            'data' => [
                'reference' => 'MTX-TEST',
                'status' => 'completed',
                'metadata' => [
                    'company_id' => (string) $company->id,
                    'plan_id' => (string) $plan->id,
                ],
            ],
        ], [
            'X-Webhook-Event' => 'payment.success',
        ])->assertOk();

        $company->refresh();
        $this->assertTrue($company->hasAccess());
        $this->assertSame('active', $company->status);
    }
}
