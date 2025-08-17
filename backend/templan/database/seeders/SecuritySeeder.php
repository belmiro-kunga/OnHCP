<?php

namespace Database\Seeders;

use App\Models\IpPolicy;
use App\Models\AuditLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SecuritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpar dados existentes
        DB::table('ip_policies')->truncate();
        DB::table('audit_logs')->truncate();
        
        // Criar políticas de IP de exemplo
        $policies = [
            [
                'type' => 'whitelist',
                'ip_cidr' => '192.168.1.0/24',
                'reason' => 'Rede interna da empresa',
                'created_by' => 1, // Admin user
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'whitelist',
                'ip_cidr' => '10.0.0.0/8',
                'reason' => 'Rede privada corporativa',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'blacklist',
                'ip_cidr' => '203.0.113.0/24',
                'reason' => 'Rede suspeita - múltiplas tentativas de invasão',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'blacklist',
                'ip_cidr' => '198.51.100.50/32',
                'reason' => 'IP bloqueado por atividade maliciosa',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($policies as $policy) {
            IpPolicy::create($policy);
        }

        // Criar logs de auditoria de exemplo
        $auditLogs = [
            [
                'action' => 'user_login',
                'ip' => '192.168.1.100',
                'user_id' => 1,
                'meta' => json_encode([
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'success' => true,
                ]),
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ],
            [
                'action' => 'user_login_failed',
                'ip' => '203.0.113.25',
                'user_id' => null,
                'meta' => json_encode([
                    'email' => 'admin@hemeracapital.com',
                    'reason' => 'Senha incorreta',
                ]),
                'created_at' => now()->subHours(1),
                'updated_at' => now()->subHours(1),
            ],
            [
                'action' => 'ip_whitelisted',
                'ip' => '192.168.1.1',
                'user_id' => 1,
                'meta' => json_encode([
                    'ip_cidr' => '192.168.1.0/24',
                    'reason' => 'Rede interna da empresa',
                ]),
                'created_at' => now()->subMinutes(30),
                'updated_at' => now()->subMinutes(30),
            ],
            [
                'action' => 'ip_blacklisted',
                'ip' => '192.168.1.1',
                'user_id' => 1,
                'meta' => json_encode([
                    'ip_cidr' => '203.0.113.0/24',
                    'reason' => 'Rede suspeita - múltiplas tentativas de invasão',
                ]),
                'created_at' => now()->subMinutes(15),
                'updated_at' => now()->subMinutes(15),
            ],
            [
                'action' => 'user_logout',
                'ip' => '192.168.1.100',
                'user_id' => 2,
                'meta' => json_encode([
                    'session_duration' => '2h 15m',
                ]),
                'created_at' => now()->subMinutes(5),
                'updated_at' => now()->subMinutes(5),
            ],
        ];

        foreach ($auditLogs as $log) {
            AuditLog::create($log);
        }

        $this->command->info('Dados de segurança criados com sucesso!');
        $this->command->info('- ' . count($policies) . ' políticas de IP criadas');
        $this->command->info('- ' . count($auditLogs) . ' logs de auditoria criados');
    }
}