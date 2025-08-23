<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class UserCourseEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'status',
        'enrolled_at',
        'completed_at',
        'progress_percentage',
        'lessons_completed',
        'total_lessons',
        'final_grade',
        'certificate_issued',
        'certificate_issued_at',
        'enrollment_metadata',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
        'certificate_issued_at' => 'datetime',
        'progress_percentage' => 'decimal:2',
        'final_grade' => 'decimal:2',
        'certificate_issued' => 'boolean',
        'enrollment_metadata' => 'array',
    ];

    /**
     * Relacionamento com o usuário
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com o curso
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Relacionamento com o progresso das aulas
     */
    public function lessonProgress(): HasMany
    {
        return $this->hasMany(UserLessonProgress::class, 'enrollment_id');
    }

    /**
     * Certificado da matrícula
     */
    public function certificate()
    {
        return $this->hasOne(Certificate::class, 'user_course_enrollment_id');
    }

    /**
     * Verifica se a matrícula está ativa
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Verifica se o curso foi completado
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Verifica se o certificado foi emitido
     */
    public function hasCertificate(): bool
    {
        return $this->certificate_issued;
    }

    /**
     * Calcula o progresso baseado nas aulas completadas
     */
    public function calculateProgress(): float
    {
        if ($this->total_lessons === 0) {
            return 0.00;
        }

        return round(($this->lessons_completed / $this->total_lessons) * 100, 2);
    }

    /**
     * Atualiza o progresso da matrícula
     */
    public function updateProgress(): void
    {
        $completedLessons = $this->lessonProgress()->where('completed', true)->count();
        $totalLessons = $this->course->lessons()->count();
        
        $this->update([
            'lessons_completed' => $completedLessons,
            'total_lessons' => $totalLessons,
            'progress_percentage' => $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 2) : 0,
        ]);

        // Marcar como completado se todas as aulas foram finalizadas
        if ($completedLessons === $totalLessons && $totalLessons > 0 && $this->status === 'active') {
            $this->markAsCompleted();
        }
    }

    /**
     * Marca a matrícula como completada
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'progress_percentage' => 100.00,
        ]);
    }

    /**
     * Emite o certificado
     */
    public function issueCertificate(): void
    {
        if ($this->isCompleted() && !$this->certificate_issued) {
            $this->update([
                'certificate_issued' => true,
                'certificate_issued_at' => now(),
            ]);
        }
    }

    /**
     * Suspende a matrícula
     */
    public function suspend(): void
    {
        $this->update(['status' => 'suspended']);
    }

    /**
     * Cancela a matrícula
     */
    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Reativa a matrícula
     */
    public function reactivate(): void
    {
        if (in_array($this->status, ['suspended', 'cancelled'])) {
            $this->update(['status' => 'active']);
        }
    }

    /**
     * Obtém a duração da matrícula em dias
     */
    public function getEnrollmentDurationAttribute(): int
    {
        $endDate = $this->completed_at ?? now();
        return $this->enrolled_at->diffInDays($endDate);
    }

    /**
     * Scope para matrículas ativas
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope para matrículas completadas
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope para matrículas com certificado emitido
     */
    public function scopeWithCertificate($query)
    {
        return $query->where('certificate_issued', true);
    }

    /**
     * Scope para matrículas por período
     */
    public function scopeEnrolledBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('enrolled_at', [$startDate, $endDate]);
    }
}