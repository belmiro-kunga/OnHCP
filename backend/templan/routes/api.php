<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SecurityIpController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\PermissionRequestController;
use App\Http\Controllers\DelegationController;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\RoleMappingController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\Ms365AuthController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\JobPositionController;
use App\Http\Controllers\OrganizationalStructureController;
use App\Http\Controllers\ExternalIntegrationController;
use App\Http\Controllers\SyncLogController;
use App\Http\Controllers\OnboardingRoadmapController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentSignatureController;
use App\Http\Controllers\UserOnboardingProgressController;
use App\Http\Controllers\AdminSimuladoController;
use App\Http\Controllers\AdminSimuladoAssignmentController;
use App\Http\Controllers\UserSimuladoController;
use App\Http\Controllers\SimuladoCategoryController;
use App\Http\Controllers\SimuladoAttemptController;
use App\Http\Controllers\SimuladoCertificateController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Api\NotificationPreferencesController;
use App\Http\Controllers\Api\ReportsController;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Laravel backend is running',
        'timestamp' => now()->toISOString()
    ]);
});

// ------------------------------------------------------------
// Admin Simulados CRUD + Assignments (dev: public; secure with auth in prod)
// ------------------------------------------------------------
Route::prefix('admin/simulados')->group(function () {
    Route::get('/', [AdminSimuladoController::class, 'index']);
    Route::post('/', [AdminSimuladoController::class, 'store']);
    Route::get('/{simulado}', [AdminSimuladoController::class, 'show']);
    Route::put('/{simulado}', [AdminSimuladoController::class, 'update']);
    Route::delete('/{simulado}', [AdminSimuladoController::class, 'destroy']);

    // Assignments
    Route::get('/{simulado}/assignments', [AdminSimuladoAssignmentController::class, 'index']);
    Route::post('/{simulado}/assignments', [AdminSimuladoAssignmentController::class, 'store']);
});
Route::delete('/admin/assignments/{assignment}', [AdminSimuladoAssignmentController::class, 'destroy']);

// Categories for Simulados
Route::middleware('auth:sanctum')->prefix('admin/simulado-categories')->group(function () {
    Route::get('/', [SimuladoCategoryController::class, 'index']);
    Route::post('/', [SimuladoCategoryController::class, 'store']);
    Route::put('/{category}', [SimuladoCategoryController::class, 'update']);
    Route::delete('/{category}', [SimuladoCategoryController::class, 'destroy']);
});

// ------------------------------------------------------------
// User endpoints for Simulados
// ------------------------------------------------------------
Route::get('/me/simulados', [UserSimuladoController::class, 'mySimulados']);

// Email (SMTP) config + test (protected)
Route::middleware('auth:sanctum')->prefix('email')->group(function () {
    Route::get('/config', [EmailController::class, 'show'])->middleware('can:users.manage');
    Route::put('/config', [EmailController::class, 'update'])->middleware('can:users.manage');
    Route::post('/test', [EmailController::class, 'test'])->middleware('can:users.manage');
});

// Role Mappings (protected)
Route::middleware('auth:sanctum')->prefix('role-mappings')->group(function () {
    Route::get('/', [RoleMappingController::class, 'index'])->middleware('can:users.manage');
    Route::post('/', [RoleMappingController::class, 'store'])->middleware('can:users.manage');
    Route::put('/{roleMapping}', [RoleMappingController::class, 'update'])->middleware('can:users.manage');
    Route::delete('/{roleMapping}', [RoleMappingController::class, 'destroy'])->middleware('can:users.manage');
    Route::post('/reorder', [RoleMappingController::class, 'reorder'])->middleware('can:users.manage');
});

// Directory (LDAP/AD) config + test (protected)
Route::middleware('auth:sanctum')->prefix('directory')->group(function () {
    Route::get('/config', [DirectoryController::class, 'show'])->middleware('can:users.manage');
    Route::put('/config', [DirectoryController::class, 'update'])->middleware('can:users.manage');
    Route::post('/test-connection', [DirectoryController::class, 'testConnection'])->middleware('can:users.manage');
    Route::get('/groups', [DirectoryController::class, 'groups'])->middleware('can:users.manage');
});

