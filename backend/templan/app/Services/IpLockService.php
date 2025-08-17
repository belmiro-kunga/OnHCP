<?php

namespace App\Services;

use App\Models\IpLock;
use App\Models\AuditLog;
use Carbon\CarbonImmutable;
use App\Services\NotificationService;

class IpLockService
{
    public function __construct(
        private int $failWindowMin = 15,
        private int $maxFails = 5,
        private int $lockMinutes = 30,
    ) {
        $this->failWindowMin = (int) (env('SECURITY_IP_FAIL_WINDOW_MIN', $this->failWindowMin));
        $this->maxFails = (int) (env('SECURITY_IP_MAX_FAILS', $this->maxFails));
        $this->lockMinutes = (int) (env('SECURITY_IP_LOCK_MIN', $this->lockMinutes));
    }

    public function isLocked(?IpLock $lock): bool
    {
        if (!$lock) return false;
        return $lock->locked_until && $lock->locked_until->isFuture();
    }

    public function registerFailure(string $ip): IpLock
    {
        $now = CarbonImmutable::now();
        $lock = IpLock::firstOrNew(['ip' => $ip]);

        // Reset window if expired
        if (!$lock->window_started_at || $now->diffInMinutes($lock->window_started_at) >= $this->failWindowMin) {
            $lock->window_started_at = $now;
            $lock->fail_count = 0;
        }

        $lock->fail_count = ($lock->fail_count ?? 0) + 1;

        if ($lock->fail_count >= $this->maxFails) {
            $lock->locked_until = $now->addMinutes($this->lockMinutes);
            $lock->fail_count = 0;
            $lock->window_started_at = $now; // reset window after locking
            // Audit ip_locked
            AuditLog::create([
                'action' => 'ip_locked',
                'ip' => $ip,
                'user_id' => null,
                'meta' => [
                    'locked_until' => $lock->locked_until?->toIso8601String(),
                    'window_started_at' => $lock->window_started_at?->toIso8601String(),
                    'max_fails' => $this->maxFails,
                    'lock_minutes' => $this->lockMinutes,
                ],
            ]);

            // Notify admins by email
            try {
                /** @var NotificationService $notifier */
                $notifier = app(NotificationService::class);
                $subject = sprintf('[Security] IP bloqueado: %s', $ip);
                $body = sprintf(
                    "O IP %s foi bloqueado por %d minutos após exceder %d falhas dentro de %d minutos.\nBloqueado até: %s",
                    $ip,
                    $this->lockMinutes,
                    $this->maxFails,
                    $this->failWindowMin,
                    $lock->locked_until?->toIso8601String() ?? 'N/A'
                );
                $notifier->notifyAdmins($subject, $body);
            } catch (\Throwable $e) {
                // evitar quebrar fluxo por falha de email
            }
        }

        $lock->save();
        return $lock;
    }

    public function resetOnSuccess(string $ip): void
    {
        $lock = IpLock::where('ip', $ip)->first();
        if ($lock) {
            $lock->fail_count = 0;
            $lock->locked_until = null;
            $lock->window_started_at = null;
            $lock->save();
        }
    }

    public function unlock(string $ip): void
    {
        $lock = IpLock::where('ip', $ip)->first();
        if ($lock) {
            $lock->locked_until = null;
            $lock->fail_count = 0;
            $lock->save();
        }
    }
}
