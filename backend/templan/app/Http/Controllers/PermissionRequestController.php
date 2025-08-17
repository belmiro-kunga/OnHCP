<?php

namespace App\Http\Controllers;

use App\Models\PermissionRequest;
use App\Models\AuditLog;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class PermissionRequestController extends Controller
{
    public function index()
    {
        return response()->json(['data' => PermissionRequest::with(['requester:id,name,email','approver:id,name,email'])->orderByDesc('created_at')->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'target' => ['required','string','max:190'],
            'change_set' => ['required','array'],
            'reason' => ['nullable','string']
        ]);
        $pr = PermissionRequest::create([
            'requester_id' => $request->user()->id,
            'target' => $validated['target'],
            'change_set' => $validated['change_set'],
            'reason' => $validated['reason'] ?? null,
            'status' => 'pending',
        ]);
        AuditLog::create([
            'action' => 'permission_request_created',
            'user_id' => $request->user()->id,
            'ip' => $request->ip(),
            'meta' => ['id' => $pr->id, 'target' => $pr->target]
        ]);
        // notify admins
        try {
            app(NotificationService::class)->approvalRequested((string)$pr->id, $request->user()->email, $pr->target, json_encode($pr->change_set));
        } catch (\Throwable $e) {}
        return response()->json(['data' => $pr], 201);
    }

    public function approve(Request $request, PermissionRequest $permissionRequest)
    {
        $this->authorizeAction($request, $permissionRequest);
        $permissionRequest->status = 'approved';
        $permissionRequest->approver_id = $request->user()->id;
        $permissionRequest->save();
        AuditLog::create([
            'action' => 'permission_request_approved',
            'user_id' => $request->user()->id,
            'ip' => $request->ip(),
            'meta' => ['id' => $permissionRequest->id]
        ]);
        try {
            app(NotificationService::class)->approvalApproved((string)$permissionRequest->id, $request->user()->email, $permissionRequest->target);
        } catch (\Throwable $e) {}
        return response()->json(['data' => $permissionRequest]);
    }

    public function reject(Request $request, PermissionRequest $permissionRequest)
    {
        $this->authorizeAction($request, $permissionRequest);
        $validated = $request->validate(['reason' => ['nullable','string']]);
        $permissionRequest->status = 'rejected';
        $permissionRequest->approver_id = $request->user()->id;
        $permissionRequest->reason = $validated['reason'] ?? null;
        $permissionRequest->save();
        AuditLog::create([
            'action' => 'permission_request_rejected',
            'user_id' => $request->user()->id,
            'ip' => $request->ip(),
            'meta' => ['id' => $permissionRequest->id, 'reason' => $permissionRequest->reason]
        ]);
        try {
            app(NotificationService::class)->approvalRejected((string)$permissionRequest->id, $request->user()->email, $permissionRequest->target, $permissionRequest->reason);
        } catch (\Throwable $e) {}
        return response()->json(['data' => $permissionRequest]);
    }

    protected function authorizeAction(Request $request, PermissionRequest $permissionRequest): void
    {
        // Placeholder para policy; por agora, qualquer user com habilidade 'approve-permissions' poderia aprovar
        // Implementar via Gate/Policy conforme necessidade
    }
}