// Reports - Dashboard de Relatórios
Route::middleware('auth:sanctum')->prefix('reports')->group(function () {
    // Dashboard principal
    Route::get('/dashboard', [ReportsController::class, 'dashboard']);
    Route::get('/overview', [ReportsController::class, 'overview']);
    
    // Métricas e tendências
    Route::get('/trends', [ReportsController::class, 'trends']);
    Route::get('/performance', [ReportsController::class, 'performance']);
    Route::get('/system', [ReportsController::class, 'system']);
    
    // Relatórios específicos
    Route::get('/simulados/categories', [ReportsController::class, 'simuladosByCategory']);
    Route::get('/users/top-performers', [ReportsController::class, 'topPerformers']);
    Route::get('/scores/distribution', [ReportsController::class, 'scoreDistribution']);
    
    // Estatísticas pessoais (disponível para todos os usuários)
    Route::get('/my-stats', [ReportsController::class, 'myStats']);
    
    // Administração
    Route::post('/collect-metrics', [ReportsController::class, 'collectMetrics'])->middleware('admin');
    Route::post('/clear-cache', [ReportsController::class, 'clearCache'])->middleware('admin');
    Route::get('/export', [ReportsController::class, 'export'])->middleware('admin');
    
    // Rotas antigas (manter compatibilidade temporária)
    Route::get('/metrics', [ReportController::class, 'metrics']);
    Route::get('/access', [ReportController::class, 'access']);
    Route::get('/permissions', [ReportController::class, 'permissions']);
});

// Groups / Teams
Route::prefix('groups')->group(function () {
    Route::get('/', [GroupController::class, 'index']);
    Route::post('/', [GroupController::class, 'store']);
    Route::put('/{group}', [GroupController::class, 'update']);
    Route::delete('/{group}', [GroupController::class, 'destroy']);
    Route::get('/{group}/members', [GroupController::class, 'members']);
    Route::post('/{group}/members', [GroupController::class, 'addMember']);
    Route::delete('/{group}/members/{user}', [GroupController::class, 'removeMember']);
});

// Approvals
Route::prefix('approvals')->group(function () {
    Route::get('/', [PermissionRequestController::class, 'index']);
    Route::post('/', [PermissionRequestController::class, 'store']);
    Route::post('/{permissionRequest}/approve', [PermissionRequestController::class, 'approve']);
    Route::post('/{permissionRequest}/reject', [PermissionRequestController::class, 'reject']);
});

// Delegations
Route::prefix('delegations')->group(function () {
    Route::get('/', [DelegationController::class, 'index']);
    Route::post('/', [DelegationController::class, 'store']);
    Route::post('/{delegation}/revoke', [DelegationController::class, 'revoke']);
});

