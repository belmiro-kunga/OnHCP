<?php

namespace App\Http\Controllers;

use App\Models\SimuladoAttempt;
use App\Services\SimuladoAttemptService;
use App\Http\Requests\StartAttemptRequest;
use App\Http\Requests\UpdateAttemptRequest;
use App\Http\Requests\SubmitAttemptRequest;
use App\Http\Resources\AttemptResource;
use App\Http\Resources\ResultResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

class SimuladoAttemptController extends Controller
{
    // Minimal in-memory catalog to keep compatibility with FE expectations
    // This mirrors the closures that served as dev stubs.
    private function getCatalog(): array
    {
        return [
            [
                'id' => 1,
                'title' => 'Simulado de Segurança no Trabalho',
                'description' => 'Avalie seus conhecimentos em normas de segurança.',
                'duration' => 1800,
                'minScore' => 70,
                'maxAttempts' => 3,
                'allowNavigation' => true,
                'allowSaveProgress' => true,
                'showFeedback' => true,
                'type' => 'obrigatorio',
                'questions' => [
                    [
                        'id' => 1,
                        'type' => 'multiple_choice',
                        'question' => 'Qual é a cor do capacete para visitantes?',
                        'options' => [
                            ['id' => 'a', 'text' => 'Azul'],
                            ['id' => 'b', 'text' => 'Branco'],
                            ['id' => 'c', 'text' => 'Vermelho'],
                            ['id' => 'd', 'text' => 'Amarelo'],
                        ],
                        'correctAnswer' => 'b',
                        'explanation' => 'Em muitas normas, capacete branco é usado por visitantes/supervisores.',
                    ],
                    [
                        'id' => 2,
                        'type' => 'true_false',
                        'question' => 'É obrigatório o uso de EPI em áreas sinalizadas.',
                        'options' => [
                            ['id' => 'true', 'text' => 'Verdadeiro'],
                            ['id' => 'false', 'text' => 'Falso'],
                        ],
                        'correctAnswer' => 'true',
                        'explanation' => 'EPI é obrigatório conforme NR-06.',
                    ],
                    [
                        'id' => 3,
                        'type' => 'multiple_choice',
                        'question' => 'Qual extintor usar em incêndio elétrico?',
                        'options' => [
                            ['id' => 'a', 'text' => 'Água'],
                            ['id' => 'b', 'text' => 'Pó químico'],
                            ['id' => 'c', 'text' => 'CO2'],
                            ['id' => 'd', 'text' => 'Espuma'],
                        ],
                        'correctAnswer' => 'c',
                        'explanation' => 'CO2 é indicado para equipamentos energizados.',
                    ],
                ],
            ],
        ];
    }

    private function findSimulado(int $id): ?array
    {
        foreach ($this->getCatalog() as $s) {
            if ($s['id'] === $id) { return $s; }
        }
        return null;
    }

    private function transformAttemptToFrontend(SimuladoAttempt $attempt, ?array $simulado = null): array
    {
        return [
            'id' => (string)$attempt->id,
            'attemptId' => (string)$attempt->id,
            'simuladoId' => (int)$attempt->simulado_id,
            'createdAt' => optional($attempt->created_at)->toISOString(),
            'submittedAt' => optional($attempt->submitted_at)->toISOString(),
            'currentQuestion' => (int)$attempt->current_question,
            'answers' => $attempt->answers ?? [],
            'timeRemaining' => $attempt->time_remaining ?? ($simulado['duration'] ?? 0),
            'result' => $attempt->result ?? null,
        ];
    }

    public function listBySimulado($simulado)
    {
        $simuladoId = (int)$simulado;
        $attempts = SimuladoAttempt::where('simulado_id', $simuladoId)
            ->orderBy('created_at', 'desc')->get();
        $sim = $this->findSimulado($simuladoId);
        return response()->json(
            $attempts->map(fn($a) => (new AttemptResource($a))->additional(['simulado' => $sim])->toArray(request()))
            ->values()
        );
    }

    public function startOrResume($simulado, StartAttemptRequest $request)
    {
        $simuladoId = (int)$simulado;
        $resume = (bool)$request->input('resume', false);

        $sim = $this->findSimulado($simuladoId);
        if (!$sim) { return response()->json(['message' => 'Simulado não encontrado'], 404); }

        $userId = Auth::id();
        /** @var SimuladoAttemptService $svc */
        $svc = app(SimuladoAttemptService::class);
        $attempt = $svc->startOrResume($userId, $simuladoId, $resume, $sim);
        $status = $resume && $attempt->created_at ? 200 : 201; // preservar 201 para novas
        return response()->json((new AttemptResource($attempt))->additional(['simulado' => $sim])->toArray($request), $status);
    }

    public function show($attempt)
    {
        $a = SimuladoAttempt::find($attempt);
        if (!$a) { return response()->json(['message' => 'Tentativa não encontrada'], 404); }
        $sim = $this->findSimulado((int)$a->simulado_id);
        return response()->json((new AttemptResource($a))->additional(['simulado' => $sim])->toArray(request()));
    }

    public function updatePartial($attempt, UpdateAttemptRequest $request)
    {
        $a = SimuladoAttempt::find($attempt);
        if (!$a) { return response()->json(['message' => 'Tentativa não encontrada'], 404); }
        $payload = $request->only(['currentQuestion','answers','timeRemaining']);
        $sim = $this->findSimulado((int)$a->simulado_id);
        /** @var SimuladoAttemptService $svc */
        $svc = app(SimuladoAttemptService::class);
        $a = $svc->saveProgress($a, $payload, $sim ?? []);
        $sim = $this->findSimulado((int)$a->simulado_id);
        return response()->json((new AttemptResource($a))->additional(['simulado' => $sim])->toArray($request));
    }

    public function submit($attempt, SubmitAttemptRequest $request)
    {
        $a = SimuladoAttempt::find($attempt);
        if (!$a) { return response()->json(['message' => 'Tentativa não encontrada'], 404); }

        // Load simulado
        $simulado = $this->findSimulado((int)$a->simulado_id);
        if (!$simulado) { return response()->json(['message' => 'Simulado não encontrado'], 404); }

        /** @var SimuladoAttemptService $svc */
        $svc = app(SimuladoAttemptService::class);
        $answers = $request->input('answers') ?? [];
        $result = $svc->submit($a, is_array($answers) ? $answers : [], $simulado);
        return response()->json((new ResultResource($result))->toArray($request));
    }

    public function result($attempt)
    {
        $a = SimuladoAttempt::find($attempt);
        if (!$a || empty($a->result)) {
            return response()->json(['message' => 'Resultado não disponível'], 404);
        }
        return response()->json($a->result);
    }
}
