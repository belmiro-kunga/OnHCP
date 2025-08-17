<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Group;
use App\Models\PermissionRequest;
use App\Models\Delegation;
use App\Models\License;
use App\Models\LicenseAssignment;
use App\Models\AuditLog;

class ReportController extends Controller
{
    public function metrics(Request $request)
    {
        $users = User::count();
        $groups = Group::count();
        $approvals_pending = PermissionRequest::where('status', 'pending')->count();
        $delegations_active = Delegation::where('status', 'active')->count();
        $licenses_total = License::count();
        $licenses_assigned = LicenseAssignment::count();

        // Placeholder: implementar origem real de sessões ativas
        $active_logins = null;

        return response()->json([
            'data' => compact(
                'users',
                'groups',
                'approvals_pending',
                'delegations_active',
                'licenses_total',
                'licenses_assigned',
                'active_logins'
            )
        ]);
    }

    public function access(Request $request)
    {
        // Filtros simples via query: user_id, action, days
        $query = AuditLog::query()->with(['user:id,name,email']);
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }
        if ($request->filled('action')) {
            $query->where('action', $request->string('action'));
        }
        if ($request->filled('days')) {
            $query->where('created_at', '>=', now()->subDays((int)$request->input('days')));
        }
        $items = $query->orderByDesc('created_at')->limit(200)->get();
        return response()->json(['data' => $items]);
    }

    public function permissions(Request $request)
    {
        // Relatório simples baseado em pedidos de permissão
        $items = PermissionRequest::with(['requester:id,name,email','approver:id,name,email'])
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();
        return response()->json(['data' => $items]);
    }
}
