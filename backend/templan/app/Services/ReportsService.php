<?php

namespace App\Services;

use App\Models\SystemMetric;
use App\Models\User;
use App\Models\Simulado;
use App\Models\SimuladoAttempt;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ReportsService
{
    /**
     * Coleta e armazena métricas diárias do sistema
     */
    public function collectDailyMetrics(Carbon $date = null): void
    {
        $date = $date ?? now();
        $dateString = $date->toDateString();

        // Métricas de Usuários
        $this->collectUserMetrics($date);
        
        // Métricas de Simulados
        $this->collectSimuladoMetrics($date);
        
        // Métricas de Performance
        $this->collectPerformanceMetrics($date);
        
        // Métricas do Sistema
        $this->collectSystemMetrics($date);
    }

    /**
     * Coleta métricas diárias de usuários
     */
    public function collectUserMetrics(Carbon $date): void
    {
        $dateString = $date->toDateString();
        
        // Total de usuários
        $totalUsers = User::count();
        $this->storeMetric(
            'usuarios',
            'total_usuarios',
            ['count' => $totalUsers],
            $totalUsers,
            $dateString
        );

        // Usuários ativos (baseado em atividade recente - usando created_at como proxy)
        $activeUsers = User::where('created_at', '>=', $date->copy()->subDays(30))->count();
        $this->storeMetric(
            'usuarios',
            'usuarios_ativos',
            ['count' => $activeUsers, 'period' => '30_days'],
            $activeUsers,
            $dateString
        );

        // Novos usuários (criados hoje)
        $newUsers = User::whereDate('created_at', $dateString)->count();
        $this->storeMetric(
            'usuarios',
            'novos_usuarios',
            ['count' => $newUsers],
            $newUsers,
            $dateString
        );

        // Taxa de usuários ativos
        $activeRate = $totalUsers > 0 ? ($activeUsers / $totalUsers) * 100 : 0;
        $this->storeMetric(
            'usuarios',
            'taxa_usuarios_ativos',
            ['active' => $activeUsers, 'total' => $totalUsers],
            $activeRate,
            $dateString
        );
    }

    /**
     * Coleta métricas diárias de simulados
     */
    public function collectSimuladoMetrics(Carbon $date): void
    {
        $dateString = $date->toDateString();
        
        // Total de simulados
        $totalSimulados = Simulado::count();
        $this->storeMetric(
            'simulados',
            'total_simulados',
            ['count' => $totalSimulados],
            $totalSimulados,
            $dateString
        );

        // Simulados criados hoje
        $newSimulados = Simulado::whereDate('created_at', $dateString)->count();
        $this->storeMetric(
            'simulados',
            'novos_simulados',
            ['count' => $newSimulados],
            $newSimulados,
            $dateString
        );

        // Total de tentativas
        $totalAttempts = SimuladoAttempt::count();
        $this->storeMetric(
            'simulados',
            'total_tentativas',
            ['count' => $totalAttempts],
            $totalAttempts,
            $dateString
        );

        // Tentativas de hoje
        $todayAttempts = SimuladoAttempt::whereDate('created_at', $dateString)->count();
        $this->storeMetric(
            'simulados',
            'tentativas_hoje',
            ['count' => $todayAttempts],
            $todayAttempts,
            $dateString
        );

        // Simulados concluídos
        $completedAttempts = SimuladoAttempt::whereNotNull('submitted_at')->count();
        $this->storeMetric(
            'simulados',
            'simulados_concluidos',
            ['count' => $completedAttempts],
            $completedAttempts,
            $dateString
        );

        // Taxa de conclusão
        $completionRate = $totalAttempts > 0 ? ($completedAttempts / $totalAttempts) * 100 : 0;
        $this->storeMetric(
            'simulados',
            'taxa_conclusao',
            ['completed' => $completedAttempts, 'total' => $totalAttempts],
            $completionRate,
            $dateString
        );

        // Taxa de aprovação
        $passedAttempts = SimuladoAttempt::where('passed', true)->count();
        $approvalRate = $completedAttempts > 0 ? ($passedAttempts / $completedAttempts) * 100 : 0;
        $this->storeMetric(
            'simulados',
            'taxa_aprovacao',
            ['passed' => $passedAttempts, 'completed' => $completedAttempts],
            $approvalRate,
            $dateString
        );
    }

    /**
     * Coleta métricas de performance
     */
    public function collectPerformanceMetrics(Carbon $date): void
    {
        $dateString = $date->toDateString();
        
        // Tempo médio de conclusão dos simulados (usando created_at como proxy para início)
        $avgCompletionTime = SimuladoAttempt::whereNotNull('submitted_at')
            ->whereNotNull('created_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, submitted_at)) as avg_time')
            ->value('avg_time');

        $this->storeMetric(
            'performance',
            'tempo_medio_conclusao',
            ['time_minutes' => $avgCompletionTime],
            $avgCompletionTime ?? 0,
            $dateString
        );

        // Pontuação média dos simulados
        $avgScore = SimuladoAttempt::whereNotNull('score')
            ->avg('score');

        $this->storeMetric(
            'performance',
            'pontuacao_media',
            ['score' => $avgScore],
            $avgScore ?? 0,
            $dateString
        );

        // Distribuição de pontuações
        $scoreDistribution = SimuladoAttempt::whereNotNull('score')
            ->selectRaw('
                SUM(CASE WHEN score >= 90 THEN 1 ELSE 0 END) as excellent,
                SUM(CASE WHEN score >= 70 AND score < 90 THEN 1 ELSE 0 END) as good,
                SUM(CASE WHEN score >= 50 AND score < 70 THEN 1 ELSE 0 END) as average,
                SUM(CASE WHEN score < 50 THEN 1 ELSE 0 END) as poor
            ')
            ->first();

        $this->storeMetric(
            'performance',
            'distribuicao_pontuacoes',
            [
                'excellent' => $scoreDistribution->excellent ?? 0,
                'good' => $scoreDistribution->good ?? 0,
                'average' => $scoreDistribution->average ?? 0,
                'poor' => $scoreDistribution->poor ?? 0,
            ],
            0,
            $dateString
        );
    }

    /**
     * Coleta métricas do sistema
     */
    public function collectSystemMetrics(Carbon $date): void
    {
        $dateString = $date->toDateString();
        
        // Total de notificações
        $totalNotifications = Notification::count();
        $this->storeMetric(
            'sistema',
            'total_notificacoes',
            ['count' => $totalNotifications],
            $totalNotifications,
            $dateString
        );

        // Notificações enviadas hoje
        $todayNotifications = Notification::whereDate('created_at', $dateString)->count();
        $this->storeMetric(
            'sistema',
            'notificacoes_hoje',
            ['count' => $todayNotifications],
            $todayNotifications,
            $dateString
        );

        // Uso de armazenamento (simulação)
        $storageUsage = rand(20, 80); // Em produção, calcular o uso real
        $this->storeMetric(
            'sistema',
            'uso_armazenamento',
            ['percentage' => $storageUsage],
            $storageUsage,
            $dateString
        );

        // Uptime do sistema (simulação)
        $uptime = rand(95, 100); // Em produção, calcular o uptime real
        $this->storeMetric(
            'sistema',
            'uptime_sistema',
            ['percentage' => $uptime],
            $uptime,
            $dateString
        );
    }

    /**
     * Armazena uma métrica no banco de dados
     */
    private function storeMetric(
        string $type,
        string $name,
        array $data,
        ?float $value,
        string $date,
        string $periodType = SystemMetric::PERIOD_DAILY
    ): void {
        SystemMetric::updateOrCreate(
            [
                'metric_type' => $type,
                'metric_name' => $name,
                'period_type' => $periodType,
                'period_date' => $date,
            ],
            [
                'metric_data' => $data,
                'metric_value' => $value,
            ]
        );
    }

    /**
     * Obtém métricas para o dashboard
     */
    public function getDashboardMetrics(int $days = 30): array
    {
        $startDate = now()->subDays($days)->toDateString();
        $endDate = now()->toDateString();

        return [
            'overview' => $this->getOverviewMetrics(),
            'trends' => $this->getTrendMetrics($startDate, $endDate),
            'performance' => $this->getPerformanceMetrics($startDate, $endDate),
            'system' => $this->getSystemMetrics($startDate, $endDate),
        ];
    }

    /**
     * Obtém métricas de visão geral
     */
    private function getOverviewMetrics(): array
    {
        $latest = now()->toDateString();
        
        return [
            'total_usuarios' => $this->getLatestMetricValue(SystemMetric::TYPE_USUARIOS, 'total_usuarios', $latest),
            'usuarios_ativos' => $this->getLatestMetricValue(SystemMetric::TYPE_USUARIOS, 'usuarios_ativos', $latest),
            'total_simulados' => $this->getLatestMetricValue(SystemMetric::TYPE_SIMULADOS, 'total_simulados', $latest),
            'simulados_concluidos' => $this->getLatestMetricValue(SystemMetric::TYPE_SIMULADOS, 'simulados_concluidos', $latest),
            'taxa_aprovacao' => $this->getLatestMetricValue(SystemMetric::TYPE_SIMULADOS, 'taxa_aprovacao', $latest),
            'pontuacao_media' => $this->getLatestMetricValue(SystemMetric::TYPE_PERFORMANCE, 'pontuacao_media', $latest),
        ];
    }

    /**
     * Obtém métricas de tendência
     */
    private function getTrendMetrics(string $startDate, string $endDate): array
    {
        return [
            'usuarios_novos' => $this->getMetricTrend(SystemMetric::TYPE_USUARIOS, 'novos_usuarios', $startDate, $endDate),
            'simulados_novos' => $this->getMetricTrend(SystemMetric::TYPE_SIMULADOS, 'novos_simulados', $startDate, $endDate),
            'tentativas_diarias' => $this->getMetricTrend(SystemMetric::TYPE_SIMULADOS, 'tentativas_hoje', $startDate, $endDate),
            'taxa_conclusao' => $this->getMetricTrend(SystemMetric::TYPE_SIMULADOS, 'taxa_conclusao', $startDate, $endDate),
        ];
    }

    /**
     * Obtém métricas de performance
     */
    private function getPerformanceMetrics(string $startDate, string $endDate): array
    {
        $latest = now()->toDateString();
        
        return [
            'tempo_medio_conclusao' => $this->getLatestMetricValue(SystemMetric::TYPE_PERFORMANCE, 'tempo_medio_conclusao', $latest),
            'distribuicao_pontuacoes' => $this->getLatestMetricData(SystemMetric::TYPE_PERFORMANCE, 'distribuicao_pontuacoes', $latest),
            'pontuacao_trend' => $this->getMetricTrend(SystemMetric::TYPE_PERFORMANCE, 'pontuacao_media', $startDate, $endDate),
        ];
    }

    /**
     * Obtém métricas do sistema
     */
    private function getSystemMetrics(string $startDate, string $endDate): array
    {
        $latest = now()->toDateString();
        
        return [
            'uptime' => $this->getLatestMetricValue(SystemMetric::TYPE_SISTEMA, 'uptime_sistema', $latest),
            'uso_armazenamento' => $this->getLatestMetricValue(SystemMetric::TYPE_SISTEMA, 'uso_armazenamento', $latest),
            'notificacoes_trend' => $this->getMetricTrend(SystemMetric::TYPE_SISTEMA, 'notificacoes_hoje', $startDate, $endDate),
        ];
    }

    /**
     * Obtém o valor mais recente de uma métrica
     */
    private function getLatestMetricValue(string $type, string $name, string $date): ?float
    {
        $metric = SystemMetric::where('metric_type', $type)
            ->where('metric_name', $name)
            ->where('period_date', '<=', $date)
            ->orderBy('period_date', 'desc')
            ->first();

        return $metric?->metric_value;
    }

    /**
     * Obtém os dados mais recentes de uma métrica
     */
    private function getLatestMetricData(string $type, string $name, string $date): ?array
    {
        $metric = SystemMetric::where('metric_type', $type)
            ->where('metric_name', $name)
            ->where('period_date', '<=', $date)
            ->orderBy('period_date', 'desc')
            ->first();

        return $metric?->metric_data;
    }

    /**
     * Obtém a tendência de uma métrica ao longo do tempo
     */
    private function getMetricTrend(string $type, string $name, string $startDate, string $endDate): array
    {
        $metrics = SystemMetric::where('metric_type', $type)
            ->where('metric_name', $name)
            ->whereBetween('period_date', [$startDate, $endDate])
            ->orderBy('period_date')
            ->get(['period_date', 'metric_value']);

        return $metrics->map(function ($metric) {
            return [
                'date' => $metric->period_date->format('Y-m-d'),
                'value' => $metric->metric_value,
            ];
        })->toArray();
    }

    /**
     * Gera relatório de simulados por categoria
     */
    public function getSimuladosByCategory(): array
    {
        return Simulado::select('category', DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->orderBy('count', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Gera relatório de performance por usuário
     */
    public function getTopPerformers(int $limit = 10): array
    {
        return User::select('users.id', 'users.name', 'users.email')
            ->selectRaw('AVG(simulado_attempts.score) as avg_score')
            ->selectRaw('COUNT(simulado_attempts.id) as total_attempts')
            ->join('simulado_attempts', 'users.id', '=', 'simulado_attempts.user_id')
            ->whereNotNull('simulado_attempts.score')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderBy('avg_score', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Limpa métricas antigas
     */
    public function cleanupOldMetrics(int $daysToKeep = 365): int
    {
        $cutoffDate = now()->subDays($daysToKeep)->toDateString();
        
        return SystemMetric::where('period_date', '<', $cutoffDate)->delete();
    }
}