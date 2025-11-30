<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = [
        'setting_key',
        'setting_value',
        'is_encrypted',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Accessors
    public function getValueAttribute()
    {
        if ($this->is_encrypted && $this->setting_value) {
            try {
                return Crypt::decryptString($this->setting_value);
            } catch (\Exception $e) {
                return $this->setting_value;
            }
        }

        return $this->setting_value;
    }

    public function setValueAttribute($value)
    {
        if ($this->is_encrypted && $value) {
            $this->attributes['setting_value'] = Crypt::encryptString($value);
        } else {
            $this->attributes['setting_value'] = $value;
        }
    }

    // Helper methods
    public static function getSetting($key, $default = null)
    {
        $setting = self::where('setting_key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function setSetting($key, $value, $encrypted = false)
    {
        return self::updateOrCreate(
            ['setting_key' => $key],
            [
                'setting_value' => $value,
                'is_encrypted' => $encrypted,
            ]
        );
    }
}
