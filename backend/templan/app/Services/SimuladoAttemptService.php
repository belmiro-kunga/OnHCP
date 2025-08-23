<?php
namespace App\Services;

use App\Models\SimuladoAttempt;
use App\Models\User;
use App\Models\Simulado;
use App\Contracts\NotificationServiceInterface;
use App\Services\Scoring\ScoringStrategyFactory;
use App\Contracts\ScoringStrategyInterface;
use Illuminate\Support\Facades\DB;

class SimuladoAttemptService
{
    /**
     * @var ScoringStrategyInterface
     */
    private ScoringStrategyInterface $scoringStrategy;

    public function __construct()
    {
        // Default to standard scoring strategy
        $this->scoringStrategy = ScoringStrategyFactory::create('standard');
    }

    /**
     * Set the scoring strategy
     *
     * @param string $strategyName
     * @return void
     */
    public function setScoringStrategy(string $strategyName): void
    {
        $this->scoringStrategy = ScoringStrategyFactory::create($strategyName);
    }
    /**
     * Inicia ou retoma uma tentativa para um simulado e usuário.
     */
    public function startOrResume(int $userId = null, int $simuladoId, bool $resume, array $sim): SimuladoAttempt
    {
        if ($resume) {
            $existing = SimuladoAttempt::where('simulado_id', $simuladoId)
                ->when($userId, fn($q) => $q->where('user_id', $userId))
                ->whereNull('submitted_at')
                ->orderBy('created_at', 'desc')
                ->first();
            if ($existing) {
                return $existing;
            }
        }

        $attempt = new SimuladoAttempt();
        $attempt->simulado_id = $simuladoId;
        $attempt->user_id = $userId;
        $attempt->current_question = 0;
        $attempt->answers = [];
        $attempt->time_remaining = (int)($sim['duration'] ?? 0);
        $attempt->save();

        return $attempt;
    }

    /**
     * Atualiza progresso parcial (currentQuestion, answers, timeRemaining)
     */
    public function saveProgress(SimuladoAttempt $a, array $payload, array $sim): SimuladoAttempt
    {
        if (array_key_exists('currentQuestion', $payload)) {
            $a->current_question = (int)$payload['currentQuestion'];
        }
        if (array_key_exists('answers', $payload) && is_array($payload['answers'])) {
            $a->answers = $payload['answers'];
        }
        if (array_key_exists('timeRemaining', $payload)) {
            $a->time_remaining = (int)$payload['timeRemaining'];
        }
        $a->save();
        return $a;
    }

    /**
     * Submete tentativa e retorna o "result" no formato esperado pelo FE.
     */
    public function submit(SimuladoAttempt $a, array $answers, array $sim): array
    {
        // Merge provided answers if any
        if (!empty($answers)) {
            $a->answers = array_merge($a->answers ?? [], $answers);
        }

        $questions = $sim['questions'] ?? [];
        $total = count($questions);

        // Preparar questões para a estratégia de pontuação
        $questionsForScoring = [];
        foreach ($questions as $q) {
            $questionsForScoring[] = [
                'id' => $q['id'],
                'correct_answer' => $q['correctAnswer'] ?? null,
                'weight' => $q['weight'] ?? 1, // Para estratégias ponderadas
            ];
        }

        // Calcular score usando estratégia de pontuação
        $scoreDetails = $this->scoringStrategy->calculateScore($a->answers ?? [], $questionsForScoring);
        $score = $scoreDetails['score'];
        $passed = $this->scoringStrategy->hasPassed($scoreDetails, (float)($sim['minScore'] ?? 0));

        // Montar details (mantendo formato atual)
        $details = [];
        foreach ($questions as $q) {
            $userAnswer = $a->answers[$q['id']] ?? null;
            $isCorrect = $userAnswer !== null && $userAnswer === ($q['correctAnswer'] ?? null);
            $details[] = [
                'questionId' => $q['id'],
                'question' => $q['question'] ?? '',
                'userAnswer' => $userAnswer,
                'correctAnswer' => $q['correctAnswer'] ?? null,
                'isCorrect' => $isCorrect,
                'explanation' => !empty($sim['showFeedback']) ? ($q['explanation'] ?? null) : null,
                'options' => $q['options'] ?? [],
            ];
        }

        $duration = ($sim['duration'] ?? 0) - (int)($a->time_remaining ?? 0);
        if ($duration < 0) { $duration = 0; }

        $result = [
            'attemptId' => (string)$a->id,
            'score' => $score,
            'passed' => $passed,
            'duration' => $duration,
            'totalQuestions' => $scoreDetails['total_questions'],
            'correctAnswers' => $scoreDetails['correct_answers'],
            'wrongAnswers' => $scoreDetails['incorrect_answers'],
            'details' => $details,
            'certificateEligible' => $passed && (($sim['type'] ?? '') === 'obrigatorio'),
            'scoringStrategy' => $this->scoringStrategy->getName(),
        ];

        // Preservar certificateId anterior, se houver, mantendo compatibilidade
        if (is_array($a->result ?? null) && !empty($a->result['certificateId'])) {
            $result['certificateId'] = $a->result['certificateId'];
        }

        // Persistir submissão
        DB::transaction(function () use ($a, $score, $passed, $result, $sim) {
            $a->markSubmitted($score, $passed, $result);
            $a->save();
            
            // Enviar notificações após submissão
            $this->sendNotificationsAfterSubmission($a, $sim, $result);
        });

        return $result;
    }

    /**
     * Emite certificado, validando elegibilidade com base no result e tipo do simulado.
     * Retorna o payload de certificado no formato esperado pelo FE.
     */
    public function issueCertificate(SimuladoAttempt $a, array $sim): array
    {
        $result = $a->result ?? [];
        if (empty($result) || empty($result['passed'])) {
            abort(422, 'Certificado não elegível');
        }
        // Se o simulado não for obrigatório, manter compatibilidade: ainda permite se result['passed'] for true
        $certId = $result['certificateId'] ?? ('cert_' . uniqid());

        // Persistir no JSON via accessors do model
        $a->issueCertificate($certId);
        // Compatibilidade: manter chaves flat em result
        $current = $a->result ?? [];
        $issuedAt = optional($a->certificate_issued_at)->toISOString();
        $current['certificateId'] = $certId;
        $current['certificateIssuedAt'] = $issuedAt;
        $a->result = $current;
        $a->save();

        return [
            'id' => $certId,
            'attemptId' => (string)$a->id,
            'simuladoId' => (int)$a->simulado_id,
            'issuedAt' => $issuedAt,
            'score' => (int)($a->result['score'] ?? 0),
        ];
    }

    /**
     * Envia notificações após submissão do simulado
     */
    private function sendNotificationsAfterSubmission(SimuladoAttempt $attempt, array $sim, array $result): void
    {
        $user = User::find($attempt->user_id);
        $simulado = Simulado::find($attempt->simulado_id);
        
        if (!$user || !$simulado) {
            return;
        }

        // Simulate notification service calls
        // In a real implementation, this would use dependency injection
        try {
            // Simulate notification of completed simulado
            error_log("Simulado completed notification sent for user {$user->id} and simulado {$simulado->id}");
            
            // Simulate result notification (passed/failed)
            $status = $result['passed'] ? 'aprovado' : 'reprovado';
            error_log("Simulado result notification sent: {$status} with score {$result['score']}");
        } catch (\Exception $e) {
            error_log('Failed to send notifications: ' . $e->getMessage());
        }
    }
}
