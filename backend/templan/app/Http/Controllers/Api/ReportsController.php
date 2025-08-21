<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportsService;
use App\Models\SystemMetric;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ReportsController extends Controller
{
    protected ReportsService $reportsService;

    public function __construct(ReportsService $reportsService)
    {
        $this->reportsService = $reportsService;
        $this->middleware('auth:sanctum');
        $this->middleware('admin')->except(['myStats']);
    }

    /**
     * Obtém métricas do dashboard principal
     */
    public function dashboard(Request $request): JsonResponse
    {
        $request->validate([
            'days' => 'integer|min:1|max:365',
        ]);

        $days = $request->get('days', 30);
        $cacheKey = "dashboard_metrics_{$days}";

        $metrics = Cache::remember($cacheKey, 300, function () use ($days) {
            return $this->reportsService->getDashboardMetrics($days);
        });

        return response()->json([
            'success' => true,
            'data' => $metrics,
            'meta' => [
                'period_days' => $days,
                'generated_at' => now()->toISOString(),
                'cache_ttl' => 300,
            ],
        ]);
    }

    /**
     * Obtém métricas de visão geral
     */
    public function overview(): JsonResponse
    {
        $cacheKey = 'overview_metrics';

        $overview = Cache::remember($cacheKey, 600, function () {
            $latest = now()->toDateString();
            return [
                'users' => [
                    'total' => $this->getLatestMetricValue('usuarios', 'total_usuarios', $latest),
                    'active' => $this->getLatestMetricValue('usuarios', 'usuarios_ativos', $latest),
                    'new_today' => $this->getLatestMetricValue('usuarios', 'novos_usuarios', $latest),
                ],
                'simulados' => [
                    'total' => $this->getLatestMetricValue('simulados', 'total_simulados', $latest),
                    'completed' => $this->getLatestMetricValue('simulados', 'simulados_concluidos', $latest),
                    'completion_rate' => $this->getLatestMetricValue('simulados', 'taxa_conclusao', $latest),
                    'approval_rate' => $this->getLatestMetricValue('simulados', 'taxa_aprovacao', $latest),
                ],
                'performance' => [
                    'avg_score' => $this->getLatestMetricValue('performance', 'pontuacao_media', $latest),
                    'avg_completion_time' => $this->getLatestMetricValue('performance', 'tempo_medio_conclusao', $latest),
                ],
                'system' => [
                    'uptime' => $this->getLatestMetricValue('sistema', 'uptime_sistema', $latest),
                    'storage_usage' => $this->getLatestMetricValue('sistema', 'uso_armazenamento', $latest),
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $overview,
        ]);
    }

    /**
     * Obtém métricas de tendência
     */
    public function trends(Request $request): JsonResponse
    {
        $request->validate([
            'days' => 'integer|min:7|max:365',
            'metrics' => 'array',
            'metrics.*' => 'string|in:users,simulados,attempts,completion_rate',
        ]);

        $days = $request->get('days', 30);
        $requestedMetrics = $request->get('metrics', ['users', 'simulados', 'attempts']);
        
        $startDate = now()->subDays($days)->toDateString();
        $endDate = now()->toDateString();
        
        $cacheKey = "trends_" . md5(json_encode($requestedMetrics) . $days);

        $trends = Cache::remember($cacheKey, 300, function () use ($requestedMetrics, $startDate, $endDate) {
            $data = [];

            if (in_array('users', $requestedMetrics)) {
                $data['users'] = $this->getMetricTrend('usuarios', 'novos_usuarios', $startDate, $endDate);
            }

            if (in_array('simulados', $requestedMetrics)) {
                $data['simulados'] = $this->getMetricTrend('simulados', 'novos_simulados', $startDate, $endDate);
            }

            if (in_array('attempts', $requestedMetrics)) {
                $data['attempts'] = $this->getMetricTrend('simulados', 'tentativas_hoje', $startDate, $endDate);
            }

            if (in_array('completion_rate', $requestedMetrics)) {
                $data['completion_rate'] = $this->getMetricTrend('simulados', 'taxa_conclusao', $startDate, $endDate);
            }

            return $data;
        });

        return response()->json([
            'success' => true,
            'data' => $trends,
            'meta' => [
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate,
                    'days' => $days,
                ],
                'metrics' => $requestedMetrics,
            ],
        ]);
    }

    /**
     * Obtém relatório de simulados por categoria
     */
    public function simuladosByCategory(): JsonResponse
    {
        $cacheKey = 'simulados_by_category';

        $data = Cache::remember($cacheKey, 1800, function () {
            return $this->reportsService->getSimuladosByCategory();
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Obtém relatório de top performers
     */
    public function topPerformers(Request $request): JsonResponse
    {
        $request->validate([
            'limit' => 'integer|min:5|max:50',
        ]);

        $limit = $request->get('limit', 10);
        $cacheKey = "top_performers_{$limit}";

        $data = Cache::remember($cacheKey, 900, function () use ($limit) {
            return $this->reportsService->getTopPerformers($limit);
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'limit' => $limit,
            ],
        ]);
    }

    /**
     * Obtém distribuição de pontuações
     */
    public function scoreDistribution(): JsonResponse
    {
        $cacheKey = 'score_distribution';

        $data = Cache::remember($cacheKey, 600, function () {
            $latest = now()->toDateString();
            return $this->getLatestMetricData('performance', 'distribuicao_pontuacoes', $latest);
        });

        return response()->json([
            'success' => true,
            'data' => $data ?: [
                'excellent' => 0,
                'good' => 0,
                'average' => 0,
                'poor' => 0,
            ],
        ]);
    }

    /**
     * Obtém métricas de performance
     */
    public function performance(Request $request): JsonResponse
    {
        $request->validate([
            'days' => 'integer|min:7|max:365',
        ]);

        $days = $request->get('days', 30);
        $startDate = now()->subDays($days)->toDateString();
        $endDate = now()->toDateString();
        
        $cacheKey = "performance_metrics_{$days}";

        $data = Cache::remember($cacheKey, 300, function () use ($startDate, $endDate) {
            return $this->reportsService->getPerformanceMetrics($startDate, $endDate);
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate,
                    'days' => $days,
                ],
            ],
        ]);
    }

    /**
     * Obtém métricas do sistema
     */
    public function system(Request $request): JsonResponse
    {
        $request->validate([
            'days' => 'integer|min:7|max:365',
        ]);

        $days = $request->get('days', 30);
        $startDate = now()->subDays($days)->toDateString();
        $endDate = now()->toDateString();
        
        $cacheKey = "system_metrics_{$days}";

        $data = Cache::remember($cacheKey, 300, function () use ($startDate, $endDate) {
            return $this->reportsService->getSystemMetrics($startDate, $endDate);
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate,
                    'days' => $days,
                ],
            ],
        ]);
    }

    /**
     * Força a coleta de métricas (apenas para admins)
     */
    public function collectMetrics(): JsonResponse
    {
        try {
            $this->reportsService->collectDailyMetrics();
            
            // Limpa cache relacionado
            Cache::forget('dashboard_metrics_30');
            Cache::forget('overview_metrics');
            Cache::tags(['reports'])->flush();

            return response()->json([
                'success' => true,
                'message' => 'Métricas coletadas com sucesso',
                'collected_at' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao coletar métricas: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtém estatísticas pessoais do usuário (disponível para todos)
     */
    public function myStats(Request $request): JsonResponse
    {
        $user = $request->user();
        $cacheKey = "user_stats_{$user->id}";

        $stats = Cache::remember($cacheKey, 600, function () use ($user) {
            return [
                'total_attempts' => $user->simuladoAttempts()->count(),
                'completed_attempts' => $user->simuladoAttempts()->whereNotNull('submitted_at')->count(),
                'passed_attempts' => $user->simuladoAttempts()->where('passed', true)->count(),
                'avg_score' => $user->simuladoAttempts()->whereNotNull('score')->avg('score'),
                'best_score' => $user->simuladoAttempts()->whereNotNull('score')->max('score'),
                'total_time_spent' => $user->simuladoAttempts()
                    ->whereNotNull('started_at')
                    ->whereNotNull('submitted_at')
                    ->selectRaw('SUM(TIMESTAMPDIFF(MINUTE, started_at, submitted_at)) as total_minutes')
                    ->value('total_minutes'),
                'recent_activity' => $user->simuladoAttempts()
                    ->with('simulado:id,title')
                    ->latest()
                    ->limit(5)
                    ->get(['id', 'simulado_id', 'score', 'passed', 'submitted_at'])
                    ->map(function ($attempt) {
                        return [
                            'simulado_title' => $attempt->simulado->title,
                            'score' => $attempt->score,
                            'passed' => $attempt->passed,
                            'completed_at' => $attempt->submitted_at?->format('Y-m-d H:i:s'),
                        ];
                    }),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Exporta relatórios em diferentes formatos
     */
    public function export(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string|in:dashboard,performance,users,simulados',
            'format' => 'string|in:json,csv',
            'days' => 'integer|min:1|max:365',
        ]);

        $type = $request->get('type');
        $format = $request->get('format', 'json');
        $days = $request->get('days', 30);

        try {
            $data = match ($type) {
                'dashboard' => $this->reportsService->getDashboardMetrics($days),
                'performance' => $this->reportsService->getPerformanceMetrics(
                    now()->subDays($days)->toDateString(),
                    now()->toDateString()
                ),
                'users' => $this->reportsService->getTopPerformers(50),
                'simulados' => $this->reportsService->getSimuladosByCategory(),
            };

            if ($format === 'csv') {
                // Em uma implementação real, você converteria para CSV
                return response()->json([
                    'success' => false,
                    'message' => 'Exportação CSV será implementada em versão futura',
                ], 501);
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'meta' => [
                    'type' => $type,
                    'format' => $format,
                    'period_days' => $days,
                    'exported_at' => now()->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar dados: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Limpa cache de relatórios
     */
    public function clearCache(): JsonResponse
    {
        try {
            Cache::tags(['reports'])->flush();
            Cache::forget('dashboard_metrics_30');
            Cache::forget('overview_metrics');
            Cache::forget('simulados_by_category');
            Cache::forget('score_distribution');

            return response()->json([
                'success' => true,
                'message' => 'Cache de relatórios limpo com sucesso',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao limpar cache: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Métodos auxiliares para buscar métricas
     */
    private function getLatestMetricValue(string $metricType, string $metricName, string $date = null)
    {
        $query = SystemMetric::where('metric_type', $metricType)
            ->where('metric_name', $metricName);
        
        if ($date) {
            $query->where('period_date', $date);
        }
        
        $metric = $query->latest('period_date')->first();
        
        return $metric ? $metric->metric_value : 0;
    }

    private function getLatestMetricData(string $metricType, string $metricName, string $date = null)
    {
        $query = SystemMetric::where('metric_type', $metricType)
            ->where('metric_name', $metricName);
        
        if ($date) {
            $query->where('period_date', $date);
        }
        
        $metric = $query->latest('period_date')->first();
        
        return $metric ? $metric->metric_data : [];
    }

    private function getMetricTrend(string $metricType, string $metricName, string $startDate, string $endDate)
    {
        return SystemMetric::where('metric_type', $metricType)
            ->where('metric_name', $metricName)
            ->whereBetween('period_date', [$startDate, $endDate])
            ->orderBy('period_date')
            ->get()
            ->map(function ($metric) {
                return [
                    'date' => $metric->period_date->format('Y-m-d'),
                    'value' => $metric->metric_value,
                    'data' => $metric->metric_data
                ];
            });
    }
}