<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AttemptResource extends JsonResource
{
    public function toArray($request)
    {
        // Mantém o formato atual usado pelo FE
        $attempt = $this->resource;
        $sim = $this->additional['simulado'] ?? null; // opcional

        return [
            'id' => (string)$attempt->id,
            'attemptId' => (string)$attempt->id,
            'simuladoId' => (int)$attempt->simulado_id,
            'createdAt' => optional($attempt->created_at)->toISOString(),
            'submittedAt' => optional($attempt->submitted_at)->toISOString(),
            'currentQuestion' => (int)$attempt->current_question,
            'answers' => $attempt->answers ?? [],
            'timeRemaining' => $attempt->time_remaining ?? ($sim['duration'] ?? 0),
            'result' => $attempt->result ?? null,
        ];
    }
}
