<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'category_id',
        'thumbnail_path',
        'status',
        'sort_index',
    ];

    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class)->orderBy('sort_index');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class);
    }

    /**
     * Relacionamento com aulas através dos módulos
     */
    public function lessons(): HasManyThrough
    {
        return $this->hasManyThrough(CourseLesson::class, CourseModule::class);
    }

    /**
     * Relacionamento com matrículas
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(UserCourseEnrollment::class);
    }

    /**
     * Relacionamento com usuários matriculados
     */
    public function enrolledUsers()
    {
        return $this->belongsToMany(User::class, 'user_course_enrollments')
                    ->withPivot(['status', 'enrolled_at', 'completed_at', 'progress_percentage', 'final_grade', 'certificate_issued'])
                    ->withTimestamps();
    }

    /**
     * Relacionamento com usuários ativos
     */
    public function activeUsers()
    {
        return $this->enrolledUsers()->wherePivot('status', 'active');
    }

    /**
     * Relacionamento com usuários que completaram o curso
     */
    public function completedUsers()
    {
        return $this->enrolledUsers()->wherePivot('status', 'completed');
    }

    /**
     * Scope a query to only include published courses.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Verifica se o curso está ativo/publicado
     */
    public function isActive(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Obtém o número total de aulas do curso
     */
    public function getTotalLessonsAttribute(): int
    {
        return $this->lessons()->count();
    }

    /**
     * Obtém a duração total do curso em horas
     */
    public function getDurationHoursAttribute(): float
    {
        $totalMinutes = $this->lessons()->sum('duration_minutes');
        return round($totalMinutes / 60, 1);
    }

    /**
     * Obtém o número de usuários matriculados
     */
    public function getEnrolledCountAttribute(): int
    {
        return $this->enrollments()->count();
    }

    /**
     * Obtém o número de usuários ativos
     */
    public function getActiveEnrollmentsCountAttribute(): int
    {
        return $this->enrollments()->active()->count();
    }

    /**
     * Obtém o número de usuários que completaram o curso
     */
    public function getCompletedCountAttribute(): int
    {
        return $this->enrollments()->completed()->count();
    }

    /**
     * Calcula a taxa de conclusão do curso
     */
    public function getCompletionRateAttribute(): float
    {
        $total = $this->enrolled_count;
        if ($total === 0) return 0;
        
        return round(($this->completed_count / $total) * 100, 2);
    }

    /**
     * Obtém a avaliação média do curso (se implementado)
     */
    public function getAverageRatingAttribute(): float
    {
        // Placeholder para quando implementarmos sistema de avaliações
        return 0.0;
    }

    /**
     * Verifica se um usuário está matriculado no curso
     */
    public function isUserEnrolled(int $userId): bool
    {
        return $this->enrollments()
            ->where('user_id', $userId)
            ->whereIn('status', ['active', 'completed'])
            ->exists();
    }

    /**
     * Obtém a matrícula de um usuário específico
     */
    public function getUserEnrollment(int $userId): ?UserCourseEnrollment
    {
        return $this->enrollments()
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Scope para cursos com matrículas ativas
     */
    public function scopeWithActiveEnrollments($query)
    {
        return $query->whereHas('enrollments', function ($q) {
            $q->active();
        });
    }

    /**
     * Scope para cursos populares (com mais matrículas)
     */
    public function scopePopular($query, $limit = 10)
    {
        return $query->withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->limit($limit);
    }
}
