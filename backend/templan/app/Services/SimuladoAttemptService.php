<?php
namespace App\Services;

use App\Models\SimuladoAttempt;
use App\Models\User;
use App\Models\Simulado;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

class SimuladoAttemptService
{
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

        // Calcular score usando método do model
        $score = $a->calculateScore($questions, $a->answers ?? []);
        $passed = $score >= (int)($sim['minScore'] ?? 0);

        // Montar details (mantendo formato atual)
        $correct = 0; $details = [];
        foreach ($questions as $q) {
            $userAnswer = $a->answers[$q['id']] ?? null;
            $isCorrect = $userAnswer !== null && $userAnswer === ($q['correctAnswer'] ?? null);
            if ($isCorrect) { $correct++; }
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
            'totalQuestions' => $total,
            'correctAnswers' => $correct,
            'wrongAnswers' => max(0, $total - $correct),
            'details' => $details,
            'certificateEligible' => $passed && (($sim['type'] ?? '') === 'obrigatorio'),
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

        $notificationService = app(NotificationService::class);
        
        // Notificação de simulado concluído
        $notificationService->simuladoCompleted($user, $simulado, $attempt);
        
        // Notificação de resultado (aprovado/reprovado)
        $notificationService->simuladoResult($user, $simulado, $attempt, $result['passed'], $result['score']);
    }
}
