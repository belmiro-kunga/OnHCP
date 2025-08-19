<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Simulado extends Model
{
    use HasFactory;

    protected $fillable = [
        'title','description','category_id','duration','min_score','max_attempts','type',
        'allow_navigation','allow_save_progress','show_feedback','status','created_by'
    ];

    protected $casts = [
        'duration' => 'integer',
        'min_score' => 'integer',
        'max_attempts' => 'integer',
        'allow_navigation' => 'boolean',
        'allow_save_progress' => 'boolean',
        'show_feedback' => 'boolean',
    ];

    public function questions()
    {
        return $this->hasMany(SimuladoQuestion::class)->orderBy('q_order');
    }

    public function assignments()
    {
        return $this->hasMany(SimuladoAssignment::class);
    }

    public function category()
    {
        return $this->belongsTo(SimuladoCategory::class, 'category_id');
    }
}
