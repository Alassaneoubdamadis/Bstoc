<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPayment extends Model
{
    protected $fillable = [
        'company_id',
        'plan_id',
        'amount',
        'currency',
        'genius_reference',
        'status',
        'checkout_url',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
        'amount' => 'integer',
    ];
}
