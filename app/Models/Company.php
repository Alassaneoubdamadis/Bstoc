<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    public const STATUS_TRIALING = 'trialing';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAST_DUE = 'past_due';
    public const STATUS_CANCELED = 'canceled';
    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'city',
        'country',
        'address',
        'subscription_plan_id',
        'status',
        'trial_ends_at',
        'subscription_ends_at',
        'is_suspended',
        'owner_user_id',
        'notes',
        'allowed_permissions',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'is_suspended' => 'boolean',
        'allowed_permissions' => 'array',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function hasAccess(): bool
    {
        if ($this->is_suspended) {
            return false;
        }

        if ($this->status === self::STATUS_CANCELED || $this->status === self::STATUS_EXPIRED) {
            return false;
        }

        if ($this->status === self::STATUS_TRIALING) {
            return $this->trial_ends_at && $this->trial_ends_at->isFuture();
        }

        if ($this->status === self::STATUS_ACTIVE) {
            return ! $this->subscription_ends_at || $this->subscription_ends_at->isFuture();
        }

        return false;
    }

    public function accessLabel(): string
    {
        if ($this->is_suspended) {
            return 'Suspendu';
        }
        if ($this->status === self::STATUS_TRIALING && $this->trial_ends_at) {
            if ($this->trial_ends_at->isPast()) {
                return 'Essai terminé';
            }

            return 'Essai ('.$this->trial_ends_at->diffForHumans().')';
        }

        return match ($this->status) {
            self::STATUS_ACTIVE => 'Actif',
            self::STATUS_PAST_DUE => 'Impayé',
            self::STATUS_CANCELED => 'Résilié',
            self::STATUS_EXPIRED => 'Expiré',
            default => $this->status,
        };
    }

    public function allows(string $permission): bool
    {
        if ($this->allowed_permissions === null) {
            return true;
        }

        return in_array($permission, $this->allowed_permissions, true);
    }

    public function filterPermissions(array $permissions): array
    {
        if ($this->allowed_permissions === null) {
            return array_values($permissions);
        }

        return array_values(array_intersect($permissions, $this->allowed_permissions));
    }

    public function activatePlan(SubscriptionPlan $plan): void
    {
        $endsAt = $plan->interval === 'year'
            ? Carbon::now()->addYear()
            : Carbon::now()->addMonth();

        $this->forceFill([
            'subscription_plan_id' => $plan->id,
            'status' => self::STATUS_ACTIVE,
            'is_suspended' => false,
            'trial_ends_at' => null,
            'subscription_ends_at' => $endsAt,
        ])->save();
    }

    public static function startTrial(array $attributes, SubscriptionPlan $plan): self
    {
        $trialDays = $plan->trial_days ?: 14;

        return self::create(array_merge($attributes, [
            'subscription_plan_id' => $plan->id,
            'status' => self::STATUS_TRIALING,
            'trial_ends_at' => Carbon::now()->addDays($trialDays),
            'is_suspended' => false,
        ]));
    }
}
