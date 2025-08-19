<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RoleMappingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpar dados existentes
        DB::table('role_mappings')->delete();
        
        $roleMappings = [
            // Mapeamentos para Recursos Humanos
            [
                'department_id' => 1, // Recursos Humanos
                'job_title' => 'Gerente de RH',
                'ad_group' => 'RH_Managers',
                'role_id' => 3, // Recursos Humanos
                'priority' => 10,
                'active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_id' => 1, // Recursos Humanos
                'job_title' => 'Analista de RH',
                'ad_group' => 'RH_Analysts',
                'role_id' => 3, // Recursos Humanos
                'priority' => 20,
                'active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            
            // Mapeamentos para TI
            [
                'department_id' => 2, // TI
                'job_title' => 'Gerente de TI',
                'ad_group' => 'IT_Managers',
                'role_id' => 4, // Tecnologia da Informação
                'priority' => 10,
                'active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_id' => 2, // TI
                'job_title' => 'Desenvolvedor',
                'ad_group' => 'IT_Developers',
                'role_id' => 4, // Tecnologia da Informação
                'priority' => 20,
                'active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_id' => 2, // TI
                'job_title' => 'Analista de Sistemas',
                'ad_group' => 'IT_Analysts',
                'role_id' => 4, // Tecnologia da Informação
                'priority' => 30,
                'active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            
            // Mapeamentos para Financeiro
            [
                'department_id' => 3, // Financeiro
                'job_title' => 'Gerente Financeiro',
                'ad_group' => 'Finance_Managers',
                'role_id' => 5, // Financeiro
                'priority' => 10,
                'active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_id' => 3, // Financeiro
                'job_title' => 'Analista Financeiro',
                'ad_group' => 'Finance_Analysts',
                'role_id' => 5, // Financeiro
                'priority' => 20,
                'active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            
            // Mapeamentos para Comercial
            [
                'department_id' => 4, // Comercial
                'job_title' => 'Gerente de Vendas',
                'ad_group' => 'Sales_Managers',
                'role_id' => 6, // Comercial
                'priority' => 10,
                'active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_id' => 4, // Comercial
                'job_title' => 'Vendedor',
                'ad_group' => 'Sales_Representatives',
                'role_id' => 6, // Comercial
                'priority' => 20,
                'active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            
            // Mapeamentos para Marketing
            [
                'department_id' => 5, // Marketing
                'job_title' => 'Gerente de Marketing',
                'ad_group' => 'Marketing_Managers',
                'role_id' => 7, // Marketing
                'priority' => 10,
                'active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_id' => 5, // Marketing
                'job_title' => 'Analista de Marketing',
                'ad_group' => 'Marketing_Analysts',
                'role_id' => 7, // Marketing
                'priority' => 20,
                'active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            
            // Mapeamentos gerais
            [
                'department_id' => null,
                'job_title' => 'Estagiário',
                'ad_group' => 'Interns',
                'role_id' => 9, // Estagiário
                'priority' => 90,
                'active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'department_id' => null,
                'job_title' => 'Consultor',
                'ad_group' => 'External_Consultants',
                'role_id' => 10, // Consultor Externo
                'priority' => 80,
                'active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];
        
        foreach ($roleMappings as $mapping) {
            DB::table('role_mappings')->insert($mapping);
        }
        
        $this->command->info('RoleMappingSeeder: ' . count($roleMappings) . ' mapeamentos de papéis criados com sucesso!');
    }
}
