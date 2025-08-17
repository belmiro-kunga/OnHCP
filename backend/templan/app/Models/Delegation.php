<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delegation extends Model
{
    use HasFactory;

    protected $fillable = [
        'delegator_id', 'delegatee_id', 'scope', 'starts_at', 'ends_at', 'status'
    ];

    protected $casts = [
        'scope' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function delegator(): BelongsTo { return $this->belongsTo(User::class, 'delegator_id'); }
    public function delegatee(): BelongsTo { return $this->belongsTo(User::class, 'delegatee_id'); }

    public function isActive(): bool
    {
        $now = now();
        return $this->status === 'active' && $this->starts_at <= $now && $this->ends_at >= $now;
    }
}