// Licenses
Route::prefix('licenses')->group(function () {
    Route::get('/', [LicenseController::class, 'index']);
    Route::post('/', [LicenseController::class, 'store']);
    Route::delete('/{license}', [LicenseController::class, 'destroy']);
    Route::get('/{license}/assignments', [LicenseController::class, 'assignments']);
    Route::post('/{license}/assign', [LicenseController::class, 'assign']);
    Route::post('/{license}/unassign', [LicenseController::class, 'unassign']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Auth
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    // Microsoft 365 OIDC (SSO)
    Route::get('/sso/ms365/redirect', [Ms365AuthController::class, 'redirect']);
    Route::get('/sso/ms365/callback', [Ms365AuthController::class, 'callback']);
    Route::post('/sso/ms365/logout', [Ms365AuthController::class, 'logout'])->middleware('auth:sanctum');

    // Development stubs for frontend integration
    Route::middleware('auth:sanctum')->group(function () {
        // Stats (normalized keys expected by FE)
        Route::get('/stats', function () {
            return response()->json([
                'data' => [
                    'traditional' => 156,
                    'mfa' => 89,
                    'sso' => 34,
                    // optional extras
                    'totalUsers' => 279,
                    'activeSessions' => 7,
                ]
            ]);
        });

        // Authentication methods
        Route::get('/methods', function () {
            return response()->json([
                'data' => [
                    [
                        'id' => 1,
                        'name' => 'Email/Senha',
                        'type' => 'password',
                        'description' => 'Autenticação tradicional com email e senha',
                        'enabled' => true,
                        'userCount' => 156,
                        'lastUpdated' => now()->subDays(2)->toISOString(),
                    ],
                    [
                        'id' => 2,
                        'name' => 'Microsoft 365 SSO',
                        'type' => 'sso',
                        'description' => 'Login via Microsoft 365',
                        'enabled' => true,
                        'userCount' => 34,
                        'lastUpdated' => now()->subDays(10)->toISOString(),
                    ],
                    [
                        'id' => 3,
                        'name' => 'MFA TOTP',
                        'type' => 'mfa',
                        'description' => 'Aplicativo autenticador (TOTP)',
                        'enabled' => true,
                        'userCount' => 89,
                        'lastUpdated' => now()->subDays(1)->toISOString(),
                    ],
                ]
            ]);
        });
        Route::post('/methods', function (Request $request) {
            $name = (string)($request->input('name', ''));
            $type = (string)($request->input('type', 'password'));
            $description = (string)($request->input('description', ''));
            $enabled = (bool)$request->input('enabled', false);

            // Basic validation
            $errors = [];
            if ($name === '' || strlen($name) > 100) {
                $errors['name'] = 'Nome é obrigatório e deve ter no máximo 100 caracteres';
            }
            $allowedTypes = ['password','mfa','sso','social'];
            if (!in_array($type, $allowedTypes, true)) {
                $errors['type'] = 'Tipo inválido';
            }
            if (!empty($errors)) {
                return response()->json(['message' => 'Validação falhou', 'errors' => $errors], 422);
            }

            $id = rand(1000, 9999);
            return response()->json([
                'data' => [
                    'id' => $id,
                    'name' => $name,
                    'type' => $type,
                    'description' => $description,
                    'enabled' => $enabled,
                    'userCount' => 0,
                    'lastUpdated' => now()->toISOString(),
                ]
            ], 201);
        });
        Route::patch('/methods/{id}', function ($id, Request $request) {
            $allowedTypes = ['password','mfa','sso','social'];
            $payload = [];
            $errors = [];

            if ($request->has('name')) {
                $name = (string)$request->input('name');
                if ($name === '' || strlen($name) > 100) {
                    $errors['name'] = 'Nome é obrigatório e deve ter no máximo 100 caracteres';
                } else {
                    $payload['name'] = $name;
                }
            }
            if ($request->has('description')) {
                $payload['description'] = (string)$request->input('description');
            }
            if ($request->has('enabled')) {
                $payload['enabled'] = (bool)$request->input('enabled');
            }
            if ($request->has('type')) {
                $type = (string)$request->input('type');
                if (!in_array($type, $allowedTypes, true)) {
                    $errors['type'] = 'Tipo inválido';
                } else {
                    $payload['type'] = $type;
                }
            }

            if (!empty($errors)) {
                return response()->json(['message' => 'Validação falhou', 'errors' => $errors], 422);
            }

            // Simulate fetching existing and merging
            $existing = [
                'id' => (int)$id,
                'name' => 'Método #' . (int)$id,
                'type' => 'password',
                'description' => 'Descrição',
                'enabled' => true,
                'userCount' => 10,
                'lastUpdated' => now()->subDays(5)->toISOString(),
            ];
            $updated = array_merge($existing, $payload, [
                'lastUpdated' => now()->toISOString(),
            ]);

            return response()->json(['data' => $updated]);
        });

        // Sessions (normalized for FE)
        Route::get('/sessions', function () {
            return response()->json([
                'data' => [
                    [
                        'id' => 'sess_1',
                        'user' => [ 'name' => 'Admin Demo', 'email' => 'admin@demo.com' ],
                        'ipAddress' => '127.0.0.1',
                        'device' => 'Chrome/Windows',
                        'startTime' => now()->subHours(3)->toISOString(),
                        'lastActivity' => now()->toISOString(),
                    ],
                    [
                        'id' => 'sess_2',
                        'user' => [ 'name' => 'Admin Demo', 'email' => 'admin@demo.com' ],
                        'ipAddress' => '192.168.1.10',
                        'device' => 'Safari/macOS',
                        'startTime' => now()->subHours(5)->toISOString(),
                        'lastActivity' => now()->subMinutes(7)->toISOString(),
                    ],
                ]
            ]);
        });
        Route::post('/sessions/{id}/terminate', function ($id) {
            return response()->json(['data' => ['terminated' => (string)$id, 'timestamp' => now()->toISOString()]]);
        });

        // Login mode (password or ldap) - persistent via settings table
        Route::get('/login-mode', function () {
            $mode = \App\Models\Setting::getValue('auth.login_mode', 'password');
            $mode = in_array($mode, ['password','ldap'], true) ? $mode : 'password';
            return response()->json(['data' => ['mode' => $mode]]);
        });
        Route::put('/login-mode', function (Request $request) {
            $mode = (string) $request->input('mode', 'password');
            if (!in_array($mode, ['password','ldap'], true)) {
                return response()->json(['message' => 'Modo inválido. Use password ou ldap'], 422);
            }
            \App\Models\Setting::putValue('auth.login_mode', $mode);
            return response()->json(['data' => ['mode' => $mode]]);
        });

        // Security settings (normalized for FE)
        Route::get('/settings', function () {
            return response()->json([
                'data' => [
                    'passwordPolicy' => [
                        'minLength' => 8,
                        'requireUppercase' => true,
                        'requireNumbers' => true,
                        'requireSymbols' => true,
                    ],
                    'sessionManagement' => [
                        'timeout' => 30,
                        'maxSessions' => 3,
                        'autoLogout' => true,
                    ],
                    'mfa' => [
                        'required' => false,
                        'allowedMethods' => ['sms','app'],
                    ],
                    'accountLockout' => [
                        'maxAttempts' => 5,
                        'lockoutDuration' => 15,
                    ],
                ]
            ]);
        });
        Route::put('/settings', function (Request $request) {
            $data = $request->all();
            $errors = [];

            $pp = $data['passwordPolicy'] ?? [];
            if (isset($pp['minLength']) && (!is_int($pp['minLength']) || $pp['minLength'] < 6)) {
                $errors['passwordPolicy']['minLength'] = 'O comprimento mínimo deve ser >= 6';
            }

            $sm = $data['sessionManagement'] ?? [];
            if (isset($sm['timeout']) && (!is_int($sm['timeout']) || $sm['timeout'] < 5)) {
                $errors['sessionManagement']['timeout'] = 'Timeout deve ser >= 5 minutos';
            }
            if (isset($sm['maxSessions']) && (!is_int($sm['maxSessions']) || $sm['maxSessions'] < 1)) {
                $errors['sessionManagement']['maxSessions'] = 'Máx. sessões deve ser >= 1';
            }

            $mfa = $data['mfa'] ?? [];
            if (isset($mfa['allowedMethods']) && !is_array($mfa['allowedMethods'])) {
                $errors['mfa']['allowedMethods'] = 'Deve ser uma lista de métodos';
            }

            $al = $data['accountLockout'] ?? [];
            if (isset($al['maxAttempts']) && (!is_int($al['maxAttempts']) || $al['maxAttempts'] < 3)) {
                $errors['accountLockout']['maxAttempts'] = 'Limite deve ser >= 3';
            }
            if (isset($al['lockoutDuration']) && (!is_int($al['lockoutDuration']) || $al['lockoutDuration'] < 5)) {
                $errors['accountLockout']['lockoutDuration'] = 'Duração deve ser >= 5 minutos';
            }

            if (!empty($errors)) {
                return response()->json([
                    'message' => 'Validação falhou',
                    'errors' => $errors,
                ], 422);
            }

            // Return normalized data object under data
            return response()->json(['data' => [
                'passwordPolicy' => [
                    'minLength' => (int)($pp['minLength'] ?? 8),
                    'requireUppercase' => (bool)($pp['requireUppercase'] ?? true),
                    'requireNumbers' => (bool)($pp['requireNumbers'] ?? true),
                    'requireSymbols' => (bool)($pp['requireSymbols'] ?? true),
                ],
                'sessionManagement' => [
                    'timeout' => (int)($sm['timeout'] ?? 30),
                    'maxSessions' => (int)($sm['maxSessions'] ?? 3),
                    'autoLogout' => (bool)($sm['autoLogout'] ?? true),
                ],
                'mfa' => [
                    'required' => (bool)($mfa['required'] ?? false),
                    'allowedMethods' => array_values($mfa['allowedMethods'] ?? []),
                ],
                'accountLockout' => [
                    'maxAttempts' => (int)($al['maxAttempts'] ?? 5),
                    'lockoutDuration' => (int)($al['lockoutDuration'] ?? 15),
                ],
            ]]);
        });
    });
});

// Users (protected)
Route::middleware('auth:sanctum')->prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index'])->middleware('can:users.view');
    Route::get('/{user}', [UserController::class, 'show'])->middleware('can:users.view');
    Route::post('/', [UserController::class, 'store'])->middleware('can:users.manage');
    Route::put('/{user}', [UserController::class, 'update'])->middleware('can:users.manage');
    Route::delete('/{user}', [UserController::class, 'destroy'])->middleware('can:users.manage');
    Route::post('/{user}/reset-password', [UserController::class, 'resetPassword'])->middleware('can:users.manage');
    Route::post('/{user}/enable-mfa', [UserController::class, 'enableMfa'])->middleware('can:users.manage');
    Route::post('/{user}/disable-mfa', [UserController::class, 'disableMfa'])->middleware('can:users.manage');
    Route::post('/bulk', [UserController::class, 'bulk'])->middleware('can:users.manage');
    Route::post('/import', [UserController::class, 'import'])->middleware('can:users.manage');
    Route::get('/export', [UserController::class, 'export'])->middleware('can:users.view');
});

