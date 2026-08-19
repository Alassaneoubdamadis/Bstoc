<?php

use App\Models\Company;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('price', 12, 2)->default(0);
            $table->string('currency', 8)->default('XOF');
            $table->string('interval')->default('month');
            $table->unsignedInteger('trial_days')->default(14);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('features')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->text('address')->nullable();
            $table->foreignId('subscription_plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
            $table->string('status')->default('trialing');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();
            $table->boolean('is_suspended')->default(false);
            $table->unsignedBigInteger('owner_user_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id');
                $table->boolean('is_platform_admin')->default(false)->after('company_id');
                $table->index('company_id');
            }
        });

        $tenantTables = [
            'warehouses', 'brands', 'product_categories', 'products', 'main_products',
            'customers', 'suppliers', 'sales', 'sale_items', 'sales_payments',
            'sales_return', 'sale_return_items', 'purchases', 'purchase_items',
            'purchases_return', 'purchases_return_items', 'expenses', 'expense_categories',
            'quotations', 'quotation_items', 'transfers', 'transfer_items',
            'adjustments', 'adjustment_items', 'manage_stocks', 'holds', 'hold_items',
            'settings', 'sms_settings', 'currencies', 'units', 'coupon_codes',
            'pos_register', 'variations', 'variation_types', 'variation_products',
            'mail_templates', 'sms_templates',
        ];

        foreach ($tenantTables as $tableName) {
            if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'company_id')) {
                continue;
            }
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }

        $starterId = DB::table('subscription_plans')->insertGetId([
            'name' => 'Starter',
            'slug' => 'starter',
            'price' => 0,
            'currency' => 'XOF',
            'interval' => 'month',
            'trial_days' => 14,
            'is_active' => true,
            'sort_order' => 1,
            'features' => json_encode(['caisse', 'stock', 'utilisateurs', 'rapports']),
            'description' => 'Plan de démarrage : caisse, stock, utilisateurs et rôles.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $companyId = DB::table('companies')->insertGetId([
            'name' => 'Magasin démo',
            'email' => 'admin@infy-pos.com',
            'country' => "Côte d'Ivoire",
            'city' => 'Abidjan',
            'subscription_plan_id' => $starterId,
            'status' => 'trialing',
            'trial_ends_at' => now()->addDays(14),
            'is_suspended' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->update(['company_id' => $companyId, 'is_platform_admin' => false]);

        $ownerId = DB::table('users')->where('email', 'admin@infy-pos.com')->value('id');
        if ($ownerId) {
            DB::table('companies')->where('id', $companyId)->update(['owner_user_id' => $ownerId]);
        }

        foreach ($tenantTables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'company_id')) {
                DB::table($tableName)->whereNull('company_id')->update(['company_id' => $companyId]);
            }
        }

        $platformExists = DB::table('users')->where('email', 'platform@bstock.ci')->exists();
        if (! $platformExists) {
            DB::table('users')->insert([
                'first_name' => 'Plateforme',
                'last_name' => 'Admin',
                'email' => 'platform@bstock.ci',
                'password' => Hash::make('123456'),
                'language' => 'fr',
                'status' => 1,
                'company_id' => null,
                'is_platform_admin' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('products')) {
            try {
                Schema::table('products', function (Blueprint $table) {
                    $table->dropUnique('products_code_unique');
                });
            } catch (\Throwable $e) {
                // index may not exist
            }
        }
        if (Schema::hasTable('product_categories')) {
            try {
                Schema::table('product_categories', function (Blueprint $table) {
                    $table->dropUnique('product_categories_name_unique');
                });
            } catch (\Throwable $e) {
                // index may not exist
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'company_id')) {
                $table->dropColumn(['company_id', 'is_platform_admin']);
            }
        });
        Schema::dropIfExists('companies');
        Schema::dropIfExists('subscription_plans');
    }
};
