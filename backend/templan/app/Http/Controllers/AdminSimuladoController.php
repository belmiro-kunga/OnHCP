<?php
namespace App\Http\Controllers;

use App\Models\Simulado;
use App\Models\SimuladoQuestion;
use Illuminate\Http\Request;

class AdminSimuladoController extends Controller
{
    public function index(Request $request)
    {
        $query = Simulado::query()->with(['category'])->withCount('assignments');
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        return response()->json($query->orderByDesc('id')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|integer|exists:simulado_categories,id',
            'duration' => 'required|integer|min:60',
            'min_score' => 'required|integer|min:0|max:100',
            'max_attempts' => 'required|integer|min:1|max:50',
            'type' => 'required|string|max:50',
            'allow_navigation' => 'boolean',
            'allow_save_progress' => 'boolean',
            'show_feedback' => 'boolean',
            'status' => 'in:active,archived',
            'questions' => 'array',
            'questions.*.statement' => 'required_with:questions|string',
            'questions.*.options' => 'required_with:questions|array|min:2',
            'questions.*.correct_answer' => 'required_with:questions|string',
            'questions.*.explanation' => 'nullable|string',
            'questions.*.q_type' => 'nullable|string|in:multiple_choice,true_false,essay,ordering,matching',
            'questions.*.weight' => 'nullable|integer|min:1',
            'questions.*.difficulty' => 'nullable|string|in:easy,medium,hard',
        ]);

        $simulado = Simulado::create(array_merge($data, [
            'allow_navigation' => $request->boolean('allow_navigation', true),
            'allow_save_progress' => $request->boolean('allow_save_progress', true),
            'show_feedback' => $request->boolean('show_feedback', true),
            'status' => $request->input('status', 'active'),
            'created_by' => optional($request->user())->id,
        ]));

        $questions = $data['questions'] ?? [];
        foreach ($questions as $i => $q) {
            SimuladoQuestion::create([
                'simulado_id' => $simulado->id,
                'statement' => $q['statement'],
                'q_type' => $q['q_type'] ?? 'multiple_choice',
                'weight' => $q['weight'] ?? 1,
                'difficulty' => $q['difficulty'] ?? 'medium',
                'options' => $q['options'],
                'correct_answer' => $q['correct_answer'],
                'explanation' => $q['explanation'] ?? null,
                'q_order' => $i + 1,
            ]);
        }

        return response()->json($simulado->load(['questions','category']), 201);
    }

    public function show(Simulado $simulado)
    {
        return response()->json($simulado->load(['questions','category']));
    }

    public function update(Request $request, Simulado $simulado)
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|integer|exists:simulado_categories,id',
            'duration' => 'sometimes|required|integer|min:60',
            'min_score' => 'sometimes|required|integer|min:0|max:100',
            'max_attempts' => 'sometimes|required|integer|min:1|max:50',
            'type' => 'sometimes|required|string|max:50',
            'allow_navigation' => 'boolean',
            'allow_save_progress' => 'boolean',
            'show_feedback' => 'boolean',
            'status' => 'in:active,archived',
            'questions' => 'array',
            'questions.*.statement' => 'required_with:questions|string',
            'questions.*.options' => 'required_with:questions|array|min:2',
            'questions.*.correct_answer' => 'required_with:questions|string',
            'questions.*.explanation' => 'nullable|string',
            'questions.*.q_type' => 'nullable|string|in:multiple_choice,true_false,essay,ordering,matching',
            'questions.*.weight' => 'nullable|integer|min:1',
            'questions.*.difficulty' => 'nullable|string|in:easy,medium,hard',
        ]);

        $simulado->update($data);

        if ($request->has('questions')) {
            // Simple strategy: replace all
            $simulado->questions()->delete();
            $questions = $request->input('questions', []);
            foreach ($questions as $i => $q) {
                SimuladoQuestion::create([
                    'simulado_id' => $simulado->id,
                    'statement' => $q['statement'],
                    'q_type' => $q['q_type'] ?? 'multiple_choice',
                    'weight' => $q['weight'] ?? 1,
                    'difficulty' => $q['difficulty'] ?? 'medium',
                    'options' => $q['options'],
                    'correct_answer' => $q['correct_answer'],
                    'explanation' => $q['explanation'] ?? null,
                    'q_order' => $i + 1,
                ]);
            }
        }

        return response()->json($simulado->load(['questions','category']));
    }

    public function destroy(Simulado $simulado)
    {
        $simulado->delete();
        return response()->json(['deleted' => true]);
    }
}
