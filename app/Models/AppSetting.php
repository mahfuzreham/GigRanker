<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AppSetting extends Model
{
    protected $fillable = ['key', 'value', 'encrypted'];

    protected $casts = ['encrypted' => 'boolean'];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::query()->where('key', $key)->first();
        if (!$setting) return $default;
        return $setting->encrypted ? Crypt::decryptString($setting->value) : $setting->value;
    }

    public static function putValue(string $key, ?string $value, bool $encrypted = false): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value === null ? null : ($encrypted ? Crypt::encryptString($value) : $value), 'encrypted' => $encrypted]
        );
    }
}
