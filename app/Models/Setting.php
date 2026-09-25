<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'description',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    // ─── Static Accessors ──────────────────────────

    public static function get(string $group, string $key, mixed $default = null): mixed
    {
        $setting = static::where('group', $group)->where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        return static::castValue($setting->value, $setting->type);
    }

    public static function set(string $group, string $key, mixed $value, string $type = 'string'): void
    {
        static::updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => $value, 'type' => $type]
        );

        Cache::forget("settings.{$group}.{$key}");
        Cache::forget("settings.{$group}");
    }

    public static function getGroup(string $group): array
    {
        return Cache::remember("settings.{$group}", 3600, function () use ($group) {
            return static::where('group', $group)
                ->get()
                ->mapWithKeys(fn ($s) => [
                    $s->key => static::castValue($s->value, $s->type),
                ])
                ->toArray();
        });
    }

    public static function getPublicSettings(): array
    {
        return Cache::remember('settings.public', 3600, function () {
            return static::where('is_public', true)
                ->get()
                ->mapWithKeys(fn ($s) => [
                    "{$s->group}.{$s->key}" => static::castValue($s->value, $s->type),
                ])
                ->toArray();
        });
    }

    protected static function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    // ─── Scopes ────────────────────────────────────

    public function scopeOfGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }
}