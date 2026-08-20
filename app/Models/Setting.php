<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    public static function get(string $key, $default = null): ?string
    {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting?->value ?? $default;
        });
    }

    public static function set(string $key, ?string $value): static
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget("setting_{$key}");

        return $setting;
    }

    public static function getFooterText(?string $contestFooter = null): string
    {
        if (! empty(trim($contestFooter ?? ''))) {
            return trim($contestFooter);
        }

        return static::get(
            'footer_text',
            '© Powered by <span class="font-semibold text-sky-700">E..E.E. Bouzekri</span> - <span class="font-semibold text-sky-700">DSI-CNFCPP</span> August 2026'
        ) ?? '© Powered by <span class="font-semibold text-sky-700">E..E.E. Bouzekri</span> - <span class="font-semibold text-sky-700">DSI-CNFCPP</span> August 2026';
    }
}
