<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnforceCompanyFeatures
{
    /** @var array<string, string> */
    protected array $resourcePermissions = [
        'users' => 'manage_users',
        'roles' => 'manage_roles',
        'settings' => 'manage_setting',
        'brands' => 'manage_brands',
        'product-categories' => 'manage_product_categories',
        'products' => 'manage_products',
        'main-products' => 'manage_products',
        'currencies' => 'manage_currency',
        'warehouses' => 'manage_warehouses',
        'units' => 'manage_units',
        'base-units' => 'manage_units',
        'variations' => 'manage_variations',
        'customers' => 'manage_customers',
        'suppliers' => 'manage_suppliers',
        'expenses' => 'manage_expenses',
        'expense-categories' => 'manage_expense_categories',
        'transfers' => 'manage_transfers',
        'adjustments' => 'manage_adjustments',
        'quotations' => 'manage_quotations',
        'purchases' => 'manage_purchase',
        'purchases-return' => 'manage_purchase_return',
        'sales' => 'manage_sale',
        'sales-return' => 'manage_sale_return',
        'languages' => 'manage_language',
        'mail-templates' => 'manage_email_templates',
        'sms-templates' => 'manage_sms_templates',
        'sms-settings' => 'manage_sms_apis',
        'coupon-codes' => 'manage_sale',
    ];

    /** GET still required by the POS chrome even if settings cannot be edited. */
    protected array $writeOnlyResources = [
        'settings',
        'products',
        'main-products',
        'customers',
        'warehouses',
    ];

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user || ! empty($user->is_platform_admin)) {
            return $next($request);
        }

        $resource = $request->segment(2);
        $permission = $this->resourcePermissions[$resource] ?? null;
        if (! $permission) {
            return $next($request);
        }

        $isWrite = ! in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true);
        if (! $isWrite && in_array($resource, $this->writeOnlyResources, true)) {
            return $next($request);
        }

        if (! company_allows($permission)) {
            return response()->json([
                'success' => false,
                'message' => 'Cette fonctionnalité n’est pas autorisée pour ce magasin.',
            ], 403);
        }

        return $next($request);
    }
}
