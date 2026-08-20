<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $companyIds = DB::table('companies')->pluck('id');
        foreach ($companyIds as $companyId) {
            foreach ([
                ['name' => 'Piece', 'short_name' => 'pc', 'base_unit' => 1],
                ['name' => 'meter', 'short_name' => 'm', 'base_unit' => 2],
                ['name' => 'kilogram', 'short_name' => 'kg', 'base_unit' => 3],
            ] as $unit) {
                $exists = DB::table('units')
                    ->whereRaw('LOWER(name) = ?', [strtolower($unit['name'])])
                    ->where(function ($q) use ($companyId) {
                        $q->where('company_id', $companyId)->orWhereNull('company_id');
                    })
                    ->exists();

                if ($exists) {
                    DB::table('units')
                        ->whereRaw('LOWER(name) = ?', [strtolower($unit['name'])])
                        ->whereNull('company_id')
                        ->update([
                            'company_id' => $companyId,
                            'base_unit' => $unit['base_unit'],
                            'updated_at' => now(),
                        ]);
                    continue;
                }

                try {
                    DB::table('units')->insert([
                        'name' => $unit['name'],
                        'short_name' => $unit['short_name'],
                        'base_unit' => $unit['base_unit'],
                        'company_id' => $companyId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } catch (\Throwable $e) {
                    // nom déjà pris par l'index unique global
                }
            }
        }
    }

    public function down(): void
    {
        //
    }
};
