<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class UserLessonProgress extends Model
{
    use HasFactory;

    protected $table = 'user_lesson_progress';

    protected $fillable = [
        'enrollment_id',
        'course_lesson_id',
        'started',
        'completed',
        'started_at',
        'completed_at',
        'watch_time_seconds',
        'total_duration_seconds',
        'completion_percentage',
        'attempts',
        'last_accessed_at',
        'progress_metadata',
    ];

    protected $casts = [
        'started' => 'boolean',
        'completed' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_accessed_at' => 'datetime',
        'completion_percentage' => 'decimal:2',
        'progress_metadata' => 'array',
    ];

    /**
     * Relacionamento com a matrícula
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(UserCourseEnrollment::class, 'enrollment_id');
    }

    /**
     * Relacionamento com a aula
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CourseLesson::class, 'course_lesson_id');
    }

    /**
     * Marca a aula como iniciada
     */
    public function markAsStarted(): void
    {
        if (!$this->started) {
            $this->update([
                'started' => true,
                'started_at' => now(),
                'last_accessed_at' => now(),
                'attempts' => $this->attempts + 1,
            ]);
        } else {
            $this->updateLastAccessed();
        }
    }

    /**
     * Marca a aula como completada
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'completed' => true,
            'completed_at' => now(),
            'completion_percentage' => 100.00,
            'last_accessed_at' => now(),
        ]);

        // Atualizar o progresso da matrícula
        $this->enrollment->updateProgress();
    }

    /**
     * Atualiza o tempo assistido
     */
    public function updateWatchTime(int $watchTimeSeconds, ?int $totalDurationSeconds = null): void
    {
        $data = [
            'watch_time_seconds' => $watchTimeSeconds,
            'last_accessed_at' => now(),
        ];

        if ($totalDurationSeconds !== null) {
            $data['total_duration_seconds'] = $totalDurationSeconds;
        }

        // Calcular porcentagem de conclusão
        if ($this->total_duration_seconds > 0) {
            $completionPercentage = min(100, ($watchTimeSeconds / $this->total_duration_seconds) * 100);
            $data['completion_percentage'] = round($completionPercentage, 2);

            // Marcar como completado se assistiu mais de 80% do vídeo
            if ($completionPercentage >= 80 && !$this->completed) {
                $data['completed'] = true;
                $data['completed_at'] = now();
            }
        }

        $this->update($data);

        // Se foi marcado como completado, atualizar progresso da matrícula
        if (isset($data['completed']) && $data['completed']) {
            $this->enrollment->updateProgress();
        }
    }

    /**
     * Atualiza o último acesso
     */
    public function updateLastAccessed(): void
    {
        $this->update([
            'last_accessed_at' => now(),
            'attempts' => $this->attempts + 1,
        ]);
    }

    /**
     * Reseta o progresso da aula
     */
    public function resetProgress(): void
    {
        $this->update([
            'started' => false,
            'completed' => false,
            'started_at' => null,
            'completed_at' => null,
            'watch_time_seconds' => 0,
            'completion_percentage' => 0.00,
            'attempts' => 0,
            'last_accessed_at' => null,
            'progress_metadata' => null,
        ]);

        // Atualizar o progresso da matrícula
        $this->enrollment->updateProgress();
    }

    /**
     * Verifica se a aula foi iniciada
     */
    public function isStarted(): bool
    {
        return $this->started;
    }

    /**
     * Verifica se a aula foi completada
     */
    public function isCompleted(): bool
    {
        return $this->completed;
    }

    /**
     * Obtém o tempo restante em segundos
     */
    public function getRemainingTimeAttribute(): int
    {
        if (!$this->total_duration_seconds) {
            return 0;
        }

        return max(0, $this->total_duration_seconds - $this->watch_time_seconds);
    }

    /**
     * Obtém o tempo assistido formatado
     */
    public function getFormattedWatchTimeAttribute(): string
    {
        return $this->formatSeconds($this->watch_time_seconds);
    }

    /**
     * Obtém a duração total formatada
     */
    public function getFormattedTotalDurationAttribute(): string
    {
        return $this->formatSeconds($this->total_duration_seconds ?? 0);
    }

    /**
     * Formata segundos em HH:MM:SS
     */
    private function formatSeconds(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * Scope para progresso completado
     */
    public function scopeCompleted($query)
    {
        return $query->where('completed', true);
    }

    /**
     * Scope para progresso iniciado
     */
    public function scopeStarted($query)
    {
        return $query->where('started', true);
    }

    /**
     * Scope para progresso por período
     */
    public function scopeAccessedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('last_accessed_at', [$startDate, $endDate]);
    }
}