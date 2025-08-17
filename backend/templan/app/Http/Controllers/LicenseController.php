<?php

namespace App\Http\Controllers;

use App\Models\License;
use App\Models\LicenseAssignment;
use App\Models\AuditLog;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LicenseController extends Controller
{
    public function index()
    {
        return response()->json(['data' => License::withCount('assignments')->orderBy('product')->get()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product' => ['required','string','max:190'],
            'seats_total' => ['required','integer','min:0'],
            'meta' => ['nullable','array']
        ]);
        $license = License::create([
            'product' => $validated['product'],
            'seats_total' => $validated['seats_total'],
            'seats_used' => 0,
            'meta' => $validated['meta'] ?? null,
        ]);
        AuditLog::create([
            'action' => 'license_created',
            'user_id' => optional($request->user())->id,
            'ip' => $request->ip(),
            'meta' => ['id' => $license->id, 'product' => $license->product]
        ]);
        return response()->json(['data' => $license], 201);
    }

    public function destroy(License $license)
    {
        $license->delete();
        return response()->json(['message' => 'deleted']);
    }

    public function assign(Request $request, License $license)
    {
        $validated = $request->validate([
            'user_id' => ['required','exists:users,id']
        ]);
        if ($license->seats_used >= $license->seats_total) {
            // notify limit
            try { app(NotificationService::class)->licenseLimitReached($license->product, (int)$license->seats_total); } catch (\Throwable $e) {}
            return response()->json(['message' => 'license limit reached'], 422);
        }
        $existing = LicenseAssignment::where('license_id',$license->id)->where('user_id',$validated['user_id'])->first();
        if ($existing) {
            return response()->json(['data' => $existing]);
        }
        $assignment = LicenseAssignment::create([
            'license_id' => $license->id,
            'user_id' => $validated['user_id'],
            'assigned_at' => Carbon::now(),
        ]);
        $license->seats_used = LicenseAssignment::where('license_id',$license->id)->count();
        $license->save();
        AuditLog::create([
            'action' => 'license_assigned',
            'user_id' => optional($request->user())->id,
            'ip' => $request->ip(),
            'meta' => ['license_id' => $license->id, 'user_id' => $validated['user_id']]
        ]);
        try {
            $userEmail = optional($assignment->user)->email ?? (string)$validated['user_id'];
            app(NotificationService::class)->licenseAssigned((string)$license->id, $license->product, $userEmail);
        } catch (\Throwable $e) {}
        return response()->json(['data' => $assignment], 201);
    }

    public function unassign(Request $request, License $license)
    {
        $validated = $request->validate([
            'user_id' => ['required','exists:users,id']
        ]);
        $assignment = LicenseAssignment::where('license_id',$license->id)->where('user_id',$validated['user_id'])->first();
        if (!$assignment) {
            return response()->json(['message' => 'not assigned'], 404);
        }
        $assignment->delete();
        $license->seats_used = LicenseAssignment::where('license_id',$license->id)->count();
        $license->save();
        AuditLog::create([
            'action' => 'license_unassigned',
            'user_id' => optional($request->user())->id,
            'ip' => $request->ip(),
            'meta' => ['license_id' => $license->id, 'user_id' => $validated['user_id']]
        ]);
        try {
            $userEmail = (string) $validated['user_id'];
            app(NotificationService::class)->licenseUnassigned((string)$license->id, $license->product, $userEmail);
        } catch (\Throwable $e) {}
        return response()->json(['message' => 'unassigned']);
    }

    public function assignments(License $license)
    {
        $items = LicenseAssignment::where('license_id',$license->id)->with('user:id,name,email')->get();
        return response()->json(['data' => $items]);
    }
}
