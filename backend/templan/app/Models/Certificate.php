<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'certificate_number',
        'issued_at',
        'template_used',
        'pdf_path',
        'verification_code',
        'metadata',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Relacionamento com a matrícula
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(UserCourseEnrollment::class);
    }

    /**
     * Relacionamento com o usuário através da matrícula
     */
    public function user()
    {
        return $this->hasOneThrough(
            User::class,
            UserCourseEnrollment::class,
            'id', // Foreign key on enrollments table
            'id', // Foreign key on users table
            'enrollment_id', // Local key on certificates table
            'user_id' // Local key on enrollments table
        );
    }

    /**
     * Relacionamento com o curso através da matrícula
     */
    public function course()
    {
        return $this->hasOneThrough(
            Course::class,
            UserCourseEnrollment::class,
            'id', // Foreign key on enrollments table
            'id', // Foreign key on courses table
            'enrollment_id', // Local key on certificates table
            'course_id' // Local key on enrollments table
        );
    }

    /**
     * Gera um número único de certificado
     */
    public static function generateCertificateNumber(): string
    {
        $year = Carbon::now()->year;
        $month = Carbon::now()->format('m');
        
        // Conta certificados emitidos no mês atual
        $count = static::whereYear('issued_at', $year)
            ->whereMonth('issued_at', Carbon::now()->month)
            ->count() + 1;
        
        return sprintf('CERT-%d%s-%04d', $year, $month, $count);
    }

    /**
     * Gera um código de verificação único
     */
    public static function generateVerificationCode(): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        } while (static::where('verification_code', $code)->exists());
        
        return $code;
    }

    /**
     * Obtém a URL de download do certificado
     */
    public function getDownloadUrlAttribute(): string
    {
        return route('certificates.download', ['certificate' => $this->id]);
    }

    /**
     * Obtém a URL de verificação do certificado
     */
    public function getVerificationUrlAttribute(): string
    {
        return route('certificates.verify', ['code' => $this->verification_code]);
    }

    /**
     * Verifica se o certificado é válido
     */
    public function isValid(): bool
    {
        return $this->enrollment && 
               $this->enrollment->status === 'completed' &&
               $this->pdf_path &&
               file_exists(storage_path('app/' . $this->pdf_path));
    }

    /**
     * Obtém informações formatadas para o template
     */
    public function getTemplateDataAttribute(): array
    {
        $enrollment = $this->enrollment;
        $user = $enrollment->user;
        $course = $enrollment->course;
        
        return [
            'student_name' => $user->name,
            'student_email' => $user->email,
            'course_title' => $course->title,
            'course_description' => $course->description,
            'completion_date' => $enrollment->completed_at->format('d/m/Y'),
            'issue_date' => $this->issued_at->format('d/m/Y'),
            'certificate_number' => $this->certificate_number,
            'verification_code' => $this->verification_code,
            'verification_url' => $this->verification_url,
            'final_grade' => $enrollment->final_grade,
            'duration_hours' => $course->duration_hours,
            'lessons_completed' => $enrollment->lessons_completed,
            'total_lessons' => $enrollment->total_lessons,
        ];
    }

    /**
     * Scope para certificados válidos
     */
    public function scopeValid($query)
    {
        return $query->whereHas('enrollment', function ($q) {
            $q->where('status', 'completed');
        })->whereNotNull('pdf_path');
    }

    /**
     * Scope para certificados emitidos em um período
     */
    public function scopeIssuedBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('issued_at', [$startDate, $endDate]);
    }

    /**
     * Scope para certificados de um curso específico
     */
    public function scopeForCourse($query, $courseId)
    {
        return $query->whereHas('enrollment', function ($q) use ($courseId) {
            $q->where('course_id', $courseId);
        });
    }

    /**
     * Scope para certificados de um usuário específico
     */
    public function scopeForUser($query, $userId)
    {
        return $query->whereHas('enrollment', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Boot method para eventos do modelo
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($certificate) {
            if (!$certificate->certificate_number) {
                $certificate->certificate_number = static::generateCertificateNumber();
            }
            
            if (!$certificate->verification_code) {
                $certificate->verification_code = static::generateVerificationCode();
            }
            
            if (!$certificate->issued_at) {
                $certificate->issued_at = Carbon::now();
            }
        });
    }
}