// Roles (protected)
Route::middleware('auth:sanctum')->prefix('roles')->group(function () {
    Route::get('/', [RoleController::class, 'index'])->middleware('can:users.manage');
    Route::get('/options', [RoleController::class, 'options']);
    Route::get('/{role}', [RoleController::class, 'show'])->middleware('can:users.manage');
    Route::post('/', [RoleController::class, 'store'])->middleware('can:users.manage');
    Route::put('/{role}', [RoleController::class, 'update'])->middleware('can:users.manage');
    Route::delete('/{role}', [RoleController::class, 'destroy'])->middleware('can:users.manage');
    Route::post('/{role}/toggle-status', [RoleController::class, 'toggleStatus'])->middleware('can:users.manage');
});

// Departments (protected)
Route::middleware('auth:sanctum')->prefix('departments')->group(function () {
    Route::get('/', [DepartmentController::class, 'index'])->middleware('can:users.manage');
    Route::get('/options', [DepartmentController::class, 'options']);
    Route::get('/potential-managers', [DepartmentController::class, 'potentialManagers'])->middleware('can:users.manage');
    Route::get('/{department}', [DepartmentController::class, 'show'])->middleware('can:users.manage');
    Route::post('/', [DepartmentController::class, 'store'])->middleware('can:users.manage');
    Route::put('/{department}', [DepartmentController::class, 'update'])->middleware('can:users.manage');
    Route::delete('/{department}', [DepartmentController::class, 'destroy'])->middleware('can:users.manage');
    Route::post('/{department}/toggle-status', [DepartmentController::class, 'toggleStatus'])->middleware('can:users.manage');
});

