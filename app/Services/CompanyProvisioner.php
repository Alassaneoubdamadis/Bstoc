<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\Customer;
use App\Models\Setting;
use App\Models\Unit;
use App\Models\Warehouse;

class CompanyProvisioner
{
    public function seedDefaults(int $companyId, array $meta = []): void
    {
        $currency = Currency::withoutGlobalScopes()->create([
            'name' => 'Franc CFA #'.$companyId,
            'code' => 'XOF',
            'symbol' => 'F',
            'company_id' => $companyId,
        ]);
        $warehouse = Warehouse::withoutGlobalScopes()->create([
            'name' => 'Principal',
            'phone' => '00000000',
            'country' => "Côte d'Ivoire",
            'city' => $meta['city'] ?? 'Abidjan',
            'email' => 'entrepot-'.$companyId.'@magasin.local',
            'zip_code' => null,
            'company_id' => $companyId,
        ]);
        $customer = Customer::withoutGlobalScopes()->create([
            'name' => 'Client passage',
            'email' => 'client-'.$companyId.'@magasin.local',
            'phone' => '00000000',
            'country' => "Côte d'Ivoire",
            'city' => $meta['city'] ?? 'Abidjan',
            'address' => 'Magasin',
            'company_id' => $companyId,
        ]);
        Unit::withoutGlobalScopes()->create([
            'name' => 'Piece',
            'short_name' => 'pc',
            'base_unit' => 1,
            'company_id' => $companyId,
        ]);

        $settings = [
            'company_name' => $meta['name'] ?? 'Magasin',
            'email' => $meta['email'] ?? 'oubdaalassane01@gmail.com',
            'phone' => $meta['phone'] ?? '',
            'country' => "Côte d'Ivoire",
            'city' => $meta['city'] ?? 'Abidjan',
            'default_language' => '1',
            'sale_code' => 'SA',
            'purchase_code' => 'PU',
            'sale_return_code' => 'SR',
            'purchase_return_code' => 'PR',
            'expense_code' => 'EX',
            'show_app_name_in_sidebar' => '1',
            'show_logo_in_receipt' => '1',
            'is_currency_right' => '1',
            'currency' => (string) $currency->id,
            'default_warehouse' => (string) $warehouse->id,
            'default_customer' => (string) $customer->id,
            'developed' => 'Alassane Oubda',
            'footer' => \App\Support\FeatureCatalog::FOOTER,
            'address' => $meta['address'] ?? 'Abidjan, Côte d\'Ivoire',
            'state' => $meta['state'] ?? 'Abidjan',
            'logo' => '',
        ];
        foreach ($settings as $key => $value) {
            Setting::withoutGlobalScopes()->create([
                'key' => $key,
                'value' => (string) $value,
                'company_id' => $companyId,
            ]);
        }
    }
}
