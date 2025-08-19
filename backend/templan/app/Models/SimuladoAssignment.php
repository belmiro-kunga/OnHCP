<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimuladoAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'simulado_id','target_type','target_id','assigned_by','due_at','max_attempts_override','min_score_override'
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'max_attempts_override' => 'integer',
        'min_score_override' => 'integer',
    ];

    public function simulado() { return $this->belongsTo(Simulado::class); }
}
