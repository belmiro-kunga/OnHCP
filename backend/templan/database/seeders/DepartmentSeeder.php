<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;
use Carbon\Carbon;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Recursos Humanos',
                'code' => 'RH',
                'description' => 'Departamento responsável pela gestão de pessoas e recursos humanos',
                'manager_id' => null,
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Tecnologia da Informação',
                'code' => 'TI',
                'description' => 'Departamento de desenvolvimento e infraestrutura tecnológica',
                'manager_id' => null,
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Financeiro',
                'code' => 'FIN',
                'description' => 'Departamento de gestão financeira e contabilidade',
                'manager_id' => null,
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Comercial',
                'code' => 'COM',
                'description' => 'Departamento de vendas e relacionamento com clientes',
                'manager_id' => null,
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Marketing',
                'code' => 'MKT',
                'description' => 'Departamento de marketing e comunicação',
                'manager_id' => null,
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Desenvolvimento',
                'code' => 'DEV',
                'description' => 'Equipe de desenvolvimento de software',
                'manager_id' => null,
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Infraestrutura',
                'code' => 'INFRA',
                'description' => 'Equipe de infraestrutura e suporte técnico',
                'manager_id' => null,
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Suporte',
                'code' => 'SUP',
                'description' => 'Departamento de suporte ao cliente',
                'manager_id' => null,
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
