<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Certificate;
use App\Models\UserCourseEnrollment;
use App\Models\User;
use App\Models\Course;
use Carbon\Carbon;

class CertificateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar matrículas concluídas que ainda não têm certificado
        $completedEnrollments = UserCourseEnrollment::where('status', 'completed')
            ->where('certificate_issued', false)
            ->with(['user', 'course'])
            ->get();

        if ($completedEnrollments->isEmpty()) {
            $this->command->info('Nenhuma matrícula concluída encontrada para gerar certificados.');
            return;
        }

        $this->command->info("Gerando certificados para {$completedEnrollments->count()} matrículas concluídas...");

        foreach ($completedEnrollments as $enrollment) {
            // Gerar nota final aleatória entre 7.0 e 10.0
            $finalGrade = rand(70, 100) / 10;
            
            // Calcular horas de conclusão baseado na duração das aulas do curso
            $totalSeconds = $enrollment->course->lessons()->sum('duration_seconds');
            $completionHours = $totalSeconds > 0 ? round($totalSeconds / 3600, 1) : rand(20, 80);
            
            // Data de emissão entre a data de conclusão e agora
            $issuedAt = Carbon::parse($enrollment->completed_at)
                ->addDays(rand(0, 7))
                ->addHours(rand(0, 23))
                ->addMinutes(rand(0, 59));

            $certificate = Certificate::create([
                'user_course_enrollment_id' => $enrollment->id,
                'user_id' => $enrollment->user_id,
                'course_id' => $enrollment->course_id,
                'issued_at' => $issuedAt,
                'final_grade' => $finalGrade,
                'completion_hours' => $completionHours,
                'template_version' => 'v1',
                'certificate_data' => json_encode([
                    'user_name' => $enrollment->user->name,
                    'course_title' => $enrollment->course->title,
                    'completion_date' => $enrollment->completed_at,
                    'instructor' => $enrollment->course->instructor ?? 'Sistema OnHCP',
                    'course_description' => $enrollment->course->description,
                    'course_category' => $enrollment->course->category->name ?? 'Geral',
                ]),
                'status' => 'active'
            ]);

            // Atualizar a matrícula para marcar que o certificado foi emitido
            $enrollment->update([
                'certificate_issued' => true,
                'certificate_issued_at' => $issuedAt,
                'final_grade' => $finalGrade
            ]);

            $this->command->info("Certificado {$certificate->certificate_number} gerado para {$enrollment->user->name} - {$enrollment->course->title}");
        }

        // Gerar alguns certificados revogados para teste
        $this->generateRevokedCertificates();

        $this->command->info('Certificados gerados com sucesso!');
    }

    /**
     * Gerar alguns certificados revogados para demonstração
     */
    private function generateRevokedCertificates(): void
    {
        $activeCertificates = Certificate::where('status', 'active')
            ->inRandomOrder()
            ->limit(2)
            ->get();

        foreach ($activeCertificates as $certificate) {
            $certificate->update([
                'status' => 'revoked',
                'revocation_reason' => 'Certificado revogado para fins de teste e demonstração do sistema.',
                'revoked_at' => now()->subDays(rand(1, 30))
            ]);

            $this->command->info("Certificado {$certificate->certificate_number} foi revogado para teste.");
        }
    }
}