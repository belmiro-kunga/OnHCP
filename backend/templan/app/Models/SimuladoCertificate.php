<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimuladoCertificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'simulado_id','attempt_id','user_id','code','issued_at','metadata'
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'metadata' => 'array',
    ];
}
