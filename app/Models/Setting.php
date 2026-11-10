<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    public $timestamps = false;
    protected $guarded = [];

    private static ?array $cache = null;

    public static function get(string $key, $default = null)
    {
        if (self::$cache === null) {
            try {
                self::$cache = self::query()->pluck('value', 'key')->all();
            } catch (\Throwable $e) {
                self::$cache = [];
            }
        }
        $value = self::$cache[$key] ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }

    /** Saves only the keys that do not exist yet (used by the seeder). */
    public static function putMissing(array $values): void
    {
        foreach ($values as $key => $value) {
            self::firstOrCreate(['key' => $key], ['value' => $value]);
        }
        self::$cache = null;
    }

    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            self::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        self::$cache = null;
    }
}
