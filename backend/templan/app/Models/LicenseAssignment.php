<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenseAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'license_id', 'user_id', 'assigned_at'
    ];

    public function license(): BelongsTo { return $this->belongsTo(License::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
