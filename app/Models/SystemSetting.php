<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = self::where('key', $key)->first();
        if (! $setting) {
            return $default;
        }

        $val = $setting->value;
        if ($val === '1' || $val === 'true') {
            return true;
        }
        if ($val === '0' || $val === 'false') {
            return false;
        }

        $decoded = json_decode($val, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return $val;
    }

    public static function set(string $key, mixed $value): void
    {
        $stringValue = is_bool($value) ? ($value ? 'true' : 'false') : (is_scalar($value) ? (string) $value : json_encode($value));

        self::updateOrCreate(
            ['key' => $key],
            ['value' => $stringValue]
        );
    }
}
