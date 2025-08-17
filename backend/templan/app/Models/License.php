<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class License extends Model
{
    use HasFactory;

    protected $fillable = [
        'product', 'seats_total', 'seats_used', 'meta'
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    /** @return HasMany<LicenseAssignment> */
    public function assignments(): HasMany
    {
        return $this->hasMany(LicenseAssignment::class);
    }
}
