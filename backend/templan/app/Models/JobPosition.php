<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'code',
        'description',
        'department_id',
        'level',
        'category',
        'min_salary',
        'max_salary',
        'requirements',
        'responsibilities',
        'benefits',
        'external_id',
        'hr_code',
        'active',
        'sync_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'min_salary' => 'decimal:2',
        'max_salary' => 'decimal:2',
        'requirements' => 'array',
        'responsibilities' => 'array',
        'benefits' => 'array',
        'sync_at' => 'datetime',
    ];

    /**
     * Get the department that owns this job position.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the organizational structures for this job position.
     */
    public function organizationalStructures(): HasMany
    {
        return $this->hasMany(OrganizationalStructure::class);
    }

    /**
     * Scope a query to only include active job positions.
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
     * Scope a query to filter by level.
     */
    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Get the salary range as a formatted string.
     */
    public function getSalaryRangeAttribute()
    {
        if ($this->min_salary && $this->max_salary) {
            return number_format($this->min_salary, 2) . ' - ' . number_format($this->max_salary, 2);
        }
        
        return null;
    }
}
