<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('platform_settings')) {
            Schema::create('platform_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        if (DB::table('platform_settings')->count() === 0) {
            DB::table('platform_settings')->insert([
                ['key' => 'app_name', 'value' => 'B-Stock', 'created_at' => now(), 'updated_at' => now()],
                ['key' => 'logo_path', 'value' => '', 'created_at' => now(), 'updated_at' => now()],
                ['key' => 'favicon_path', 'value' => '', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (! Schema::hasTable('subscription_payments')) {
            Schema::create('subscription_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('plan_id')->nullable()->index();
                $table->unsignedInteger('amount')->default(0);
                $table->string('currency', 8)->default('XOF');
                $table->string('genius_reference')->nullable()->index();
                $table->string('status')->default('pending');
                $table->text('checkout_url')->nullable();
                $table->json('payload')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
        Schema::dropIfExists('platform_settings');
    }
};
