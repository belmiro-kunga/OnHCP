<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'job_title',
        'ad_group',
        'role_id',
        'priority',
        'active',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
