<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PlatformSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function getValue(string $key, ?string $default = null): ?string
    {
        $all = Cache::remember('platform_settings', 60, function () {
            return static::query()->pluck('value', 'key')->all();
        });

        $value = $all[$key] ?? $default;

        return $value === '' ? $default : $value;
    }

    public static function setValue(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('platform_settings');
    }
}
