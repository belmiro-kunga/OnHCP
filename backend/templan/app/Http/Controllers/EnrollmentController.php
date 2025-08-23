<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\UserCourseEnrollment;
use App\Models\UserLessonProgress;
use App\Http\Resources\EnrollmentResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\CacheOptimizationService;

class EnrollmentController extends Controller
{
    protected $cacheService;

    public function __construct(CacheOptimizationService $cacheService)
    {
        $this->cacheService = $cacheService;
    }
    /**
     * Matricular usuário em um curso
     */
    public function enroll(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'enrollment_metadata' => 'nullable|array',
        ]);

        $user = Auth::user();
        $courseId = $request->course_id;

        // Verificar se o usuário já está matriculado
        $existingEnrollment = UserCourseEnrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->first();

        if ($existingEnrollment) {
            if ($existingEnrollment->status === 'active') {
                return response()->json([
                    'message' => 'Usuário já está matriculado neste curso',
                    'enrollment' => new EnrollmentResource($existingEnrollment)
                ], 409);
            }

            // Reativar matrícula se estava suspensa ou cancelada
            if (in_array($existingEnrollment->status, ['suspended', 'cancelled'])) {
                $existingEnrollment->reactivate();
                return response()->json([
                    'message' => 'Matrícula reativada com sucesso',
                    'enrollment' => new EnrollmentResource($existingEnrollment)
                ]);
            }
        }

        // Verificar se o curso existe e está ativo
        $course = Course::findOrFail($courseId);
        if (!$course->is_active) {
            return response()->json([
                'message' => 'Este curso não está disponível para matrícula'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Criar nova matrícula
            $enrollment = UserCourseEnrollment::create([
                'user_id' => $user->id,
                'course_id' => $courseId,
                'status' => 'active',
                'enrolled_at' => now(),
                'total_lessons' => $course->lessons()->count(),
                'enrollment_metadata' => $request->enrollment_metadata ?? [],
            ]);

            // Criar registros de progresso para todas as aulas do curso
            $lessons = $course->lessons;
            foreach ($lessons as $lesson) {
                UserLessonProgress::create([
                    'enrollment_id' => $enrollment->id,
                    'course_lesson_id' => $lesson->id,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Matrícula realizada com sucesso',
                'enrollment' => new EnrollmentResource($enrollment->load(['course', 'lessonProgress']))
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao realizar matrícula',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar matrículas do usuário
     */
    public function myEnrollments(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Preparar filtros
        $filters = [];
        if ($request->has('status')) {
            $filters['status'] = $request->status;
        }
        if ($request->has('completed')) {
            $filters['completed'] = filter_var($request->completed, FILTER_VALIDATE_BOOLEAN);
        }

        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 15);
        
        // Usar cache para consultas de matrículas
        $result = $this->cacheService->getUserEnrollments($user->id, $filters, $page, $perPage);

        return response()->json([
            'enrollments' => $result['data'],
            'pagination' => [
                'current_page' => $result['current_page'],
                'last_page' => $result['last_page'],
                'per_page' => $result['per_page'],
                'total' => $result['total'],
            ]
        ]);
    }

    /**
     * Obter detalhes de uma matrícula específica
     */
    public function show(int $enrollmentId): JsonResponse
    {
        $user = Auth::user();
        
        // Usar cache para detalhes da matrícula
        $enrollmentData = $this->cacheService->getEnrollmentDetails($enrollmentId, $user->id);
        
        if (!$enrollmentData) {
            return response()->json(['message' => 'Matrícula não encontrada'], 404);
        }

        return response()->json([
            'enrollment' => $enrollmentData
        ]);
    }

    /**
     * Atualizar progresso de uma aula
     */
    public function updateLessonProgress(Request $request, int $enrollmentId, int $lessonId): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:start,complete,update_time',
            'watch_time_seconds' => 'nullable|integer|min:0',
            'total_duration_seconds' => 'nullable|integer|min:0',
        ]);

        $user = Auth::user();
        
        // Verificar se a matrícula pertence ao usuário
        $enrollment = UserCourseEnrollment::where('id', $enrollmentId)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->firstOrFail();

        // Encontrar o progresso da aula
        $progress = UserLessonProgress::where('enrollment_id', $enrollmentId)
            ->where('course_lesson_id', $lessonId)
            ->firstOrFail();

        switch ($request->action) {
            case 'start':
                $progress->markAsStarted();
                break;
                
            case 'complete':
                $progress->markAsCompleted();
                break;
                
            case 'update_time':
                if ($request->has('watch_time_seconds')) {
                    $progress->updateWatchTime(
                        $request->watch_time_seconds,
                        $request->total_duration_seconds
                    );
                }
                break;
        }

        return response()->json([
            'message' => 'Progresso atualizado com sucesso',
            'progress' => [
                'lesson_id' => $progress->course_lesson_id,
                'started' => $progress->started,
                'completed' => $progress->completed,
                'completion_percentage' => $progress->completion_percentage,
                'watch_time_seconds' => $progress->watch_time_seconds,
                'last_accessed_at' => $progress->last_accessed_at,
            ],
            'enrollment_progress' => $enrollment->fresh()->progress_percentage
        ]);
    }

    /**
     * Cancelar matrícula
     */
    public function cancel(int $enrollmentId): JsonResponse
    {
        $user = Auth::user();
        
        $enrollment = UserCourseEnrollment::where('id', $enrollmentId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($enrollment->status === 'completed') {
            return response()->json([
                'message' => 'Não é possível cancelar uma matrícula já completada'
            ], 400);
        }

        $enrollment->cancel();

        return response()->json([
            'message' => 'Matrícula cancelada com sucesso'
        ]);
    }

    /**
     * Obter estatísticas das matrículas (para administradores)
     */
    public function statistics(Request $request): JsonResponse
    {
        // Verificar se o usuário é administrador
        $user = Auth::user();
        if (!$user->is_admin) {
            return response()->json(['message' => 'Acesso negado'], 403);
        }

        $stats = [
            'total_enrollments' => UserCourseEnrollment::count(),
            'active_enrollments' => UserCourseEnrollment::active()->count(),
            'completed_enrollments' => UserCourseEnrollment::completed()->count(),
            'certificates_issued' => UserCourseEnrollment::withCertificate()->count(),
            'enrollments_this_month' => UserCourseEnrollment::enrolledBetween(
                now()->startOfMonth(),
                now()->endOfMonth()
            )->count(),
            'completion_rate' => $this->calculateCompletionRate(),
            'average_completion_time' => $this->calculateAverageCompletionTime(),
        ];

        return response()->json(['statistics' => $stats]);
    }

    /**
     * Calcular taxa de conclusão
     */
    private function calculateCompletionRate(): float
    {
        $total = UserCourseEnrollment::count();
        if ($total === 0) return 0;
        
        $completed = UserCourseEnrollment::completed()->count();
        return round(($completed / $total) * 100, 2);
    }

    /**
     * Calcular tempo médio de conclusão em dias
     */
    private function calculateAverageCompletionTime(): float
    {
        $completedEnrollments = UserCourseEnrollment::completed()
            ->whereNotNull('completed_at')
            ->get();

        if ($completedEnrollments->isEmpty()) return 0;

        $totalDays = $completedEnrollments->sum(function ($enrollment) {
            return $enrollment->enrolled_at->diffInDays($enrollment->completed_at);
        });

        return round($totalDays / $completedEnrollments->count(), 1);
    }
}