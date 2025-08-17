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

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Laravel backend is running',
        'timestamp' => now()->toISOString()
    ]);
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

    // Development stubs for frontend integration
    Route::middleware('auth:sanctum')->group(function () {
        // Stats
        Route::get('/stats', function () {
            return response()->json([
                'totalUsers' => 42,
                'activeSessions' => 3,
                'mfaEnabledUsers' => 8,
                'lastLogin' => now()->toISOString(),
            ]);
        });

        // Authentication methods
        Route::get('/methods', function () {
            return response()->json([
                ['id' => 1, 'name' => 'Email/Senha', 'key' => 'password', 'enabled' => true],
                ['id' => 2, 'name' => 'Google SSO', 'key' => 'google', 'enabled' => false],
                ['id' => 3, 'name' => 'Microsoft SSO', 'key' => 'microsoft', 'enabled' => false],
            ]);
        });
        Route::post('/methods', function (Request $request) {
            return response()->json(['id' => rand(100, 999), ...$request->all()]);
        });
        Route::patch('/methods/{id}', function ($id, Request $request) {
            return response()->json(['id' => (int)$id, 'enabled' => (bool)$request->input('enabled', true)]);
        });

        // Sessions
        Route::get('/sessions', function () {
            return response()->json([
                ['id' => 'sess_1', 'userId' => 1, 'ip' => '127.0.0.1', 'agent' => 'Chrome', 'lastActivity' => now()->toISOString()],
                ['id' => 'sess_2', 'userId' => 1, 'ip' => '192.168.1.10', 'agent' => 'Safari', 'lastActivity' => now()->subMinutes(5)->toISOString()],
            ]);
        });
        Route::post('/sessions/{id}/terminate', function ($id) {
            return response()->json(['terminated' => (string)$id]);
        });

        // Security settings
        Route::get('/settings', function () {
            return response()->json([
                'passwordPolicy' => [
                    'minLength' => 8,
                    'requireUppercase' => true,
                    'requireLowercase' => true,
                    'requireNumbers' => true,
                    'requireSpecial' => false,
                    'passwordExpiryDays' => 90,
                ],
                'sessionManagement' => [
                    'sessionTimeoutMinutes' => 30,
                    'maxConcurrentSessions' => 3,
                    'rememberMeDays' => 7,
                    'invalidateOnPasswordChange' => true,
                ],
                'mfa' => [
                    'enabled' => false,
                    'requiredForAdmins' => true,
                    'methods' => ['totp'],
                ],
                'accountLockout' => [
                    'enabled' => true,
                    'threshold' => 5,
                    'lockoutMinutes' => 15,
                ],
            ]);
        });
        Route::put('/settings', function (Request $request) {
            // Basic, lenient validation example
            $data = $request->all();
            $errors = [];
            $pp = $data['passwordPolicy'] ?? [];
            if (isset($pp['minLength']) && (!is_int($pp['minLength']) || $pp['minLength'] < 6)) {
                $errors['passwordPolicy']['minLength'] = 'O comprimento mínimo deve ser >= 6';
            }
            $sm = $data['sessionManagement'] ?? [];
            if (isset($sm['sessionTimeoutMinutes']) && (!is_int($sm['sessionTimeoutMinutes']) || $sm['sessionTimeoutMinutes'] < 5)) {
                $errors['sessionManagement']['sessionTimeoutMinutes'] = 'Timeout deve ser >= 5 minutos';
            }
            $al = $data['accountLockout'] ?? [];
            if (isset($al['threshold']) && (!is_int($al['threshold']) || $al['threshold'] < 3)) {
                $errors['accountLockout']['threshold'] = 'Limite deve ser >= 3';
            }

            if (!empty($errors)) {
                return response()->json([
                    'message' => 'Validação falhou',
                    'errors' => $errors,
                ], 422);
            }

            // Echo back what was "saved"
            return response()->json($data);
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

// Security: IP policies and locks (consider also protecting with auth in production)
Route::prefix('security')->middleware('ip.policy')->group(function () {
    // IP Policies
    Route::get('/ip-policies', [SecurityIpController::class, 'indexPolicies']);
    Route::post('/ip-policies', [SecurityIpController::class, 'storePolicy']);
    Route::delete('/ip-policies/{policy}', [SecurityIpController::class, 'destroyPolicy']);

    // IP Locks
    Route::get('/ip-locks', [SecurityIpController::class, 'indexLocks']);
    Route::post('/ip-locks/unlock', [SecurityIpController::class, 'unlock']);
});