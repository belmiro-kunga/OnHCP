<?php

namespace App\Http\Controllers;

use App\Models\Delegation;
use App\Models\AuditLog;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class DelegationController extends Controller
{
    public function index()
    {
        return response()->json(['data' => Delegation::with(['delegator:id,name,email','delegatee:id,name,email'])->orderByDesc('created_at')->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'delegator_id' => ['required','exists:users,id'],
            'delegatee_id' => ['required','exists:users,id'],
            'scope' => ['required','array'],
            'starts_at' => ['required','date'],
            'ends_at' => ['required','date','after:starts_at'],
        ]);
        $delegation = Delegation::create([
            'delegator_id' => $validated['delegator_id'],
            'delegatee_id' => $validated['delegatee_id'],
            'scope' => $validated['scope'],
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'],
            'status' => 'active'
        ]);
        AuditLog::create([
            'action' => 'delegation_created',
            'user_id' => $request->user()->id ?? null,
            'ip' => $request->ip(),
            'meta' => ['id' => $delegation->id]
        ]);
        try {
            app(NotificationService::class)->delegationCreated((string)$delegation->id, (string)optional($delegation->delegator)->email, (string)optional($delegation->delegatee)->email, json_encode($delegation->scope), $delegation->starts_at->toIso8601String(), $delegation->ends_at->toIso8601String());
        } catch (\Throwable $e) {}
        return response()->json(['data' => $delegation], 201);
    }

    public function revoke(Request $request, Delegation $delegation)
    {
        $delegation->status = 'revoked';
        $delegation->save();
        AuditLog::create([
            'action' => 'delegation_revoked',
            'user_id' => $request->user()->id ?? null,
            'ip' => $request->ip(),
            'meta' => ['id' => $delegation->id]
        ]);
        try {
            app(NotificationService::class)->delegationRevoked((string)$delegation->id, optional($request->user())->email ?? 'system', (string)optional($delegation->delegator)->email, (string)optional($delegation->delegatee)->email);
        } catch (\Throwable $e) {}
        return response()->json(['data' => $delegation]);
    }
}
