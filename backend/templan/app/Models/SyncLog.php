<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class SyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_integration_id',
        'sync_type',
        'direction',
        'status',
        'started_at',
        'completed_at',
        'duration',
        'records_processed',
        'records_created',
        'records_updated',
        'records_deleted',
        'records_failed',
        'summary',
        'errors',
        'warnings',
        'error_message',
        'metadata',
        'triggered_by',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'summary' => 'array',
        'errors' => 'array',
        'warnings' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the external integration that owns this sync log.
     */
    public function externalIntegration(): BelongsTo
    {
        return $this->belongsTo(ExternalIntegration::class);
    }

    /**
     * Get the user that triggered this sync.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by sync type.
     */
    public function scopeBySyncType($query, $type)
    {
        return $query->where('sync_type', $type);
    }

    /**
     * Scope a query to filter by integration.
     */
    public function scopeByIntegration($query, $integrationId)
    {
        return $query->where('external_integration_id', $integrationId);
    }

    /**
     * Scope a query to include only successful syncs.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to include only failed syncs.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to include syncs from a specific date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('started_at', [$startDate, $endDate]);
    }

    /**
     * Get the duration in a human readable format.
     */
    public function getFormattedDurationAttribute()
    {
        if (!$this->duration) {
            return null;
        }

        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;

        if ($minutes > 0) {
            return "{$minutes}m {$seconds}s";
        }

        return "{$seconds}s";
    }

    /**
     * Check if the sync was successful.
     */
    public function isSuccessful()
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the sync failed.
     */
    public function isFailed()
    {
        return $this->status === 'failed';
    }

    /**
     * Check if the sync is still running.
     */
    public function isRunning()
    {
        return in_array($this->status, ['started', 'running']);
    }

    /**
     * Get the total records affected.
     */
    public function getTotalRecordsAffectedAttribute()
    {
        return $this->records_created + $this->records_updated + $this->records_deleted;
    }
}
