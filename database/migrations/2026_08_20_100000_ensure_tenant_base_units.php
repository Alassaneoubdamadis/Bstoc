<?php

use App\Models\Unit;
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
                    ->where('company_id', $companyId)
                    ->whereRaw('LOWER(name) = ?', [strtolower($unit['name'])])
                    ->exists();
                if ($exists) {
                    continue;
                }
                Unit::withoutGlobalScopes()->create([
                    'name' => $unit['name'],
                    'short_name' => $unit['short_name'],
                    'base_unit' => $unit['base_unit'],
                    'company_id' => $companyId,
                ]);
            }
        }
    }

    public function down(): void
    {
        //
    }
};
