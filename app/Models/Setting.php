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
        try {
            return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
                try {
                    $setting = static::where('key', $key)->first();
                    return $setting?->value ?? $default;
                } catch (\Throwable $e) {
                    return $default;
                }
            });
        } catch (\Throwable $e) {
            return $default;
        }
    }

    public static function set(string $key, ?string $value): ?static
    {
        try {
            $setting = static::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );

            Cache::forget("setting_{$key}");

            return $setting;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function getFooterText(?string $contestFooter = null): string
    {
        try {
            if (! empty(trim($contestFooter ?? ''))) {
                return trim($contestFooter);
            }

            $activeContest = \App\Models\Contest::query()->where('ends_at', '>', now())->first()
                ?? \App\Models\Contest::latest()->first();

            if (! empty(trim($activeContest?->footer_text ?? ''))) {
                return trim($activeContest->footer_text);
            }

            return static::get(
                'footer_text',
                '© Powered by <span class="font-semibold text-sky-700">E..E.E. Bouzekri</span> - <span class="font-semibold text-sky-700">DSI-CNFCPP</span> August 2026'
            ) ?? '© Powered by <span class="font-semibold text-sky-700">E..E.E. Bouzekri</span> - <span class="font-semibold text-sky-700">DSI-CNFCPP</span> August 2026';
        } catch (\Throwable $e) {
            return '© Powered by <span class="font-semibold text-sky-700">E..E.E. Bouzekri</span> - <span class="font-semibold text-sky-700">DSI-CNFCPP</span> August 2026';
        }
    }
}
