<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Course;
use App\Models\UserCourseEnrollment;
use App\Models\CourseLesson;
use App\Models\UserLessonProgress;
use Carbon\Carbon;

class EnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obter alguns usuários e cursos existentes
        $users = User::take(5)->get();
        $courses = Course::take(3)->get();

        if ($users->isEmpty() || $courses->isEmpty()) {
            $this->command->info('Não há usuários ou cursos suficientes para criar matrículas.');
            return;
        }

        $this->command->info('Criando matrículas de exemplo...');

        foreach ($users as $index => $user) {
            foreach ($courses as $courseIndex => $course) {
                // Criar matrícula apenas para alguns usuários/cursos para variedade
                if (($index + $courseIndex) % 2 === 0) {
                    $enrolledAt = Carbon::now()->subDays(rand(1, 30));
                    
                    // Determinar status baseado em probabilidade
                    $statusRand = rand(1, 100);
                    if ($statusRand <= 70) {
                        $status = 'active';
                        $completedAt = null;
                        $progressPercentage = rand(10, 80);
                    } elseif ($statusRand <= 90) {
                        $status = 'completed';
                        $completedAt = $enrolledAt->copy()->addDays(rand(5, 25));
                        $progressPercentage = 100;
                    } else {
                        $status = 'suspended';
                        $completedAt = null;
                        $progressPercentage = rand(5, 50);
                    }

                    // Criar matrícula
                    $enrollment = UserCourseEnrollment::create([
                        'user_id' => $user->id,
                        'course_id' => $course->id,
                        'status' => $status,
                        'enrolled_at' => $enrolledAt,
                        'completed_at' => $completedAt,
                        'progress_percentage' => $progressPercentage,
                        'lessons_completed' => 0,
                        'total_lessons' => 0,
                        'final_grade' => $status === 'completed' ? rand(70, 100) : null,
                        'certificate_issued' => $status === 'completed' && rand(1, 100) <= 80,
                        'certificate_issued_at' => $status === 'completed' && rand(1, 100) <= 80 ? $completedAt : null,
                    ]);

                    // Obter aulas do curso e criar progresso
                    $lessons = CourseLesson::whereHas('module', function ($query) use ($course) {
                        $query->where('course_id', $course->id);
                    })->take(5)->get();

                    $lessonsCompleted = 0;
                    foreach ($lessons as $lessonIndex => $lesson) {
                        $lessonStarted = rand(1, 100) <= 80; // 80% chance de ter iniciado
                        
                        if ($lessonStarted) {
                            $lessonCompleted = false;
                            $watchTimeSeconds = 0;
                            $completionPercentage = 0;
                            
                            if ($status === 'completed' || rand(1, 100) <= 60) {
                                $lessonCompleted = true;
                                $lessonsCompleted++;
                                $watchTimeSeconds = $lesson->duration_minutes * 60;
                                $completionPercentage = 100;
                            } else {
                                $watchTimeSeconds = rand(30, $lesson->duration_minutes * 60 * 0.8);
                                $completionPercentage = round(($watchTimeSeconds / ($lesson->duration_minutes * 60)) * 100, 2);
                            }

                            UserLessonProgress::create([
                                'enrollment_id' => $enrollment->id,
                                'course_lesson_id' => $lesson->id,
                                'started' => true,
                                'completed' => $lessonCompleted,
                                'started_at' => $enrolledAt->copy()->addHours(rand(1, 48)),
                                'completed_at' => $lessonCompleted ? $enrolledAt->copy()->addDays(rand(1, 10)) : null,
                                'watch_time_seconds' => $watchTimeSeconds,
                                'total_duration_seconds' => $lesson->duration_minutes * 60,
                                'completion_percentage' => $completionPercentage,
                                'attempts' => rand(1, 3),
                                'last_accessed_at' => Carbon::now()->subDays(rand(0, 7)),
                            ]);
                        }
                    }

                    // Atualizar contadores na matrícula
                    $enrollment->update([
                        'lessons_completed' => $lessonsCompleted,
                        'total_lessons' => $lessons->count(),
                    ]);

                    $this->command->info("Matrícula criada: {$user->name} -> {$course->title} (Status: {$status})");
                }
            }
        }

        $this->command->info('Matrículas de exemplo criadas com sucesso!');
    }
}