// Job Positions (protected)
Route::middleware('auth:sanctum')->prefix('job-positions')->group(function () {
    Route::get('/', [JobPositionController::class, 'index'])->middleware('can:users.manage');
    Route::get('/options', [JobPositionController::class, 'options']);
    Route::get('/by-department/{department}', [JobPositionController::class, 'byDepartment'])->middleware('can:users.manage');
    Route::get('/salary-ranges', [JobPositionController::class, 'salaryRanges'])->middleware('can:users.manage');
    Route::get('/{jobPosition}', [JobPositionController::class, 'show'])->middleware('can:users.manage');
    Route::post('/', [JobPositionController::class, 'store'])->middleware('can:users.manage');
    Route::put('/{jobPosition}', [JobPositionController::class, 'update'])->middleware('can:users.manage');
    Route::delete('/{jobPosition}', [JobPositionController::class, 'destroy'])->middleware('can:users.manage');
    Route::post('/{jobPosition}/toggle-status', [JobPositionController::class, 'toggleStatus'])->middleware('can:users.manage');
});

// Organizational Structure (protected)
Route::middleware('auth:sanctum')->prefix('organizational-structure')->group(function () {
    Route::get('/', [OrganizationalStructureController::class, 'index'])->middleware('can:users.manage');
    Route::get('/by-user/{user}', [OrganizationalStructureController::class, 'byUser'])->middleware('can:users.manage');
    Route::get('/by-department/{department}', [OrganizationalStructureController::class, 'byDepartment'])->middleware('can:users.manage');
    Route::get('/current-by-user/{user}', [OrganizationalStructureController::class, 'currentByUser'])->middleware('can:users.manage');
    Route::get('/organizational-chart', [OrganizationalStructureController::class, 'organizationalChart'])->middleware('can:users.view');
    Route::get('/team-members/{manager}', [OrganizationalStructureController::class, 'teamMembers'])->middleware('can:users.view');
    Route::get('/{organizationalStructure}', [OrganizationalStructureController::class, 'show'])->middleware('can:users.manage');
    Route::post('/', [OrganizationalStructureController::class, 'store'])->middleware('can:users.manage');
    Route::put('/{organizationalStructure}', [OrganizationalStructureController::class, 'update'])->middleware('can:users.manage');
    Route::delete('/{organizationalStructure}', [OrganizationalStructureController::class, 'destroy'])->middleware('can:users.manage');
    Route::post('/{organizationalStructure}/toggle-status', [OrganizationalStructureController::class, 'toggleStatus'])->middleware('can:users.manage');
});

