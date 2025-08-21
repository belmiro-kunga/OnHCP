<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class SimuladoAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'simulado_id','user_id','current_question','answers','time_remaining',
        'submitted_at','score','passed','result'
    ];

    protected $casts = [
        'current_question' => 'integer',
        'answers' => 'array',
        'time_remaining' => 'integer',
        'submitted_at' => 'datetime',
        'score' => 'integer',
        'passed' => 'boolean',
        'result' => 'array',
    ];

    public function simulado() { return $this->belongsTo(Simulado::class); }

    /**
     * Accessor: certificateId armazenado em result[certificate][id]
     */
    public function getCertificateIdAttribute(): ?string
    {
        $result = $this->result ?? [];
        return $result['certificate']['id'] ?? null;
    }

    /**
     * Mutator: certificateId em result[certificate][id]
     */
    public function setCertificateIdAttribute(?string $value): void
    {
        $result = $this->result ?? [];
        $result['certificate'] = $result['certificate'] ?? [];
        $result['certificate']['id'] = $value;
        $this->result = $result;
    }

    /**
     * Accessor: certificateIssuedAt armazenado em result[certificate][issuedAt]
     */
    public function getCertificateIssuedAtAttribute(): ?Carbon
    {
        $result = $this->result ?? [];
        $iso = $result['certificate']['issuedAt'] ?? null;
        return $iso ? Carbon::parse($iso) : null;
    }

    /**
     * Mutator: certificateIssuedAt em result[certificate][issuedAt] (ISO8601)
     */
    public function setCertificateIssuedAtAttribute($value): void
    {
        $issuedAt = $value instanceof Carbon ? $value : ($value ? Carbon::parse($value) : null);
        $result = $this->result ?? [];
        $result['certificate'] = $result['certificate'] ?? [];
        $result['certificate']['issuedAt'] = $issuedAt ? $issuedAt->toISOString() : null;
        $this->result = $result;
    }

    /**
     * Emite (ou regrava preservando) certificado no result JSON.
     * Se $issuedAt não fornecido, usa agora().
     */
    public function issueCertificate(string $certificateId, ?Carbon $issuedAt = null): void
    {
        // Preservar data original, se já existir
        $currentIssuedAt = $this->certificate_issued_at; // accessor
        $this->certificate_id = $certificateId;
        $this->certificate_issued_at = $currentIssuedAt ?: ($issuedAt ?: now());
    }

    /**
     * Cálculo simples de score: percentual de acertos.
     * $questions deve conter pares id => correctAnswer ou lista com chaves.
     */
    public function calculateScore(array $questions, array $answers): int
    {
        if (empty($questions)) return 0;
        $total = 0; $correct = 0;
        foreach ($questions as $q) {
            $qid = is_array($q) ? ($q['id'] ?? null) : null;
            $corr = is_array($q) ? ($q['correctAnswer'] ?? null) : null;
            if ($qid === null) continue;
            $total++;
            if (array_key_exists($qid, $answers) && $answers[$qid] === $corr) {
                $correct++;
            }
        }
        if ($total === 0) return 0;
        return (int) floor(($correct / $total) * 100);
    }

    /**
     * Marca a tentativa como submetida, preenchendo campos principais.
     */
    public function markSubmitted(int $score, bool $passed, array $resultExtras = []): void
    {
        $this->submitted_at = now();
        $this->score = $score;
        $this->passed = $passed;
        $base = $this->result ?? [];
        $this->result = array_merge($base, $resultExtras);
    }

    /**
     * Scopes utilitários
     */
    public function scopeByUserSimulado($query, int $userId, int $simuladoId)
    {
        return $query->where('user_id', $userId)->where('simulado_id', $simuladoId);
    }

    public function scopeSubmitted($query)
    {
        return $query->whereNotNull('submitted_at');
    }
}
