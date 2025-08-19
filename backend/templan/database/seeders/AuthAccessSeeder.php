<?php

namespace Database\Seeders;

use App\Models\RoleMapping;
use App\Models\PermissionRequest;
use App\Models\Delegation;
use App\Models\License;
use App\Models\LicenseAssignment;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuthAccessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpar dados existentes (usando DELETE para evitar problemas com FK)
        DB::table('license_assignments')->delete();
        DB::table('licenses')->delete();
        DB::table('delegations')->delete();
        DB::table('permission_requests')->delete();
        DB::table('role_mappings')->delete();
        
        // Criar mapeamentos de roles de exemplo
        $roleMappings = [
            [
                'department_id' => 1, // TI
                'job_title' => 'Desenvolvedor Senior',
                'ad_group' => 'DEV_SENIOR',
                'role_id' => 2, // Informática
                'priority' => 100,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'department_id' => 1, // TI
                'job_title' => 'Administrador de Sistema',
                'ad_group' => 'SYS_ADMIN',
                'role_id' => 2, // Informática
                'priority' => 90,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'department_id' => null,
                'job_title' => 'Gestor de RH',
                'ad_group' => 'HR_MANAGER',
                'role_id' => 1, // RH
                'priority' => 80,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        
        foreach ($roleMappings as $mapping) {
            RoleMapping::create($mapping);
        }
        
        // Criar solicitações de permissão de exemplo
        $permissionRequests = [
            [
                'requester_id' => 5, // Maria Santos
                'target' => 'role_change',
                'change_set' => json_encode([
                    'from_role' => 'Colaborador',
                    'to_role' => 'Gestor',
                    'reason' => 'Promoção para líder de equipa'
                ]),
                'status' => 'pending',
                'approver_id' => null,
                'reason' => 'Solicitação de mudança de cargo devido a promoção',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'requester_id' => 6, // Pedro Costa
                'target' => 'access_request',
                'change_set' => json_encode([
                    'resource' => 'financial_reports',
                    'access_level' => 'read',
                    'duration' => '30 days'
                ]),
                'status' => 'approved',
                'approver_id' => 1, // Admin
                'reason' => 'Acesso temporário para auditoria financeira',
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(3),
            ],
            [
                'requester_id' => 7, // Ana Ferreira
                'target' => 'department_transfer',
                'change_set' => json_encode([
                    'from_department' => 'TI',
                    'to_department' => 'Vendas',
                    'effective_date' => now()->addDays(30)->format('Y-m-d')
                ]),
                'status' => 'rejected',
                'approver_id' => 1, // Admin
                'reason' => 'Transferência rejeitada - recursos insuficientes no departamento de destino',
                'created_at' => now()->subDays(7),
                'updated_at' => now()->subDays(1),
            ],
        ];
        
        foreach ($permissionRequests as $request) {
            PermissionRequest::create($request);
        }
        
        // Criar delegações de exemplo
        $delegations = [
            [
                'delegator_id' => 1, // Admin
                'delegatee_id' => 2, // Administrador Principal
                'scope' => json_encode([
                    'permissions' => ['users.manage', 'roles.manage'],
                    'departments' => ['TI'],
                    'duration' => '7 days'
                ]),
                'starts_at' => now(),
                'ends_at' => now()->addDays(7),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'delegator_id' => 2, // Administrador Principal
                'delegatee_id' => 5, // Maria Santos
                'scope' => json_encode([
                    'permissions' => ['users.view', 'audit.view'],
                    'departments' => ['TI'],
                    'duration' => '14 days'
                ]),
                'starts_at' => now()->subDays(3),
                'ends_at' => now()->addDays(11),
                'status' => 'active',
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
            [
                'delegator_id' => 1, // Admin
                'delegatee_id' => 6, // Pedro Costa
                'scope' => json_encode([
                    'permissions' => ['audit.view', 'security.view'],
                    'departments' => ['all'],
                    'duration' => '30 days'
                ]),
                'starts_at' => now()->subDays(10),
                'ends_at' => now()->subDays(3),
                'status' => 'expired',
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(3),
            ],
        ];
        
        foreach ($delegations as $delegation) {
            Delegation::create($delegation);
        }
        
        // Criar licenças de exemplo
        $licenses = [
            [
                'product' => 'Microsoft 365 Business Premium',
                'seats_total' => 50,
                'seats_used' => 32,
                'meta' => json_encode([
                    'vendor' => 'Microsoft',
                    'renewal_date' => '2025-12-31',
                    'cost_per_seat' => 22.00,
                    'features' => ['Office Apps', 'Teams', 'SharePoint', 'Exchange']
                ]),
                'created_at' => now()->subMonths(6),
                'updated_at' => now(),
            ],
            [
                'product' => 'Adobe Creative Cloud',
                'seats_total' => 10,
                'seats_used' => 8,
                'meta' => json_encode([
                    'vendor' => 'Adobe',
                    'renewal_date' => '2025-06-30',
                    'cost_per_seat' => 52.99,
                    'features' => ['Photoshop', 'Illustrator', 'InDesign', 'Premiere Pro']
                ]),
                'created_at' => now()->subMonths(3),
                'updated_at' => now(),
            ],
            [
                'product' => 'Slack Pro',
                'seats_total' => 100,
                'seats_used' => 45,
                'meta' => json_encode([
                    'vendor' => 'Slack',
                    'renewal_date' => '2025-09-15',
                    'cost_per_seat' => 7.25,
                    'features' => ['Unlimited messages', 'Apps & integrations', 'Guest access']
                ]),
                'created_at' => now()->subMonths(4),
                'updated_at' => now(),
            ],
        ];
        
        foreach ($licenses as $license) {
            License::create($license);
        }
        
        // Criar atribuições de licenças de exemplo
        $licenseAssignments = [
            // Microsoft 365 assignments
            ['license_id' => 1, 'user_id' => 1, 'assigned_at' => now()->subMonths(6)],
            ['license_id' => 1, 'user_id' => 2, 'assigned_at' => now()->subMonths(6)],
            ['license_id' => 1, 'user_id' => 3, 'assigned_at' => now()->subMonths(5)],
            ['license_id' => 1, 'user_id' => 5, 'assigned_at' => now()->subMonths(4)],
            ['license_id' => 1, 'user_id' => 6, 'assigned_at' => now()->subMonths(3)],
            
            // Adobe Creative Cloud assignments
            ['license_id' => 2, 'user_id' => 7, 'assigned_at' => now()->subMonths(3)],
            ['license_id' => 2, 'user_id' => 8, 'assigned_at' => now()->subMonths(2)],
            ['license_id' => 2, 'user_id' => 9, 'assigned_at' => now()->subMonths(1)],
            
            // Slack Pro assignments
            ['license_id' => 3, 'user_id' => 1, 'assigned_at' => now()->subMonths(4)],
            ['license_id' => 3, 'user_id' => 2, 'assigned_at' => now()->subMonths(4)],
            ['license_id' => 3, 'user_id' => 5, 'assigned_at' => now()->subMonths(3)],
            ['license_id' => 3, 'user_id' => 6, 'assigned_at' => now()->subMonths(3)],
            ['license_id' => 3, 'user_id' => 7, 'assigned_at' => now()->subMonths(2)],
        ];
        
        foreach ($licenseAssignments as $assignment) {
            LicenseAssignment::create(array_merge($assignment, [
                'created_at' => $assignment['assigned_at'],
                'updated_at' => $assignment['assigned_at'],
            ]));
        }
        
        $this->command->info('AuthAccessSeeder: Dados de autenticação e acesso criados com sucesso!');
        $this->command->info('- ' . count($roleMappings) . ' mapeamentos de roles');
        $this->command->info('- ' . count($permissionRequests) . ' solicitações de permissão');
        $this->command->info('- ' . count($delegations) . ' delegações');
        $this->command->info('- ' . count($licenses) . ' licenças');
        $this->command->info('- ' . count($licenseAssignments) . ' atribuições de licenças');
    }
}