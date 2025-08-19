<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimuladoAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'simulado_id','user_id','current_question','answers','time_remaining',
        'submitted_at','score','passed','result'
    ];

    protected $casts = [
        'current_question' => 'integer',
        'answers' => 'array',
        'time_remaining' => 'integer',
        'submitted_at' => 'datetime',
        'score' => 'integer',
        'passed' => 'boolean',
        'result' => 'array',
    ];

    public function simulado() { return $this->belongsTo(Simulado::class); }
}
