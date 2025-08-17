<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpLock extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip',
        'fail_count',
        'window_started_at',
        'locked_until',
    ];

    protected $casts = [
        'window_started_at' => 'datetime',
        'locked_until' => 'datetime',
    ];
}
