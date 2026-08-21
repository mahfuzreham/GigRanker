<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

final class AppSetting extends Model
{
    protected $fillable = ['key', 'value', 'is_secret'];

    protected function casts(): array
    {
        return ['is_secret' => 'boolean'];
    }

    public function getDecryptedValueAttribute(): ?string
    {
        if ($this->value === null) {
            return null;
        }

        return $this->is_secret ? Crypt::decryptString($this->value) : $this->value;
    }

    public static function putValue(string $key, ?string $value, bool $secret = false): self
    {
        $setting = self::query()->firstOrNew(['key' => $key]);
        $setting->is_secret = $secret;
        $setting->value = $value === null || $value === ''
            ? null
            : ($secret ? Crypt::encryptString($value) : $value);
        $setting->save();

        return $setting;
    }
}
