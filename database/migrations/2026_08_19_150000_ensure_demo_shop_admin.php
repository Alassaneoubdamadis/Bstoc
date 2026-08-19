<?php

use App\Models\Setting;
use App\Models\User;
use App\Services\CompanyProvisioner;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('email', 'platform@bstock.ci')->update([
            'is_platform_admin' => true,
            'company_id' => null,
        ]);

        $companyId = DB::table('companies')->orderBy('id')->value('id');
        if (! $companyId) {
            return;
        }

        $shop = User::withoutGlobalScopes()
            ->whereIn('email', ['admin@bstock.ci', 'admin@infy-pos.com'])
            ->first();

        if ($shop) {
            $shop->forceFill([
                'email' => 'admin@bstock.ci',
                'password' => Hash::make('123456'),
                'status' => 1,
                'language' => $shop->language ?: 'fr',
                'company_id' => $companyId,
                'is_platform_admin' => false,
            ])->save();
        } else {
            $shop = User::withoutGlobalScopes()->create([
                'first_name' => 'Admin',
                'last_name' => 'Magasin',
                'email' => 'admin@bstock.ci',
                'password' => Hash::make('123456'),
                'language' => 'fr',
                'status' => 1,
                'company_id' => $companyId,
                'is_platform_admin' => false,
                'email_verified_at' => now(),
            ]);
        }

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole && ! $shop->hasRole($adminRole)) {
            $shop->assignRole($adminRole);
        }

        DB::table('companies')->where('id', $companyId)->update([
            'owner_user_id' => $shop->id,
            'email' => 'admin@bstock.ci',
        ]);

        $hasSettings = Setting::withoutGlobalScopes()->where('company_id', $companyId)->exists();
        if (! $hasSettings) {
            app(CompanyProvisioner::class)->seedDefaults((int) $companyId, [
                'name' => 'Magasin démo',
                'email' => 'admin@bstock.ci',
                'city' => 'Abidjan',
            ]);
        }
    }

    public function down(): void
    {
        //
    }
};
