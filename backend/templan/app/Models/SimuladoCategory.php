<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimuladoCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name'];
    
    /**
     * Relação com simulados
     */
    public function simulados()
    {
        return $this->hasMany(Simulado::class, 'category_id');
    }

    /**
     * Escopo: ordenar por nome (asc)
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }
}
