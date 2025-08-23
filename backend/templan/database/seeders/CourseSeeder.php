<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\CourseLesson;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a sample course with modules and lessons
        $course = Course::create([
            'title' => 'OnHCP: Fundamentos de Boas Práticas Clínicas',
            'description' => 'Curso introdutório cobrindo princípios essenciais, segurança do paciente e ética.',
            'thumbnail_path' => null,
            'status' => 'draft', // valores aceitos pelo sistema: draft/published (ajuste se necessário)
            'sort_index' => 1,
        ]);

        // Module 1
        $m1 = CourseModule::create([
            'course_id' => $course->id,
            'title' => 'Introdução às Boas Práticas',
            'description' => 'Visão geral, objetivos e terminologias.',
            'sort_index' => 1,
        ]);

        CourseLesson::create([
            'course_module_id' => $m1->id,
            'title' => 'O que são Boas Práticas Clínicas?',
            'description' => 'Definições, histórico e importância.',
            'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'duration_seconds' => 480,
            'sort_index' => 1,
        ]);

        CourseLesson::create([
            'course_module_id' => $m1->id,
            'title' => 'Ética e Responsabilidade',
            'description' => 'Princípios éticos e responsabilidades da equipe.',
            'video_url' => null,
            'duration_seconds' => 540,
            'sort_index' => 2,
        ]);

        // Module 2
        $m2 = CourseModule::create([
            'course_id' => $course->id,
            'title' => 'Segurança do Paciente',
            'description' => 'Protocolos de segurança e gestão de risco.',
            'sort_index' => 2,
        ]);

        CourseLesson::create([
            'course_module_id' => $m2->id,
            'title' => 'Identificação Correta do Paciente',
            'description' => 'Procedimentos e checagens.',
            'video_url' => null,
            'duration_seconds' => 420,
            'sort_index' => 1,
        ]);

        CourseLesson::create([
            'course_module_id' => $m2->id,
            'title' => 'Comunicação Efetiva',
            'description' => 'Estratégias de comunicação entre times.',
            'video_url' => null,
            'duration_seconds' => 600,
            'sort_index' => 2,
        ]);
    }
}
