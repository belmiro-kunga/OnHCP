<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Notification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
        'email_sent',
        'email_sent_at',
        'priority',
        'scheduled_for',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'email_sent' => 'boolean',
        'email_sent_at' => 'datetime',
        'scheduled_for' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Notification types constants
     */
    const TYPE_SIMULADO_ASSIGNED = 'simulado_assigned';
    const TYPE_SIMULADO_REMINDER = 'simulado_reminder';
    const TYPE_SIMULADO_RESULT = 'simulado_result';
    const TYPE_SIMULADO_DEADLINE = 'simulado_deadline';
    const TYPE_SIMULADO_COMPLETED = 'simulado_completed';
    const TYPE_SIMULADO_FAILED = 'simulado_failed';
    const TYPE_SIMULADO_PASSED = 'simulado_passed';

    /**
     * Priority levels constants
     */
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    /**
     * Get the user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope to get read notifications.
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope to get notifications by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get notifications by priority.
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope to get scheduled notifications.
     */
    public function scopeScheduled($query)
    {
        return $query->whereNotNull('scheduled_for');
    }

    /**
     * Scope to get notifications ready to be sent.
     */
    public function scopeReadyToSend($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('scheduled_for')
              ->orWhere('scheduled_for', '<=', now());
        });
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(): bool
    {
        if ($this->read_at) {
            return true;
        }

        return $this->update(['read_at' => now()]);
    }

    /**
     * Mark notification as unread.
     */
    public function markAsUnread(): bool
    {
        return $this->update(['read_at' => null]);
    }

    /**
     * Check if notification is read.
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    /**
     * Check if notification is unread.
     */
    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    /**
     * Mark email as sent.
     */
    public function markEmailAsSent(): bool
    {
        return $this->update([
            'email_sent' => true,
            'email_sent_at' => now()
        ]);
    }

    /**
     * Check if email was sent.
     */
    public function emailWasSent(): bool
    {
        return $this->email_sent;
    }

    /**
     * Get notification age in human readable format.
     */
    public function getAgeAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get priority label.
     */
    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'Baixa',
            self::PRIORITY_NORMAL => 'Normal',
            self::PRIORITY_HIGH => 'Alta',
            self::PRIORITY_URGENT => 'Urgente',
            default => 'Normal'
        };
    }

    /**
     * Get type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            self::TYPE_SIMULADO_ASSIGNED => 'Simulado Atribuído',
            self::TYPE_SIMULADO_REMINDER => 'Lembrete de Simulado',
            self::TYPE_SIMULADO_RESULT => 'Resultado de Simulado',
            self::TYPE_SIMULADO_DEADLINE => 'Prazo de Simulado',
            self::TYPE_SIMULADO_COMPLETED => 'Simulado Concluído',
            self::TYPE_SIMULADO_FAILED => 'Simulado Reprovado',
            self::TYPE_SIMULADO_PASSED => 'Simulado Aprovado',
            default => 'Notificação'
        };
    }
}