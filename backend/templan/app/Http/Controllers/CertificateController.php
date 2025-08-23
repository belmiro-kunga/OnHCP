<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\UserCourseEnrollment;
use App\Http\Resources\CertificateResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;
use Carbon\Carbon;

class CertificateController extends Controller
{
    /**
     * Gerar certificado para uma matrícula concluída
     */
    public function generate(UserCourseEnrollment $enrollment)
    {
        // Verificar se a matrícula está concluída
        if ($enrollment->status !== 'completed') {
            return response()->json([
                'message' => 'Certificado só pode ser gerado para matrículas concluídas'
            ], 400);
        }

        // Verificar se já existe certificado
        $existingCertificate = Certificate::where('user_course_enrollment_id', $enrollment->id)->first();
        if ($existingCertificate) {
            return response()->json([
                'message' => 'Certificado já foi gerado para esta matrícula',
                'certificate' => new CertificateResource($existingCertificate)
            ], 200);
        }

        // Criar certificado
        $certificate = Certificate::create([
            'user_course_enrollment_id' => $enrollment->id,
            'user_id' => $enrollment->user_id,
            'course_id' => $enrollment->course_id,
            'issued_at' => now(),
            'final_grade' => $enrollment->final_grade ?? 0,
            'completion_hours' => $enrollment->course->duration_hours ?? 0,
            'certificate_data' => [
                'user_name' => $enrollment->user->name,
                'course_title' => $enrollment->course->title,
                'completion_date' => $enrollment->completed_at,
                'instructor' => $enrollment->course->instructor ?? 'Sistema',
                'course_description' => $enrollment->course->description
            ]
        ]);

        // Gerar PDF
        $this->generatePDF($certificate);

        // Marcar certificado como emitido na matrícula
        $enrollment->update([
            'certificate_issued' => true,
            'certificate_issued_at' => now()
        ]);

        return response()->json([
            'message' => 'Certificado gerado com sucesso',
            'certificate' => new CertificateResource($certificate)
        ], 201);
    }

    /**
     * Listar certificados do usuário autenticado
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $certificates = Certificate::where('user_id', $user->id)
            ->with(['course', 'enrollment'])
            ->orderBy('issued_at', 'desc')
            ->paginate(10);

        return CertificateResource::collection($certificates);
    }

    /**
     * Visualizar certificado específico
     */
    public function show(Certificate $certificate, Request $request)
    {
        $user = $request->user();
        
        // Verificar se o usuário tem permissão para ver o certificado
        if ($certificate->user_id !== $user->id && !$user->hasRole('admin')) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        return new CertificateResource($certificate);
    }

    /**
     * Baixar certificado em PDF
     */
    public function download(Certificate $certificate, Request $request)
    {
        $user = $request->user();
        
        // Verificar permissão
        if ($certificate->user_id !== $user->id && !$user->hasRole('admin')) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        // Verificar se o certificado está ativo
        if ($certificate->status !== 'active') {
            return response()->json(['message' => 'Certificado não está disponível'], 400);
        }

        // Gerar PDF se não existir
        if (!$certificate->file_path || !Storage::exists($certificate->file_path)) {
            $this->generatePDF($certificate);
        }

        $filePath = storage_path('app/' . $certificate->file_path);
        
        if (!file_exists($filePath)) {
            return response()->json(['message' => 'Arquivo do certificado não encontrado'], 404);
        }

        return response()->download($filePath, "certificado_{$certificate->certificate_number}.pdf");
    }

    /**
     * Verificar autenticidade do certificado
     */
    public function verify(Request $request)
    {
        $request->validate([
            'verification_code' => 'required|string'
        ]);

        $certificate = Certificate::where('verification_code', $request->verification_code)
            ->where('status', 'active')
            ->with(['user', 'course'])
            ->first();

        if (!$certificate) {
            return response()->json([
                'valid' => false,
                'message' => 'Código de verificação inválido ou certificado não encontrado'
            ], 404);
        }

        // Verificar se não expirou
        if ($certificate->expires_at && $certificate->expires_at->isPast()) {
            return response()->json([
                'valid' => false,
                'message' => 'Certificado expirado'
            ], 400);
        }

        return response()->json([
            'valid' => true,
            'certificate' => [
                'number' => $certificate->certificate_number,
                'user_name' => $certificate->user->name,
                'course_title' => $certificate->course->title,
                'issued_at' => $certificate->issued_at->format('d/m/Y'),
                'final_grade' => $certificate->final_grade,
                'completion_hours' => $certificate->completion_hours
            ]
        ]);
    }

    /**
     * Revogar certificado (apenas admin)
     */
    public function revoke(Certificate $certificate, Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $certificate->update([
            'status' => 'revoked',
            'revocation_reason' => $request->reason,
            'revoked_at' => now()
        ]);

        return response()->json([
            'message' => 'Certificado revogado com sucesso'
        ]);
    }

    /**
     * Listar todos os certificados (admin)
     */
    public function adminIndex(Request $request)
    {
        $query = Certificate::with(['user', 'course', 'enrollment']);

        // Filtros
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhereHas('course', function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            })->orWhere('certificate_number', 'like', "%{$search}%");
        }

        $certificates = $query->orderBy('issued_at', 'desc')->paginate(15);

        return CertificateResource::collection($certificates);
    }

    /**
     * Estatísticas de certificados (admin)
     */
    public function stats()
    {
        $stats = [
            'total_certificates' => Certificate::count(),
            'active_certificates' => Certificate::where('status', 'active')->count(),
            'revoked_certificates' => Certificate::where('status', 'revoked')->count(),
            'certificates_this_month' => Certificate::whereMonth('issued_at', now()->month)
                                                  ->whereYear('issued_at', now()->year)
                                                  ->count(),
            'certificates_by_course' => Certificate::selectRaw('course_id, courses.title, COUNT(*) as total')
                                                  ->join('courses', 'certificates.course_id', '=', 'courses.id')
                                                  ->groupBy('course_id', 'courses.title')
                                                  ->orderBy('total', 'desc')
                                                  ->limit(10)
                                                  ->get(),
            'recent_certificates' => Certificate::with(['user', 'course'])
                                               ->orderBy('issued_at', 'desc')
                                               ->limit(5)
                                               ->get()
        ];

        return response()->json($stats);
    }

    /**
     * Gerar PDF do certificado
     */
    private function generatePDF(Certificate $certificate)
    {
        $data = [
            'certificate' => $certificate,
            'user' => $certificate->user,
            'course' => $certificate->course,
            'enrollment' => $certificate->enrollment,
            'issued_date' => $certificate->issued_at->format('d \\d\\e F \\d\\e Y'),
            'verification_url' => url("/certificates/verify?code={$certificate->verification_code}")
        ];

        $pdf = Pdf::loadView('certificates.template', $data)
                  ->setPaper('a4', 'landscape')
                  ->setOptions([
                      'dpi' => 150,
                      'defaultFont' => 'sans-serif',
                      'isHtml5ParserEnabled' => true,
                      'isPhpEnabled' => true
                  ]);

        $fileName = "certificates/certificate_{$certificate->certificate_number}.pdf";
        Storage::put($fileName, $pdf->output());

        $certificate->update(['file_path' => $fileName]);
    }
}