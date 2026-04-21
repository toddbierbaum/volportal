<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /** @var array<string, mixed> */
    private static array $cache = [];

    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }

        $row = self::where('key', $key)->first();
        $value = $row ? self::decode($row->value) : $default;
        self::$cache[$key] = $value;
        return $value;
    }

    public static function set(string $key, mixed $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => self::encode($value)]);
        self::$cache[$key] = $value;
    }

    public static function flushCache(): void
    {
        self::$cache = [];
    }

    private static function encode(mixed $value): string
    {
        return json_encode($value);
    }

    private static function decode(?string $raw): mixed
    {
        if ($raw === null) return null;
        return json_decode($raw, true);
    }
}
