<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Course;
use App\Models\Certificate;
use App\Models\UserCourseEnrollment;
use App\Models\UserLessonProgress;
use App\Http\Resources\EnrollmentResource;

class CacheOptimizationService
{
    const CACHE_TTL = 300; // 5 minutos
    const STATS_CACHE_TTL = 900; // 15 minutos
    const HEAVY_QUERY_TTL = 1800; // 30 minutos

    /**
     * Cache para estatísticas de usuários
     */
    public function getUserStats(): array
    {
        return Cache::remember('user_stats', self::STATS_CACHE_TTL, function () {
            return [
                'total_users' => User::count(),
                'active_users' => User::where('status', 'active')->count(),
                'users_this_month' => User::whereMonth('created_at', now()->month)
                                         ->whereYear('created_at', now()->year)
                                         ->count(),
                'users_by_department' => User::select('departments.name', DB::raw('COUNT(*) as total'))
                                            ->leftJoin('departments', 'users.department_id', '=', 'departments.id')
                                            ->groupBy('departments.name')
                                            ->orderBy('total', 'desc')
                                            ->limit(10)
                                            ->get()
            ];
        });
    }

    /**
     * Cache para estatísticas de cursos
     */
    public function getCourseStats(): array
    {
        return Cache::remember('course_stats', self::STATS_CACHE_TTL, function () {
            return [
                'total_courses' => Course::count(),
                'active_courses' => Course::where('is_active', true)->count(),
                'total_enrollments' => UserCourseEnrollment::count(),
                'active_enrollments' => UserCourseEnrollment::where('status', 'active')->count(),
                'completed_enrollments' => UserCourseEnrollment::where('status', 'completed')->count(),
                'completion_rate' => $this->calculateCompletionRate(),
                'popular_courses' => Course::select('courses.id', 'courses.title', DB::raw('COUNT(user_course_enrollments.id) as enrollments_count'))
                                          ->leftJoin('user_course_enrollments', 'courses.id', '=', 'user_course_enrollments.course_id')
                                          ->groupBy('courses.id', 'courses.title')
                                          ->orderBy('enrollments_count', 'desc')
                                          ->limit(10)
                                          ->get()
            ];
        });
    }

    /**
     * Cache para estatísticas de certificados
     */
    public function getCertificateStats(): array
    {
        return Cache::remember('certificate_stats', self::STATS_CACHE_TTL, function () {
            return [
                'total_certificates' => Certificate::count(),
                'active_certificates' => Certificate::where('status', 'active')->count(),
                'revoked_certificates' => Certificate::where('status', 'revoked')->count(),
                'certificates_this_month' => Certificate::whereMonth('issued_at', now()->month)
                                                      ->whereYear('issued_at', now()->year)
                                                      ->count(),
                'certificates_by_course' => Certificate::select('courses.title', DB::raw('COUNT(*) as total'))
                                                      ->join('courses', 'certificates.course_id', '=', 'courses.id')
                                                      ->groupBy('courses.title')
                                                      ->orderBy('total', 'desc')
                                                      ->limit(10)
                                                      ->get()
            ];
        });
    }

