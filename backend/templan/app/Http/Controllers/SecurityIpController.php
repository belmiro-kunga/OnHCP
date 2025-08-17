<?php

namespace App\Http\Controllers;

use App\Models\IpLock;
use App\Models\IpPolicy;
use App\Models\AuditLog;
use App\Services\IpLockService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class SecurityIpController extends Controller
{
    public function indexPolicies()
    {
        return response()->json([
            'data' => IpPolicy::orderBy('type')->orderBy('ip_cidr')->get(),
        ]);
    }

    public function storePolicy(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:whitelist,blacklist',
            'ip_cidr' => 'required|string',
            'reason' => 'nullable|string',
        ]);

        $policy = IpPolicy::create([
            'type' => $validated['type'],
            'ip_cidr' => $validated['ip_cidr'],
            'reason' => $validated['reason'] ?? null,
            'created_by' => optional($request->user())->id,
        ]);

        // Audit: ip_whitelisted or ip_blacklisted
        $action = $validated['type'] === 'whitelist' ? 'ip_whitelisted' : 'ip_blacklisted';
        AuditLog::create([
            'action' => $action,
            'ip' => $request->ip(),
            'user_id' => optional($request->user())->id,
            'meta' => [
                'ip_cidr' => $validated['ip_cidr'],
                'reason' => $validated['reason'] ?? null,
            ],
        ]);

        // Notify admins by email
        try {
            /** @var NotificationService $notifier */
            $notifier = app(NotificationService::class);
            $subject = sprintf('[Security] %s adicionado: %s', $validated['type'] === 'whitelist' ? 'Whitelist' : 'Blacklist', $validated['ip_cidr']);
            $by = optional($request->user())->email ?? ('user#'.optional($request->user())->id);
            $reason = $validated['reason'] ?? '-';
            $notifier->notifyAdmins($subject, "Política criada por: $by\nTipo: {$validated['type']}\nIP/CIDR: {$validated['ip_cidr']}\nMotivo: $reason");
        } catch (\Throwable $e) {}

        return response()->json(['data' => $policy], 201);
    }

    public function destroyPolicy(IpPolicy $policy)
    {
        // Audit before delete to capture context
        AuditLog::create([
            'action' => $policy->type === 'whitelist' ? 'ip_unwhitelisted' : 'ip_unblacklisted',
            'ip' => request()->ip(),
            'user_id' => optional(request()->user())->id,
            'meta' => [
                'ip_cidr' => $policy->ip_cidr,
                'policy_id' => $policy->id,
            ],
        ]);
        // Notify admins by email
        try {
            /** @var NotificationService $notifier */
            $notifier = app(NotificationService::class);
            $subject = sprintf('[Security] %s removido: %s', $policy->type === 'whitelist' ? 'Whitelist' : 'Blacklist', $policy->ip_cidr);
            $by = optional(request()->user())->email ?? ('user#'.optional(request()->user())->id);
            $notifier->notifyAdmins($subject, "Política removida por: $by\nTipo: {$policy->type}\nIP/CIDR: {$policy->ip_cidr}\nID: {$policy->id}");
        } catch (\Throwable $e) {}
        $policy->delete();
        return response()->json(['message' => 'deleted']);
    }

    public function indexLocks()
    {
        return response()->json([
            'data' => IpLock::orderByDesc('locked_until')->get(),
        ]);
    }

    public function unlock(Request $request, IpLockService $locks)
    {
        $validated = $request->validate([
            'ip' => 'required|ip',
        ]);
        $locks->unlock($validated['ip']);
        // Audit ip_unlocked
        AuditLog::create([
            'action' => 'ip_unlocked',
            'ip' => $request->ip(),
            'user_id' => optional($request->user())->id,
            'meta' => [
                'target_ip' => $validated['ip'],
            ],
        ]);
        // Notify admins by email
        try {
            /** @var NotificationService $notifier */
            $notifier = app(NotificationService::class);
            $subject = sprintf('[Security] IP desbloqueado: %s', $validated['ip']);
            $by = optional($request->user())->email ?? ('user#'.optional($request->user())->id);
            $notifier->notifyAdmins($subject, "IP desbloqueado por: $by\nIP: {$validated['ip']}");
        } catch (\Throwable $e) {}
        return response()->json(['message' => 'unlocked']);
    }
}
