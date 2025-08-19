<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpar dados existentes
        DB::table('roles')->delete();
        
        $roles = [
            [
                'name' => 'Administrador',
                'description' => 'Acesso total ao sistema com todas as permissões administrativas',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Gestor',
                'description' => 'Gestão de equipes e departamentos com permissões de supervisão',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Recursos Humanos',
                'description' => 'Gestão de funcionários, recrutamento e políticas de RH',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Tecnologia da Informação',
                'description' => 'Gestão de sistemas, infraestrutura e suporte técnico',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Financeiro',
                'description' => 'Gestão financeira, contabilidade e relatórios fiscais',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Comercial',
                'description' => 'Gestão de vendas, clientes e relacionamento comercial',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Marketing',
                'description' => 'Gestão de campanhas, comunicação e marketing digital',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Colaborador',
                'description' => 'Acesso básico ao sistema com permissões limitadas',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Estagiário',
                'description' => 'Acesso restrito para estagiários e aprendizes',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Consultor Externo',
                'description' => 'Acesso temporário para consultores e prestadores de serviços',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Auditoria',
                'description' => 'Acesso para auditoria interna e compliance',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Suporte',
                'description' => 'Atendimento ao cliente e suporte técnico',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];
        
        foreach ($roles as $role) {
            DB::table('roles')->insert($role);
        }
        
        $this->command->info('RoleSeeder: ' . count($roles) . ' roles criados com sucesso!');
    }
}
