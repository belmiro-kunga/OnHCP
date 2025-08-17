<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'key', 'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    public static function getValue(string $key, $default = null)
    {
        $rec = static::where('key', $key)->first();
        return $rec ? $rec->value : $default;
    }

    public static function putValue(string $key, $value): self
    {
        $rec = static::firstOrNew(['key' => $key]);
        $rec->value = $value;
        $rec->save();
        return $rec;
    }
}
