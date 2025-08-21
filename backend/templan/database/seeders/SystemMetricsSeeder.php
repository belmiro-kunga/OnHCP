<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemMetric;
use Carbon\Carbon;

class SystemMetricsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Criando métricas de exemplo...');
        
        // Gerar métricas para os últimos 30 dias
        $startDate = now()->subDays(30);
        $endDate = now();
        
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $this->createMetricsForDate($date);
        }
        
        $this->command->info('Métricas de exemplo criadas com sucesso!');
    }
    
    /**
     * Cria métricas para uma data específica
     */
    private function createMetricsForDate(Carbon $date): void
    {
        $dateString = $date->toDateString();
        $dayOfYear = $date->dayOfYear;
        
        // Métricas de Usuários
        $this->createUserMetrics($dateString, $dayOfYear);
        
        // Métricas de Simulados
        $this->createSimuladoMetrics($dateString, $dayOfYear);
        
        // Métricas de Performance
        $this->createPerformanceMetrics($dateString, $dayOfYear);
        
        // Métricas do Sistema
        $this->createSystemMetrics($dateString, $dayOfYear);
    }
    
    /**
     * Cria métricas de usuários
     */
    private function createUserMetrics(string $dateString, int $dayOfYear): void
    {
        // Simular crescimento gradual de usuários
        $baseUsers = 100;
        $totalUsers = $baseUsers + intval($dayOfYear * 0.5);
        $activeUsers = intval($totalUsers * 0.7); // 70% dos usuários ativos
        $newUsers = rand(0, 5); // 0-5 novos usuários por dia
        
        SystemMetric::create([
            'metric_type' => 'usuarios',
            'metric_name' => 'total_usuarios',
            'metric_data' => ['count' => $totalUsers],
            'metric_value' => $totalUsers,
            'period_type' => 'daily',
            'period_date' => $dateString,
        ]);
        
        SystemMetric::create([
            'metric_type' => 'usuarios',
            'metric_name' => 'usuarios_ativos',
            'metric_data' => ['count' => $activeUsers, 'period' => '30_days'],
            'metric_value' => $activeUsers,
            'period_type' => 'daily',
            'period_date' => $dateString,
        ]);
        
        SystemMetric::create([
            'metric_type' => 'usuarios',
            'metric_name' => 'novos_usuarios',
            'metric_data' => ['count' => $newUsers],
            'metric_value' => $newUsers,
            'period_type' => 'daily',
            'period_date' => $dateString,
        ]);
        
        $activeRate = $totalUsers > 0 ? ($activeUsers / $totalUsers) * 100 : 0;
        SystemMetric::create([
            'metric_type' => 'usuarios',
            'metric_name' => 'taxa_usuarios_ativos',
            'metric_data' => ['active' => $activeUsers, 'total' => $totalUsers],
            'metric_value' => $activeRate,
            'period_type' => 'daily',
            'period_date' => $dateString,
        ]);
    }
    
    /**
     * Cria métricas de simulados
     */
    private function createSimuladoMetrics(string $dateString, int $dayOfYear): void
    {
        // Simular crescimento de simulados
        $baseSimulados = 20;
        $totalSimulados = $baseSimulados + intval($dayOfYear * 0.1);
        $newSimulados = rand(0, 2); // 0-2 novos simulados por dia
        
        // Simular tentativas diárias
        $todayAttempts = rand(5, 25);
        $totalAttempts = 500 + ($dayOfYear * 3); // Crescimento acumulativo
        
        // Simular conclusões
        $completedAttempts = intval($totalAttempts * 0.8); // 80% de conclusão
        $passedAttempts = intval($completedAttempts * 0.75); // 75% de aprovação
        
        SystemMetric::create([
            'metric_type' => 'simulados',
            'metric_name' => 'total_simulados',
            'metric_data' => ['count' => $totalSimulados],
            'metric_value' => $totalSimulados,
            'period_type' => 'daily',
            'period_date' => $dateString,
        ]);
        
        SystemMetric::create([
            'metric_type' => 'simulados',
            'metric_name' => 'novos_simulados',
            'metric_data' => ['count' => $newSimulados],
            'metric_value' => $newSimulados,
            'period_type' => 'daily',
            'period_date' => $dateString,
        ]);
        
        SystemMetric::create([
            'metric_type' => 'simulados',
            'metric_name' => 'total_tentativas',
            'metric_data' => ['count' => $totalAttempts],
            'metric_value' => $totalAttempts,
            'period_type' => 'daily',
            'period_date' => $dateString,
        ]);
        
        SystemMetric::create([
            'metric_type' => 'simulados',
            'metric_name' => 'tentativas_hoje',
            'metric_data' => ['count' => $todayAttempts],
            'metric_value' => $todayAttempts,
            'period_type' => 'daily',
            'period_date' => $dateString,
        ]);
        
        SystemMetric::create([
            'metric_type' => 'simulados',
            'metric_name' => 'simulados_concluidos',
            'metric_data' => ['count' => $completedAttempts],
            'metric_value' => $completedAttempts,
            'period_type' => 'daily',
            'period_date' => $dateString,
        ]);
        
        $completionRate = $totalAttempts > 0 ? ($completedAttempts / $totalAttempts) * 100 : 0;
        SystemMetric::create([
            'metric_type' => 'simulados',
            'metric_name' => 'taxa_conclusao',
            'metric_data' => ['completed' => $completedAttempts, 'total' => $totalAttempts],
            'metric_value' => $completionRate,
            'period_type' => 'daily',
            'period_date' => $dateString,
        ]);
        
        $approvalRate = $completedAttempts > 0 ? ($passedAttempts / $completedAttempts) * 100 : 0;
        SystemMetric::create([
            'metric_type' => 'simulados',
            'metric_name' => 'taxa_aprovacao',
            'metric_data' => ['passed' => $passedAttempts, 'completed' => $completedAttempts],
            'metric_value' => $approvalRate,
            'period_type' => 'daily',
            'period_date' => $dateString,
        ]);
    }
    
    /**
     * Cria métricas de performance
     */
    private function createPerformanceMetrics(string $dateString, int $dayOfYear): void
    {
        // Simular tempo médio de conclusão (em minutos)
        $avgCompletionTime = rand(15, 45);
        
        // Simular pontuação média
        $avgScore = rand(65, 85) + (rand(0, 100) / 100);
        
        // Simular distribuição de pontuações
        $excellent = rand(10, 30);
        $good = rand(20, 40);
        $average = rand(15, 25);
        $poor = rand(5, 15);
        
        SystemMetric::create([
            'metric_type' => 'performance',
            'metric_name' => 'tempo_medio_conclusao',
            'metric_data' => ['time_minutes' => $avgCompletionTime],
            'metric_value' => $avgCompletionTime,
            'period_type' => 'daily',
            'period_date' => $dateString,
        ]);
        
        SystemMetric::create([
            'metric_type' => 'performance',
            'metric_name' => 'pontuacao_media',
            'metric_data' => ['score' => $avgScore],
            'metric_value' => $avgScore,
            'period_type' => 'daily',
            'period_date' => $dateString,
        ]);
        
        SystemMetric::create([
            'metric_type' => 'performance',
            'metric_name' => 'distribuicao_pontuacoes',
            'metric_data' => [
                'excellent' => $excellent,
                'good' => $good,
                'average' => $average,
                'poor' => $poor,
            ],
            'metric_value' => 0,
            'period_type' => 'daily',
            'period_date' => $dateString,
        ]);
    }
    
    /**
     * Cria métricas do sistema
     */
    private function createSystemMetrics(string $dateString, int $dayOfYear): void
    {
        // Simular notificações
        $totalNotifications = 1000 + ($dayOfYear * 2);
        $todayNotifications = rand(5, 20);
        
        // Simular uso de armazenamento (crescimento gradual)
        $storageUsage = min(20 + ($dayOfYear * 0.2), 80);
        
        // Simular uptime (alta disponibilidade)
        $uptime = rand(98, 100) + (rand(0, 100) / 100);
        
        SystemMetric::create([
            'metric_type' => 'sistema',
            'metric_name' => 'total_notificacoes',
            'metric_data' => ['count' => $totalNotifications],
            'metric_value' => $totalNotifications,
            'period_type' => 'daily',
            'period_date' => $dateString,
        ]);
        
        SystemMetric::create([
            'metric_type' => 'sistema',
            'metric_name' => 'notificacoes_hoje',
            'metric_data' => ['count' => $todayNotifications],
            'metric_value' => $todayNotifications,
            'period_type' => 'daily',
            'period_date' => $dateString,
        ]);
        
        SystemMetric::create([
            'metric_type' => 'sistema',
            'metric_name' => 'uso_armazenamento',
            'metric_data' => ['percentage' => $storageUsage],
            'metric_value' => $storageUsage,
            'period_type' => 'daily',
            'period_date' => $dateString,
        ]);
        
        SystemMetric::create([
            'metric_type' => 'sistema',
            'metric_name' => 'uptime_sistema',
            'metric_data' => ['percentage' => $uptime],
            'metric_value' => $uptime,
            'period_type' => 'daily',
            'period_date' => $dateString,
        ]);
    }
}