    /**
     * Cache para matrículas do usuário com paginação
     */
    public function getUserEnrollments(int $userId, array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $cacheKey = "user_enrollments_{$userId}_" . md5(serialize($filters) . "_{$page}_{$perPage}");
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId, $filters, $page, $perPage) {
            $query = UserCourseEnrollment::where('user_id', $userId)
                ->with(['course', 'lessonProgress']);

            // Aplicar filtros
            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['completed'])) {
                if ($filters['completed']) {
                    $query->where('status', 'completed');
                } else {
                    $query->where('status', '!=', 'completed');
                }
            }

            $enrollments = $query->orderBy('enrolled_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            return [
                'data' => EnrollmentResource::collection($enrollments->items()),
                'current_page' => $enrollments->currentPage(),
                'last_page' => $enrollments->lastPage(),
                'per_page' => $enrollments->perPage(),
                'total' => $enrollments->total(),
            ];
        });
    }

    /**
     * Cache para detalhes de uma matrícula específica
     */
    public function getEnrollmentDetails(int $enrollmentId, int $userId): ?array
    {
        $cacheKey = "enrollment_details_{$enrollmentId}_{$userId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($enrollmentId, $userId) {
            $enrollment = UserCourseEnrollment::where('id', $enrollmentId)
                ->where('user_id', $userId)
                ->with(['course.modules.lessons', 'lessonProgress.lesson'])
                ->first();

            return $enrollment ? (new EnrollmentResource($enrollment))->toArray(request()) : null;
        });
    }

    /**
     * Cache para lista de usuários com filtros
     */
    public function getUsers(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        $cacheKey = "users_list_" . md5(serialize($filters) . "_{$page}_{$perPage}");
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters, $page, $perPage) {
            $query = User::with(['role:id,name', 'department:id,name'])
                        ->select(['id', 'name', 'email', 'status', 'role_id', 'department_id', 'created_at']);

            // Aplicar filtros
            if (isset($filters['search']) && !empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            return $query->orderBy('created_at', 'desc')
                        ->paginate($perPage, ['*'], 'page', $page)
                        ->toArray();
        });
    }

    /**
     * Limpar cache relacionado a um usuário específico
     */
    public function clearUserCache(int $userId): void
    {
        $patterns = [
            "user_enrollments_{$userId}_*",
            "enrollment_details_*_{$userId}",
            'user_stats',
            'course_stats'
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }

        // Limpar cache de listas que podem incluir este usuário
        $this->clearListCaches();
    }

    /**
     * Limpar cache relacionado a cursos
     */
    public function clearCourseCache(int $courseId = null): void
    {
        Cache::forget('course_stats');
        Cache::forget('certificate_stats');
        
        if ($courseId) {
            // Limpar cache de matrículas relacionadas a este curso
            $enrollments = UserCourseEnrollment::where('course_id', $courseId)->pluck('user_id');
            foreach ($enrollments as $userId) {
                $this->clearUserCache($userId);
            }
        }
    }

    /**
     * Limpar cache de listas gerais
     */
    public function clearListCaches(): void
    {
        $patterns = [
            'users_list_*',
            'user_stats',
            'course_stats',
            'certificate_stats'
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }

    /**
     * Calcular taxa de conclusão (método auxiliar)
     */
    private function calculateCompletionRate(): float
    {
        $totalEnrollments = UserCourseEnrollment::count();
        if ($totalEnrollments === 0) {
            return 0;
        }

        $completedEnrollments = UserCourseEnrollment::where('status', 'completed')->count();
        return round(($completedEnrollments / $totalEnrollments) * 100, 2);
    }



    /**
     * Limpar cache por padrão (implementação básica)
     */
    private function clearCacheByPattern(string $pattern): void
    {
        // Para Redis, podemos usar SCAN com padrão
        // Para outros drivers, precisamos manter uma lista de chaves
        // Por simplicidade, vamos invalidar cache relacionado
        $this->clearListCaches();
    }

    /**
     * Invalidar cache quando dados são modificados
     */
    public function invalidateRelatedCache(string $model, int $id = null): void
    {
        switch ($model) {
            case 'User':
                if ($id) {
                    $this->clearUserCache($id);
                } else {
                    $this->clearListCaches();
                }
                break;
                
            case 'Course':
                $this->clearCourseCache($id);
                break;
                
            case 'UserCourseEnrollment':
                if ($id) {
                    $enrollment = UserCourseEnrollment::find($id);
                    if ($enrollment) {
                        $this->clearUserCache($enrollment->user_id);
                        $this->clearCourseCache($enrollment->course_id);
                    }
                }
                break;
                
            case 'Certificate':
                Cache::forget('certificate_stats');
                break;
        }
    }
}