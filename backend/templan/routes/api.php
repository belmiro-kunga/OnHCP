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

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Laravel backend is running',
        'timestamp' => now()->toISOString()
    ]);
});

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

// Reports
Route::prefix('reports')->group(function () {
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