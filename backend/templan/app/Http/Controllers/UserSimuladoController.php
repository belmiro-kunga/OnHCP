<?php
namespace App\Http\Controllers;

use App\Models\Simulado;
use App\Models\SimuladoAssignment;
use App\Models\SimuladoAttempt;
use Illuminate\Http\Request;

class UserSimuladoController extends Controller
{
    public function mySimulados(Request $request)
    {
        // In a real app, determine the current user and their courses/classes
        $userId = optional($request->user())->id ?? 1; // dev fallback

        // Collect assignments targeted to this user directly
        $assignments = SimuladoAssignment::query()
            ->where(function($q) use ($userId) {
                $q->where('target_type', 'user')->where('target_id', $userId);
            })
            ->get();

        // NOTE: For course/class, you would resolve memberships to user IDs and include them

        $simuladoIds = $assignments->pluck('simulado_id')->unique()->values();
        $simulados = Simulado::whereIn('id', $simuladoIds)->get()->keyBy('id');

        // Attempts per simulado for this user
        $attempts = SimuladoAttempt::whereIn('simulado_id', $simuladoIds)
            ->where('user_id', $userId)
            ->get()
            ->groupBy('simulado_id');

        $out = [];
        foreach ($assignments as $as) {
            $s = $simulados[$as->simulado_id] ?? null;
            if (!$s) continue;
            $list = $attempts->get($s->id, collect());
            $used = $list->count();
            $last = $list->sortByDesc('id')->first();

            $effectiveMin = $as->min_score_override ?? $s->min_score;
            $effectiveMax = $as->max_attempts_override ?? $s->max_attempts;

            $status = 'not_started';
            if ($last) {
                $status = $last->submitted_at ? 'completed' : 'in_progress';
            }

            $out[] = [
                'id' => $s->id,
                'title' => $s->title,
                'description' => $s->description,
                'effectiveMinScore' => (int)$effectiveMin,
                'effectiveMaxAttempts' => (int)$effectiveMax,
                'usedAttempts' => (int)$used,
                'dueAt' => optional($as->due_at)->toIso8601String(),
                'status' => $status,
                'lastAttemptId' => $last?->id,
            ];
        }

        return response()->json(array_values($out));
    }
}
