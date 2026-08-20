<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sur TiDB/Render, les UNIQUE globaux (brands.name, etc.) font échouer l'import
     * dès qu'un nom existe déjà — même pour le même magasin.
     * On les supprime purement (le filtre company_id côté app suffit).
     */
    public function up(): void
    {
        $drops = [
            'brands' => ['brands_name_unique', 'brands_company_name_unique'],
            'product_categories' => ['product_categories_name_unique', 'product_categories_company_name_unique'],
            'units' => ['units_name_unique', 'units_company_name_unique'],
            'currencies' => ['currencies_name_unique', 'currencies_company_name_unique'],
            'products' => ['products_code_unique'],
        ];

        foreach ($drops as $table => $indexes) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            foreach ($indexes as $index) {
                try {
                    DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$index}`");
                } catch (\Throwable $e) {
                    // index absent
                }
            }
        }
    }

    public function down(): void
    {
        //
    }
};
