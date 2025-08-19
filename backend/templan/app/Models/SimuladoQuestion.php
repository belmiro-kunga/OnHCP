<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimuladoQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'simulado_id','statement','q_type','weight','difficulty','options','correct_answer','explanation','q_order'
    ];

    protected $casts = [
        'options' => 'array',
        'q_order' => 'integer',
        'weight' => 'integer',
    ];

    public function simulado()
    {
        return $this->belongsTo(Simulado::class);
    }
}
