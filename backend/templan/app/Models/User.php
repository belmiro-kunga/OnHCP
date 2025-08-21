<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'status',
        'mfa_enabled',
        'role_id',
        'department_id',
        'ad_groups',
        'notification_preferences',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'ad_groups' => 'array',
            'notification_preferences' => 'array',
        ];
    }

    /**
     * Get the role that owns the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the department that owns the user.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the departments managed by this user.
     */
    public function managedDepartments()
    {
        return $this->hasMany(Department::class, 'manager_id');
    }

    /**
     * Get the user's notification preferences with defaults
     */
    public function getNotificationPreferencesAttribute($value)
    {
        $defaults = [
            'email_enabled' => true,
            'simulado_assigned' => true,
            'simulado_result' => true,
            'simulado_deadline' => true,
            'simulado_completed' => false,
            'email_frequency' => 'immediate', // immediate, daily, weekly
            'quiet_hours' => [
                'enabled' => false,
                'start' => '22:00',
                'end' => '08:00'
            ]
        ];

        $preferences = $value ? json_decode($value, true) : [];
        return array_merge($defaults, $preferences ?: []);
    }

    /**
     * Set notification preferences
     */
    public function setNotificationPreferencesAttribute($value)
    {
        $this->attributes['notification_preferences'] = is_array($value) ? json_encode($value) : $value;
    }

    /**
     * Check if user wants to receive a specific type of notification
     */
    public function wantsNotification(string $type): bool
    {
        $preferences = $this->notification_preferences;
        return $preferences[$type] ?? true;
    }

    /**
     * Check if user wants email notifications
     */
    public function wantsEmailNotifications(): bool
    {
        $preferences = $this->notification_preferences;
        return $preferences['email_enabled'] ?? true;
    }

    /**
     * Check if current time is within quiet hours
     */
    public function isInQuietHours(): bool
    {
        $preferences = $this->notification_preferences;
        $quietHours = $preferences['quiet_hours'] ?? [];
        
        if (!($quietHours['enabled'] ?? false)) {
            return false;
        }
        
        $now = now()->format('H:i');
        $start = $quietHours['start'] ?? '22:00';
        $end = $quietHours['end'] ?? '08:00';
        
        if ($start <= $end) {
            return $now >= $start && $now <= $end;
        } else {
            return $now >= $start || $now <= $end;
        }
    }
}