// External Integrations (protected)
Route::middleware('auth:sanctum')->prefix('external-integrations')->group(function () {
    Route::get('/', [ExternalIntegrationController::class, 'index'])->middleware('can:users.manage');
    Route::get('/needs-sync', [ExternalIntegrationController::class, 'needsSync'])->middleware('can:users.manage');
    Route::get('/{externalIntegration}', [ExternalIntegrationController::class, 'show'])->middleware('can:users.manage');
    Route::get('/{externalIntegration}/statistics', [ExternalIntegrationController::class, 'statistics'])->middleware('can:users.manage');
    Route::post('/', [ExternalIntegrationController::class, 'store'])->middleware('can:users.manage');
    Route::put('/{externalIntegration}', [ExternalIntegrationController::class, 'update'])->middleware('can:users.manage');
    Route::delete('/{externalIntegration}', [ExternalIntegrationController::class, 'destroy'])->middleware('can:users.manage');
    Route::post('/{externalIntegration}/toggle-status', [ExternalIntegrationController::class, 'toggleStatus'])->middleware('can:users.manage');
    Route::post('/{externalIntegration}/test-connection', [ExternalIntegrationController::class, 'testConnection'])->middleware('can:users.manage');
    Route::post('/{externalIntegration}/trigger-sync', [ExternalIntegrationController::class, 'triggerSync'])->middleware('can:users.manage');
});

