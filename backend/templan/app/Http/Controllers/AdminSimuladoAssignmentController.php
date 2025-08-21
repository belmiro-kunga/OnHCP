<?php
namespace App\Http\Controllers;

use App\Models\Simulado;
use App\Models\SimuladoAssignment;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class AdminSimuladoAssignmentController extends Controller
{
    public function index(Simulado $simulado)
    {
        return response()->json($simulado->assignments()->orderByDesc('id')->get());
    }

    public function store(Request $request, Simulado $simulado)
    {
        $data = $request->validate([
            'target_type' => 'required|in:course,class,user',
            'target_id' => 'required|integer',
            'due_at' => 'nullable|date',
            'max_attempts_override' => 'nullable|integer|min:1|max:50',
            'min_score_override' => 'nullable|integer|min:0|max:100',
        ]);

        $assignment = SimuladoAssignment::create(array_merge($data, [
            'simulado_id' => $simulado->id,
            'assigned_by' => optional($request->user())->id,
        ]));

        // Enviar notificação quando simulado é atribuído a usuário específico
        if ($data['target_type'] === 'user') {
            $user = User::find($data['target_id']);
            if ($user) {
                $notificationService = app(NotificationService::class);
                $dueDate = $data['due_at'] ? new \DateTime($data['due_at']) : null;
                $notificationService->simuladoAssigned($user, $simulado, $dueDate);
            }
        }
        // TODO: Para course/class, implementar lógica para obter usuários e notificar cada um

        return response()->json($assignment, 201);
    }

    public function destroy($assignmentId)
    {
        $a = SimuladoAssignment::findOrFail($assignmentId);
        $a->delete();
        return response()->json(['deleted' => true]);
    }
}
