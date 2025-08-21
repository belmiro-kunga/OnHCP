<?php
namespace App\Http\Controllers;

use App\Contracts\SimuladoRepositoryInterface;
use App\Contracts\SimuladoQuestionServiceInterface;
use App\Contracts\CacheServiceInterface;
use App\Models\Simulado;
use Illuminate\Http\Request;
use App\Http\Requests\CreateSimuladoRequest;

class AdminSimuladoController extends Controller
{
    public function __construct(
        private SimuladoRepositoryInterface $simuladoRepository,
        private SimuladoQuestionServiceInterface $questionService,
        private CacheServiceInterface $cacheService
    ) {}

    public function index(Request $request)
    {
        $status = $request->query('status');
        $simulados = $this->simuladoRepository->findAll($status);
        
        return response()->json($simulados);
    }

    public function store(CreateSimuladoRequest $request)
    {
        $data = $request->validated();

        $simuladoData = array_merge($data, [
            'allow_navigation' => $request->boolean('allow_navigation', true),
            'allow_save_progress' => $request->boolean('allow_save_progress', true),
            'show_feedback' => $request->boolean('show_feedback', true),
            'status' => $request->input('status', 'active'),
            'created_by' => optional($request->user())->id,
        ]);
        
        // Remove questions from simulado data
        $questionsData = $simuladoData['questions'] ?? [];
        unset($simuladoData['questions']);
        
        $simulado = $this->simuladoRepository->create($simuladoData);
        
        // Create questions if provided
        if (!empty($questionsData)) {
            $this->questionService->createQuestions($simulado, $questionsData);
        }
        
        $this->cacheService->clearSimuladoCache();

        return response()->json($simulado->load(['questions','category']), 201);
    }
    
    public function show(Simulado $simulado)
    {
        $simulado = $this->simuladoRepository->findById($simulado->id);
        return response()->json($simulado);
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

        // Separate questions data from simulado data
        $questionsData = $data['questions'] ?? null;
        unset($data['questions']);
        
        $simulado = $this->simuladoRepository->update($simulado, $data);

        if ($questionsData !== null) {
            $this->questionService->updateQuestions($simulado, $questionsData);
        }
        
        $this->cacheService->clearSimuladoCache();

        return response()->json($simulado->load(['questions','category']));
    }

    public function destroy(Simulado $simulado)
    {
        $this->simuladoRepository->delete($simulado);
        $this->cacheService->clearSimuladoCache();
        
        return response()->json(['deleted' => true]);
    }
}
