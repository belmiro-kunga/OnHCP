<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'ip_cidr',
        'reason',
        'created_by',
    ];
}
