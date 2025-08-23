<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CertificateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'certificate_number' => $this->certificate_number,
            'verification_code' => $this->verification_code,
            'status' => $this->status,
            'issued_at' => $this->issued_at?->format('Y-m-d H:i:s'),
            'expires_at' => $this->expires_at?->format('Y-m-d H:i:s'),
            'final_grade' => $this->final_grade,
            'completion_hours' => $this->completion_hours,
            'template_version' => $this->template_version,
            
            // Dados do usuário
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            
            // Dados do curso
            'course' => [
                'id' => $this->course->id,
                'title' => $this->course->title,
                'description' => $this->course->description,
                'duration_hours' => $this->course->duration_hours,
            ],
            
            // Dados da matrícula
            'enrollment' => $this->whenLoaded('enrollment', [
                'id' => $this->enrollment->id,
                'enrolled_at' => $this->enrollment->enrolled_at?->format('Y-m-d H:i:s'),
                'completed_at' => $this->enrollment->completed_at?->format('Y-m-d H:i:s'),
                'progress_percentage' => $this->enrollment->progress_percentage,
                'lessons_completed' => $this->enrollment->lessons_completed,
                'total_lessons' => $this->enrollment->total_lessons,
            ]),
            
            // URLs úteis
            'urls' => [
                'download' => route('certificates.download', $this->id),
                'verify' => route('certificates.verify') . '?code=' . $this->verification_code,
                'view' => route('certificates.show', $this->id),
            ],
            
            // Dados adicionais do certificado
            'certificate_data' => $this->certificate_data,
            
            // Informações de revogação (se aplicável)
            'revocation' => $this->when($this->status === 'revoked', [
                'reason' => $this->revocation_reason,
                'revoked_at' => $this->revoked_at?->format('Y-m-d H:i:s'),
            ]),
            
            // Metadados
            'metadata' => [
                'is_expired' => $this->expires_at && $this->expires_at->isPast(),
                'is_active' => $this->status === 'active',
                'is_revoked' => $this->status === 'revoked',
                'has_file' => !empty($this->file_path),
                'issued_days_ago' => $this->issued_at?->diffInDays(now()),
            ],
            
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}