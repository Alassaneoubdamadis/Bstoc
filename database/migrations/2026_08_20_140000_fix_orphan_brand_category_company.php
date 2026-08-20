<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Rattache marques/catégories orphelines (company_id null) au magasin
     * qui les utilise — sinon la liste produits plante (->name sur null).
     */
    public function up(): void
    {
        $defaultCompanyId = DB::table('companies')->orderBy('id')->value('id');
        if (! $defaultCompanyId) {
            return;
        }

        foreach (['brands', 'product_categories', 'units'] as $table) {
            DB::table($table)->whereNull('company_id')->update([
                'company_id' => $defaultCompanyId,
                'updated_at' => now(),
            ]);
        }

        // Marques référencées par un produit d'un autre company_id
        $links = DB::table('products as p')
            ->join('brands as b', 'b.id', '=', 'p.brand_id')
            ->whereNotNull('p.company_id')
            ->whereColumn('p.company_id', '!=', 'b.company_id')
            ->select('b.id as brand_id', 'p.company_id')
            ->distinct()
            ->get();

        foreach ($links as $link) {
            DB::table('brands')->where('id', $link->brand_id)->update([
                'company_id' => $link->company_id,
                'updated_at' => now(),
            ]);
        }

        $catLinks = DB::table('products as p')
            ->join('product_categories as c', 'c.id', '=', 'p.product_category_id')
            ->whereNotNull('p.company_id')
            ->whereColumn('p.company_id', '!=', 'c.company_id')
            ->select('c.id as category_id', 'p.company_id')
            ->distinct()
            ->get();

        foreach ($catLinks as $link) {
            DB::table('product_categories')->where('id', $link->category_id)->update([
                'company_id' => $link->company_id,
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        //
    }
};
