<?php

use App\Models\Currency;
use App\Models\ManageStock;
use App\Models\Setting;
use App\Models\Supplier;
use Illuminate\Support\Facades\File;

if (! function_exists('getPageSize')) {
    /**
     * @return mixed
     */
    function getPageSize($request)
    {
        return $request->input('page.size', 10);
    }
}

function getLogoUrl(): string
{
    static $appLogo;

    if (empty($appLogo)) {
        $appLogo = Setting::where('key', '=', 'logo')->first();
    }

    $url = ! empty($appLogo) ? (string) $appLogo->logo : '';
    if ($url !== '') {
        return $url;
    }

    return platform_logo_url();
}

if (! function_exists('platform_app_name')) {
    function platform_app_name(): string
    {
        try {
            return \App\Models\PlatformSetting::getValue('app_name', 'B-Stock') ?: 'B-Stock';
        } catch (\Throwable $e) {
            return 'B-Stock';
        }
    }
}

if (! function_exists('platform_logo_url')) {
    function platform_logo_url(): string
    {
        try {
            $path = \App\Models\PlatformSetting::getValue('logo_path');
        } catch (\Throwable $e) {
            return '';
        }

        return $path ? asset($path) : '';
    }
}

if (! function_exists('platform_favicon_url')) {
    function platform_favicon_url(): string
    {
        try {
            $path = \App\Models\PlatformSetting::getValue('favicon_path')
                ?: \App\Models\PlatformSetting::getValue('logo_path');
        } catch (\Throwable $e) {
            return '';
        }

        return $path ? asset($path) : '';
    }
}

if (! function_exists('company_subscription_snapshot')) {
    function company_subscription_snapshot(?\App\Models\Company $company): array
    {
        if (! $company) {
            return [
                'active' => false,
                'status' => 'none',
                'plan_name' => null,
                'ends_at' => null,
                'days_left' => 0,
                'label' => 'Aucun magasin',
                'message' => 'Abonnement inactif.',
                'is_suspended' => false,
            ];
        }

        $end = $company->status === \App\Models\Company::STATUS_TRIALING
            ? $company->trial_ends_at
            : $company->subscription_ends_at;
        $daysLeft = ($end && $end->isFuture()) ? (int) now()->diffInDays($end) : 0;
        $active = $company->hasAccess();

        $message = null;
        if (! $active) {
            if ($company->is_suspended) {
                $message = 'Votre magasin est suspendu. Contactez B-Stock.';
            } elseif ($company->status === \App\Models\Company::STATUS_TRIALING) {
                $message = 'Votre période d’essai est expirée.';
            } else {
                $message = 'Votre abonnement est inactif ou expiré.';
            }
        }

        return [
            'active' => $active,
            'status' => $company->status,
            'plan_name' => $company->plan?->name,
            'ends_at' => $end?->toIso8601String(),
            'days_left' => $daysLeft,
            'label' => $company->accessLabel(),
            'message' => $message,
            'is_suspended' => (bool) $company->is_suspended,
        ];
    }
}

if (! function_exists('getSettingValue')) {
    /**
     * @return mixed
     */
    function getSettingValue($keyName)
    {
        $key = 'setting'.'-'.$keyName;

        static $settingValues;

        if (isset($settingValues[$key])) {
            return $settingValues[$key];
        }

        /** @var Setting $setting */
        $setting = Setting::where('key', '=', $keyName)->first();
        $settingValues[$key] = $setting->value ?? null;

        return $settingValues[$key];
    }
}

function canDelete(array $models, string $columnName, int $id): bool
{
    foreach ($models as $model) {
        $result = $model::where($columnName, $id)->exists();

        if ($result) {
            return true;
        }
    }

    return false;
}

function getCurrencyCode()
{
    $currencyId = Setting::where('key', '=', 'currency')->first()->value;

    return Currency::whereId($currencyId)->first()->symbol;
}

function getLoginUserLanguage(): string
{
    return \Illuminate\Support\Facades\Auth::user()->language;
}

if (! function_exists('manageStock')) {
    /**
     * @param $request
     * @return mixed
     */
    function manageStock($warehouseID, $productID, $qty = 0)
    {
        $product = ManageStock::whereWarehouseId($warehouseID)
            ->whereProductId($productID)
            ->first();

        if ($product) {
            $totalQuantity = $product->quantity + $qty;

            if (($product->quantity + $qty) < 0) {
                $totalQuantity = 0;
            }
            $product->update([
                'quantity' => $totalQuantity,
            ]);
        } else {
            if ($qty < 0) {
                $qty = 0;
            }

            ManageStock::create([
                'warehouse_id' => $warehouseID,
                'product_id' => $productID,
                'quantity' => $qty,
            ]);
        }
    }
}

if (! function_exists('keyExist')) {
    function keyExist($key)
    {
        $exists = Setting::where('key', $key)->exists();

        return $exists;
    }
}

function getSupplierGrandTotalFilterIds($search)
{
    $supplierData = Supplier::with('purchases')->get();
    $ids = [];
    foreach ($supplierData as $key => $supplier) {
        $value = $supplier->purchases->sum('grand_total');
        if ($search != '') {
            if ($value == $search) {
                $ids[] = $supplier->id;
            }
        }
    }

    return $ids;
}

if (! function_exists('replaceArrayValue')) {
    function replaceArrayValue(&$array, $key, $replaceValue)
    {
        foreach ($array as $index => $value) {
            if (is_array($value)) {
                $array[$index] = replaceArrayValue($value, $key, $replaceValue);
            }
            if ($index == $key) {
                $array[$index] = $replaceValue;
            }
        }

        return $array;
    }
}

if (! function_exists('getLogo')) {
    function getLogo()
    {
        /** @var Setting $setting */
        $logoImage = Setting::where('key', '=', 'logo')->first()->value;

        $logo = '';
        if (File::exists(asset($logoImage))) {
            $logo = base64_encode(file_get_contents(asset($logoImage)));
        }

        return 'data:image/png;base64,'.$logo;
    }
}

if (! function_exists('currencyAlignment')) {
    function currencyAlignment($amount)
    {
        if (getSettingValue('is_currency_right') != 1) {
            return getCurrencyCode().' '.$amount;
        }

        return $amount.' '.getCurrencyCode();
    }
}

if (! function_exists('current_company_id')) {
    function current_company_id(): ?int
    {
        if (app()->bound('currentCompanyId')) {
            $id = app('currentCompanyId');

            return $id ? (int) $id : null;
        }

        if (! auth()->hasUser()) {
            return null;
        }

        $user = auth()->user();
        if ($user && empty($user->is_platform_admin) && ! empty($user->company_id)) {
            return (int) $user->company_id;
        }

        return null;
    }
}

if (! function_exists('filter_company_permissions')) {
    function filter_company_permissions(array $permissions, $company = null): array
    {
        $company = $company ?: (\Illuminate\Support\Facades\Auth::user()->company ?? null);
        if (! $company && current_company_id()) {
            $company = \App\Models\Company::withoutGlobalScopes()->find(current_company_id());
        }
        if (! $company) {
            return array_values($permissions);
        }

        return $company->filterPermissions($permissions);
    }
}

if (! function_exists('company_allows')) {
    function company_allows(string $permission): bool
    {
        $user = auth()->user();
        if (! $user || $user->is_platform_admin) {
            return true;
        }
        $company = \App\Models\Company::withoutGlobalScopes()->find($user->company_id);
        if (! $company) {
            return false;
        }

        return $company->allows($permission);
    }
}
