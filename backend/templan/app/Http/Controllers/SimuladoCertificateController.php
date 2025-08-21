<?php

namespace App\Http\Controllers;

use App\Models\SimuladoAttempt;
use App\Services\SimuladoAttemptService;
use Illuminate\Http\Request;
use App\Http\Requests\IssueCertificateRequest;

class SimuladoCertificateController extends Controller
{
    public function issue(IssueCertificateRequest $request)
    {
        $simuladoId = (int)$request->input('simuladoId');
        $attemptId = (string)$request->input('attemptId');

        $a = SimuladoAttempt::find($attemptId);
        if (!$a || (int)$a->simulado_id !== $simuladoId) {
            return response()->json(['message' => 'Tentativa inválida'], 422);
        }
        // Simulado metadata vem do catálogo interno do AttemptController; aqui, só garantimos elegibilidade pelo result
        /** @var SimuladoAttemptService $svc */
        $svc = app(SimuladoAttemptService::class);
        $cert = $svc->issueCertificate($a, []);
        return response()->json($cert, 201);
    }

    public function verify(Request $request)
    {
        $code = (string)$request->query('code', '');
        if ($code === '') { return response()->json(['message' => 'Código requerido'], 422); }

        $attempt = SimuladoAttempt::where('result->certificateId', $code)->first();
        if (!$attempt) {
            return response()->json(['valid' => false], 404);
        }
        $result = $attempt->result ?? [];
        $cert = [
            'id' => $code,
            'attemptId' => (string)$attempt->id,
            'simuladoId' => (int)$attempt->simulado_id,
            'issuedAt' => (string)($result['certificateIssuedAt'] ?? now()->toISOString()),
            'score' => (int)($result['score'] ?? 0),
        ];
        return response()->json(['valid' => true, 'certificate' => $cert]);
    }
}
