<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class ExternalIntegration extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'provider',
        'description',
        'configuration',
        'credentials',
        'status',
        'sync_frequency',
        'last_sync_at',
        'next_sync_at',
        'sync_settings',
        'field_mappings',
        'filters',
        'auto_sync',
        'bidirectional',
        'timeout',
        'retry_attempts',
        'error_handling',
        'notes',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'auto_sync' => 'boolean',
        'bidirectional' => 'boolean',
        'configuration' => 'array',
        'credentials' => 'encrypted:array',
        'sync_settings' => 'array',
        'field_mappings' => 'array',
        'filters' => 'array',
        'error_handling' => 'array',
        'last_sync_at' => 'datetime',
        'next_sync_at' => 'datetime',
    ];

    protected $hidden = [
        'credentials',
    ];

    /**
     * Get the sync logs for this integration.
     */
    public function syncLogs(): HasMany
    {
        return $this->hasMany(SyncLog::class);
    }

    /**
     * Get the latest sync logs.
     */
    public function latestSyncLogs($limit = 10)
    {
        return $this->syncLogs()->latest()->limit($limit);
    }

    /**
     * Scope a query to only include active integrations.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope a query to filter by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to include integrations that need sync.
     */
    public function scopeNeedsSync($query)
    {
        return $query->where('auto_sync', true)
                    ->where('active', true)
                    ->where('status', 'active')
                    ->where(function ($q) {
                        $q->whereNull('next_sync_at')
                          ->orWhere('next_sync_at', '<=', now());
                    });
    }

    /**
     * Check if the integration is ready for sync.
     */
    public function isReadyForSync()
    {
        return $this->active && 
               $this->status === 'active' && 
               $this->auto_sync &&
               (is_null($this->next_sync_at) || $this->next_sync_at <= now());
    }

    /**
     * Update the next sync time based on frequency.
     */
    public function updateNextSyncTime()
    {
        if (!$this->sync_frequency) {
            return;
        }

        $nextSync = match($this->sync_frequency) {
            'hourly' => now()->addHour(),
            'daily' => now()->addDay(),
            'weekly' => now()->addWeek(),
            'monthly' => now()->addMonth(),
            default => null,
        };

        if ($nextSync) {
            $this->update(['next_sync_at' => $nextSync]);
        }
    }

    /**
     * Get the last successful sync log.
     */
    public function getLastSuccessfulSync()
    {
        return $this->syncLogs()
                   ->where('status', 'completed')
                   ->latest('completed_at')
                   ->first();
    }

    /**
     * Get the last failed sync log.
     */
    public function getLastFailedSync()
    {
        return $this->syncLogs()
                   ->where('status', 'failed')
                   ->latest('started_at')
                   ->first();
    }
}
