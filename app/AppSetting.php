<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        return static::allAsMap()[$key] ?? $default;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value !== null ? (string) $value : null]
        );
        Cache::forget('app_settings.map');
    }

    public static function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            static::set((string) $key, $value);
        }
        Cache::forget('app_settings.map');
    }

    public static function allAsMap(): array
    {
        return Cache::remember('app_settings.map', 300, function () {
            return static::query()->pluck('value', 'key')->toArray();
        });
    }
}

