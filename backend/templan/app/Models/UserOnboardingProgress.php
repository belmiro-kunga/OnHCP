<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserOnboardingProgress extends Model
{
    use HasFactory;

    protected $table = 'user_onboarding_progress';

    protected $fillable = [
        'user_id',
        'roadmap_id',
        'completed_steps',
        'progress_percentage',
        'status',
        'started_at',
        'completed_at',
        'notes',
        'assigned_by'
    ];

    protected $casts = [
        'completed_steps' => 'array',
        'progress_percentage' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    /**
     * Get the user that owns the progress.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the roadmap that owns the progress.
     */
    public function roadmap(): BelongsTo
    {
        return $this->belongsTo(OnboardingRoadmap::class);
    }

    /**
     * Get the user who assigned this roadmap.
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Scope a query to only include in progress records.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope a query to only include completed records.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Check if the onboarding is completed.
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the onboarding is in progress.
     */
    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    /**
     * Start the onboarding process.
     */
    public function start()
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now()
        ]);
    }

    /**
     * Complete a step.
     */
    public function completeStep($stepId)
    {
        $completedSteps = $this->completed_steps ?? [];
        
        if (!in_array($stepId, $completedSteps)) {
            $completedSteps[] = $stepId;
            $this->completed_steps = $completedSteps;
            
            // Calculate progress percentage
            $totalSteps = count($this->roadmap->steps ?? []);
            $this->progress_percentage = $totalSteps > 0 ? 
                round((count($completedSteps) / $totalSteps) * 100) : 0;
            
            // Check if all steps are completed
            if ($this->progress_percentage >= 100) {
                $this->status = 'completed';
                $this->completed_at = now();
            }
            
            $this->save();
        }
    }

    /**
     * Uncomplete a step.
     */
    public function uncompleteStep($stepId)
    {
        $completedSteps = $this->completed_steps ?? [];
        
        if (($key = array_search($stepId, $completedSteps)) !== false) {
            unset($completedSteps[$key]);
            $this->completed_steps = array_values($completedSteps);
            
            // Recalculate progress percentage
            $totalSteps = count($this->roadmap->steps ?? []);
            $this->progress_percentage = $totalSteps > 0 ? 
                round((count($completedSteps) / $totalSteps) * 100) : 0;
            
            // Update status if no longer completed
            if ($this->status === 'completed') {
                $this->status = 'in_progress';
                $this->completed_at = null;
            }
            
            $this->save();
        }
    }

    /**
     * Get the remaining steps.
     */
    public function getRemainingStepsAttribute()
    {
        $allSteps = $this->roadmap->steps ?? [];
        $completedSteps = $this->completed_steps ?? [];
        
        return array_filter($allSteps, function($step) use ($completedSteps) {
            return !in_array($step['id'] ?? null, $completedSteps);
        });
    }
}