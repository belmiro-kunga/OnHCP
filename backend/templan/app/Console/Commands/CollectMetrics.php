<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ReportsService;
use Carbon\Carbon;

class CollectMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metrics:collect {--date= : Data específica para coleta (Y-m-d)} {--type= : Tipo específico de métrica (usuarios|simulados|performance|sistema)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Coleta métricas do sistema para relatórios e dashboard';

    /**
     * @var ReportsService
     */
    protected $reportsService;

    /**
     * Create a new command instance.
     */
    public function __construct(ReportsService $reportsService)
    {
        parent::__construct();
        $this->reportsService = $reportsService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : now();
        $type = $this->option('type');

        $this->info("Iniciando coleta de métricas para {$date->format('Y-m-d')}...");

        try {
            if ($type) {
                $this->collectSpecificMetrics($type, $date);
            } else {
                $this->collectAllMetrics($date);
            }

            $this->info('Coleta de métricas concluída com sucesso!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Erro durante a coleta de métricas: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Coleta todas as métricas
     */
    private function collectAllMetrics(Carbon $date)
    {
        $this->info('Coletando métricas de usuários...');
        $this->reportsService->collectUserMetrics($date);

        $this->info('Coletando métricas de simulados...');
        $this->reportsService->collectSimuladoMetrics($date);

        $this->info('Coletando métricas de performance...');
        $this->reportsService->collectPerformanceMetrics($date);

        $this->info('Coletando métricas do sistema...');
        $this->reportsService->collectSystemMetrics($date);
    }

    /**
     * Coleta métricas específicas por tipo
     */
    private function collectSpecificMetrics(string $type, Carbon $date)
    {
        switch ($type) {
            case 'usuarios':
                $this->info('Coletando métricas de usuários...');
                $this->reportsService->collectUserMetrics($date);
                break;
            case 'simulados':
                $this->info('Coletando métricas de simulados...');
                $this->reportsService->collectSimuladoMetrics($date);
                break;
            case 'performance':
                $this->info('Coletando métricas de performance...');
                $this->reportsService->collectPerformanceMetrics($date);
                break;
            case 'sistema':
                $this->info('Coletando métricas do sistema...');
                $this->reportsService->collectSystemMetrics($date);
                break;
            default:
                $this->error("Tipo de métrica inválido: {$type}");
                $this->info('Tipos válidos: usuarios, simulados, performance, sistema');
                break;
        }
    }
}
