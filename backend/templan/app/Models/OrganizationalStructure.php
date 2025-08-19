<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationalStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'job_position_id',
        'manager_id',
        'substitute_id',
        'employment_type',
        'start_date',
        'end_date',
        'salary',
        'cost_center',
        'reporting_structure',
        'permissions',
        'external_employee_id',
        'active',
        'sync_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'salary' => 'decimal:2',
        'reporting_structure' => 'array',
        'permissions' => 'array',
        'sync_at' => 'datetime',
    ];

    /**
     * Get the user that owns this organizational structure.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the department for this organizational structure.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the job position for this organizational structure.
     */
    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class);
    }

    /**
     * Get the manager for this organizational structure.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get the substitute for this organizational structure.
     */
    public function substitute(): BelongsTo
    {
        return $this->belongsTo(User::class, 'substitute_id');
    }

    /**
     * Scope a query to only include active organizational structures.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope a query to filter by department.
     */
    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope a query to filter by employment type.
     */
    public function scopeByEmploymentType($query, $type)
    {
        return $query->where('employment_type', $type);
    }

    /**
     * Scope a query to include current employees (no end date or future end date).
     */
    public function scopeCurrent($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('end_date')
              ->orWhere('end_date', '>', now());
        });
    }

    /**
     * Check if the organizational structure is currently active.
     */
    public function isCurrentlyActive()
    {
        return $this->active && 
               $this->start_date <= now() && 
               (is_null($this->end_date) || $this->end_date > now());
    }
}
