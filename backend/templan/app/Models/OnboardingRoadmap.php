<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OnboardingRoadmap extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'department_id',
        'steps',
        'estimated_duration_days',
        'status',
        'created_by'
    ];

    protected $casts = [
        'steps' => 'array',
        'estimated_duration_days' => 'integer'
    ];

    /**
     * Get the department that owns the roadmap.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the user who created the roadmap.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user progress records for this roadmap.
     */
    public function userProgress(): HasMany
    {
        return $this->hasMany(UserOnboardingProgress::class, 'roadmap_id');
    }

    /**
     * Scope a query to only include active roadmaps.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get the total number of steps in this roadmap.
     */
    public function getTotalStepsAttribute()
    {
        return count($this->steps ?? []);
    }

    /**
     * Get the completion rate for this roadmap.
     */
    public function getCompletionRateAttribute()
    {
        $totalUsers = $this->userProgress()->count();
        if ($totalUsers === 0) {
            return 0;
        }
        
        $completedUsers = $this->userProgress()->where('status', 'completed')->count();
        return round(($completedUsers / $totalUsers) * 100, 2);
    }
}