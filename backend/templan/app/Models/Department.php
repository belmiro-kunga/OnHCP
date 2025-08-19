<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'parent_id',
        'manager_id',
        'cost_center',
        'location',
        'email',
        'phone',
        'metadata',
        'external_id',
        'ad_group',
        'active',
        'sync_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'metadata' => 'array',
        'sync_at' => 'datetime',
    ];

    /**
     * Get the users that belong to this department.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the manager of this department.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get the parent department.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    /**
     * Get the child departments.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    /**
     * Get the job positions in this department.
     */
    public function jobPositions(): HasMany
    {
        return $this->hasMany(JobPosition::class);
    }

    /**
     * Get the organizational structures for this department.
     */
    public function organizationalStructures(): HasMany
    {
        return $this->hasMany(OrganizationalStructure::class);
    }

    /**
     * Scope a query to only include active departments.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the count of users assigned to this department.
     */
    public function getUsersCountAttribute()
    {
        return $this->users()->count();
    }

    /**
     * Get all descendants of this department.
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get all ancestors of this department.
     */
    public function ancestors()
    {
        $ancestors = collect();
        $parent = $this->parent;
        
        while ($parent) {
            $ancestors->push($parent);
            $parent = $parent->parent;
        }
        
        return $ancestors;
    }
}