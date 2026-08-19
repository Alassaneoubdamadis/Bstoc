<?php

namespace App\Models;

use App\Traits\BelongsToCompany;

use App\Traits\HasJsonResourcefulData;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VariationType extends BaseModel
{
    use BelongsToCompany, HasFactory, HasJsonResourcefulData;

    protected $fillable = [
        'name',
        'variation_id',
    ];

    public function variation()
    {
        return $this->belongsTo(Variation::class);
    }

    public function variationProducts()
    {
        return $this->hasMany(VariationProduct::class);
    }
}