// Sync Logs (protected)
Route::middleware('auth:sanctum')->prefix('sync-logs')->group(function () {
    Route::get('/', [SyncLogController::class, 'index'])->middleware('can:users.manage');
    Route::get('/recent', [SyncLogController::class, 'recent'])->middleware('can:users.view');
    Route::get('/statistics', [SyncLogController::class, 'statistics'])->middleware('can:users.manage');
    Route::get('/by-integration/{integration}', [SyncLogController::class, 'byIntegration'])->middleware('can:users.manage');
    Route::get('/{syncLog}', [SyncLogController::class, 'show'])->middleware('can:users.manage');
    Route::post('/', [SyncLogController::class, 'store'])->middleware('can:users.manage');
    Route::put('/{syncLog}', [SyncLogController::class, 'update'])->middleware('can:users.manage');
    Route::delete('/{syncLog}', [SyncLogController::class, 'destroy'])->middleware('can:users.manage');
    Route::post('/{syncLog}/cancel', [SyncLogController::class, 'cancel'])->middleware('can:users.manage');
    Route::post('/{syncLog}/retry', [SyncLogController::class, 'retry'])->middleware('can:users.manage');
});

// Onboarding Roadmaps (protected)
Route::middleware('auth:sanctum')->prefix('onboarding-roadmaps')->group(function () {
    Route::get('/', [OnboardingRoadmapController::class, 'index'])->middleware('can:users.view');
    Route::post('/', [OnboardingRoadmapController::class, 'store'])->middleware('can:users.manage');
    Route::get('/departments', [OnboardingRoadmapController::class, 'departments'])->middleware('can:users.view');
    Route::get('/{roadmap}', [OnboardingRoadmapController::class, 'show'])->middleware('can:users.view');
    Route::put('/{roadmap}', [OnboardingRoadmapController::class, 'update'])->middleware('can:users.manage');
    Route::delete('/{roadmap}', [OnboardingRoadmapController::class, 'destroy'])->middleware('can:users.manage');
    Route::post('/{roadmap}/duplicate', [OnboardingRoadmapController::class, 'duplicate'])->middleware('can:users.manage');
});

// Documents (protected)
Route::middleware('auth:sanctum')->prefix('documents')->group(function () {
    Route::get('/', [DocumentController::class, 'index'])->middleware('can:users.view');
    Route::post('/', [DocumentController::class, 'store'])->middleware('can:users.manage');
    Route::get('/types', [DocumentController::class, 'types'])->middleware('can:users.view');
    Route::get('/departments', [DocumentController::class, 'departments'])->middleware('can:users.view');
    Route::get('/{document}', [DocumentController::class, 'show'])->middleware('can:users.view');
    Route::put('/{document}', [DocumentController::class, 'update'])->middleware('can:users.manage');
    Route::delete('/{document}', [DocumentController::class, 'destroy'])->middleware('can:users.manage');
    Route::get('/{document}/download', [DocumentController::class, 'download'])->middleware('can:users.view');
    Route::post('/{document}/replace-file', [DocumentController::class, 'replaceFile'])->middleware('can:users.manage');
});

// Document Signatures (protected)
Route::middleware('auth:sanctum')->prefix('document-signatures')->group(function () {
    Route::get('/', [DocumentSignatureController::class, 'index']);
    Route::get('/pending', [DocumentSignatureController::class, 'pending']);
    Route::get('/statistics', [DocumentSignatureController::class, 'statistics'])->middleware('can:users.manage');
    Route::post('/documents/{document}/sign', [DocumentSignatureController::class, 'sign']);
    Route::post('/documents/{document}/reject', [DocumentSignatureController::class, 'reject']);
    Route::get('/documents/{document}/status', [DocumentSignatureController::class, 'status']);
    Route::get('/documents/{document}/signatures', [DocumentSignatureController::class, 'documentSignatures'])->middleware('can:users.manage');
    Route::get('/{signature}', [DocumentSignatureController::class, 'show']);
});

// User Onboarding Progress (protected)
Route::middleware('auth:sanctum')->prefix('onboarding-progress')->group(function () {
    Route::get('/', [UserOnboardingProgressController::class, 'index'])->middleware('can:users.view');
    Route::post('/assign', [UserOnboardingProgressController::class, 'assign'])->middleware('can:users.manage');
    Route::post('/bulk-assign', [UserOnboardingProgressController::class, 'bulkAssign'])->middleware('can:users.manage');
    Route::get('/my-progress', [UserOnboardingProgressController::class, 'myProgress']);
    Route::get('/statistics', [UserOnboardingProgressController::class, 'statistics'])->middleware('can:users.manage');
    Route::get('/{progress}', [UserOnboardingProgressController::class, 'show']);
    Route::post('/{progress}/start', [UserOnboardingProgressController::class, 'start']);
    Route::post('/{progress}/complete-step', [UserOnboardingProgressController::class, 'completeStep']);
    Route::post('/{progress}/uncomplete-step', [UserOnboardingProgressController::class, 'uncompleteStep']);
    Route::put('/{progress}/notes', [UserOnboardingProgressController::class, 'updateNotes'])->middleware('can:users.manage');
    Route::delete('/{progress}', [UserOnboardingProgressController::class, 'destroy'])->middleware('can:users.manage');
});

