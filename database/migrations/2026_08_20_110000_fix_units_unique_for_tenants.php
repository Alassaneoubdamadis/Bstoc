<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // L'index unique global sur name bloque le multi-magasin + l'import.
        try {
            Schema::table('units', function (Blueprint $table) {
                $table->dropUnique('units_name_unique');
            });
        } catch (\Throwable $e) {
            try {
                DB::statement('ALTER TABLE units DROP INDEX units_name_unique');
            } catch (\Throwable $e2) {
                // déjà absent
            }
        }

        // Dédupliquer avant nouvel index (garde la plus petite id par company+name).
        $duplicates = DB::table('units')
            ->select('company_id', DB::raw('LOWER(name) as n'), DB::raw('MIN(id) as keep_id'), DB::raw('COUNT(*) as c'))
            ->groupBy('company_id', DB::raw('LOWER(name)'))
            ->having('c', '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            DB::table('units')
                ->where('company_id', $dup->company_id)
                ->whereRaw('LOWER(name) = ?', [$dup->n])
                ->where('id', '!=', $dup->keep_id)
                ->delete();
        }

        try {
            Schema::table('units', function (Blueprint $table) {
                $table->unique(['company_id', 'name'], 'units_company_name_unique');
            });
        } catch (\Throwable $e) {
            // TiDB / index déjà présent
        }
    }

    public function down(): void
    {
        try {
            Schema::table('units', function (Blueprint $table) {
                $table->dropUnique('units_company_name_unique');
                $table->unique('name', 'units_name_unique');
            });
        } catch (\Throwable $e) {
            //
        }
    }
};
