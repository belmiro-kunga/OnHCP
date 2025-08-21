<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class SystemMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'metric_type',
        'metric_name',
        'metric_data',
        'metric_value',
        'period_type',
        'period_date',
        'metadata',
    ];

    protected $casts = [
        'metric_data' => 'array',
        'metadata' => 'array',
        'period_date' => 'date',
        'metric_value' => 'decimal:2',
    ];

    // Constantes para tipos de métricas
    const TYPE_SIMULADOS = 'simulados';
    const TYPE_USUARIOS = 'usuarios';
    const TYPE_SISTEMA = 'sistema';
    const TYPE_PERFORMANCE = 'performance';

    // Constantes para períodos
    const PERIOD_DAILY = 'daily';
    const PERIOD_WEEKLY = 'weekly';
    const PERIOD_MONTHLY = 'monthly';
    const PERIOD_YEARLY = 'yearly';

    /**
     * Scope para filtrar por tipo de métrica
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('metric_type', $type);
    }

    /**
     * Scope para filtrar por período
     */
    public function scopeForPeriod($query, string $periodType, Carbon $startDate = null, Carbon $endDate = null)
    {
        $query->where('period_type', $periodType);
        
        if ($startDate) {
            $query->where('period_date', '>=', $startDate->toDateString());
        }
        
        if ($endDate) {
            $query->where('period_date', '<=', $endDate->toDateString());
        }
        
        return $query;
    }

    /**
     * Scope para métricas recentes
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('period_date', '>=', now()->subDays($days)->toDateString());
    }

    /**
     * Obtém o valor formatado da métrica
     */
    public function getFormattedValueAttribute(): string
    {
        if (is_null($this->metric_value)) {
            return 'N/A';
        }
        
        return number_format($this->metric_value, 2, ',', '.');
    }

    /**
     * Verifica se a métrica é do tipo contador
     */
    public function isCounter(): bool
    {
        return in_array($this->metric_name, [
            'total_simulados',
            'total_usuarios',
            'total_tentativas',
            'simulados_concluidos',
        ]);
    }

    /**
     * Verifica se a métrica é do tipo percentual
     */
    public function isPercentage(): bool
    {
        return in_array($this->metric_name, [
            'taxa_aprovacao',
            'taxa_conclusao',
            'taxa_abandono',
            'uptime_sistema',
        ]);
    }
}