// Security: IP policies and locks (protect with auth in production)
Route::prefix('security')->middleware((function () {
    // Always enforce the IP policy; add auth in production
    $middlewares = ['ip.policy'];
    if (app()->environment('production')) {
        $middlewares[] = 'auth:sanctum';
    }
    return $middlewares;
})())->group(function () {
    // IP Policies
    Route::get('/ip-policies', [SecurityIpController::class, 'indexPolicies']);
    Route::post('/ip-policies', [SecurityIpController::class, 'storePolicy']);
    Route::delete('/ip-policies/{policy}', [SecurityIpController::class, 'destroyPolicy']);

    // IP Locks
    Route::get('/ip-locks', [SecurityIpController::class, 'indexLocks']);
    Route::post('/ip-locks/unlock', [SecurityIpController::class, 'unlock']);
});

// ------------------------------------------------------------
// Simulados (protected routes with authentication and rate limiting)
// ------------------------------------------------------------
Route::middleware(['auth:sanctum', 'throttle:60,1'])->prefix('simulados')->group(function () {
    // In-memory simulados catalog (static for now)
    $getCatalog = function () {
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
    };

    Route::get('/', function () use ($getCatalog) {
        return response()->json($getCatalog());
    });

    Route::get('/{simulado}', function ($simulado) use ($getCatalog) {
        $simulado = (int)$simulado;
        foreach ($getCatalog() as $s) {
            if ($s['id'] === $simulado) {
                return response()->json($s);
            }
        }
        return response()->json(['message' => 'Simulado não encontrado'], 404);
    });

    // List attempts for a given simulado
    Route::get('/{simulado}/attempts', [SimuladoAttemptController::class, 'listBySimulado']);

    // Start or resume an attempt
    Route::post('/{simulado}/attempts', [SimuladoAttemptController::class, 'startOrResume']);
});

// Attempts CRUD outside group for direct access by attemptId (protected)
Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {
    Route::get('/attempts/{attempt}', [SimuladoAttemptController::class, 'show']);
    Route::patch('/attempts/{attempt}', [SimuladoAttemptController::class, 'updatePartial']);
    Route::post('/attempts/{attempt}/submit', [SimuladoAttemptController::class, 'submit']);
    Route::get('/attempts/{attempt}/result', [SimuladoAttemptController::class, 'getResult']);
});

// Certificates (protected)
Route::middleware(['auth:sanctum', 'throttle:30,1'])->group(function () {
    Route::post('/certificates', [SimuladoCertificateController::class, 'issue']);
});

// Certificate verification (public but rate limited)
Route::middleware('throttle:10,1')->group(function () {
    Route::get('/certificates/verify', [SimuladoCertificateController::class, 'verify']);
});

// ------------------------------------------------------------
// Notifications API
// ------------------------------------------------------------
Route::middleware('auth:sanctum')->prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
    Route::get('/stats', [NotificationController::class, 'stats']);
    Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::patch('/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::delete('/{id}', [NotificationController::class, 'destroy']);
});

// Notification Preferences API
// ------------------------------------------------------------
Route::middleware('auth:sanctum')->prefix('notification-preferences')->group(function () {
    Route::get('/', [NotificationPreferencesController::class, 'index']);
    Route::put('/', [NotificationPreferencesController::class, 'update']);
    Route::post('/reset', [NotificationPreferencesController::class, 'reset']);
    Route::get('/settings', [NotificationPreferencesController::class, 'settings']);
});