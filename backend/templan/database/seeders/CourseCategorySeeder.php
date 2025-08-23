<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CourseCategory;

class CourseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Geral',
                'description' => 'Cursos de conhecimento geral e introdutórios',
                'slug' => 'geral',
                'color' => '#3B82F6',
                'icon' => 'book-open',
                'is_active' => true,
                'sort_index' => 1,
            ],
            [
                'name' => 'Segurança',
                'description' => 'Cursos relacionados à segurança da informação e proteção de dados',
                'slug' => 'seguranca',
                'color' => '#EF4444',
                'icon' => 'shield-check',
                'is_active' => true,
                'sort_index' => 2,
            ],
            [
                'name' => 'Tecnologia',
                'description' => 'Cursos de tecnologia, programação e desenvolvimento',
                'slug' => 'tecnologia',
                'color' => '#10B981',
                'icon' => 'code',
                'is_active' => true,
                'sort_index' => 3,
            ],
            [
                'name' => 'Recursos Humanos',
                'description' => 'Cursos sobre gestão de pessoas e desenvolvimento profissional',
                'slug' => 'recursos-humanos',
                'color' => '#F59E0B',
                'icon' => 'users',
                'is_active' => true,
                'sort_index' => 4,
            ],
            [
                'name' => 'Compliance',
                'description' => 'Cursos sobre conformidade, regulamentações e políticas',
                'slug' => 'compliance',
                'color' => '#8B5CF6',
                'icon' => 'clipboard-check',
                'is_active' => true,
                'sort_index' => 5,
            ],
        ];

        foreach ($categories as $category) {
            CourseCategory::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}