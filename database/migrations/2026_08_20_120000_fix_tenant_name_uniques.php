<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Les uniques globaux (name/code) cassent le multi-magasin + l'import sur TiDB/Render.
     * On passe en unique (company_id, colonne).
     */
    public function up(): void
    {
        $this->fixUnique('brands', 'name', 'brands_name_unique', 'brands_company_name_unique');
        $this->fixUnique('product_categories', 'name', 'product_categories_name_unique', 'product_categories_company_name_unique');
        $this->fixUnique('currencies', 'name', 'currencies_name_unique', 'currencies_company_name_unique');
    }

    public function down(): void
    {
        //
    }

    private function fixUnique(string $table, string $column, string $oldIndex, string $newIndex): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'company_id')) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($oldIndex) {
                $blueprint->dropUnique($oldIndex);
            });
        } catch (\Throwable $e) {
            try {
                DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$oldIndex}`");
            } catch (\Throwable $e2) {
                // déjà absent
            }
        }

        // Dédupliquer par magasin + nom (garde la plus petite id).
        $duplicates = DB::table($table)
            ->select('company_id', DB::raw("LOWER(`{$column}`) as n"), DB::raw('MIN(id) as keep_id'), DB::raw('COUNT(*) as c'))
            ->groupBy('company_id', DB::raw("LOWER(`{$column}`)"))
            ->having('c', '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            DB::table($table)
                ->where('company_id', $dup->company_id)
                ->whereRaw("LOWER(`{$column}`) = ?", [$dup->n])
                ->where('id', '!=', $dup->keep_id)
                ->delete();
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($column, $newIndex) {
                $blueprint->unique(['company_id', $column], $newIndex);
            });
        } catch (\Throwable $e) {
            // index déjà présent / TiDB
        }
    }
};
