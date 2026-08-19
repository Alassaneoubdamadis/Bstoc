<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'price',
        'currency',
        'interval',
        'trial_days',
        'is_active',
        'sort_order',
        'features',
        'description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'features' => 'array',
        'trial_days' => 'integer',
        'sort_order' => 'integer',
    ];

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }
}
