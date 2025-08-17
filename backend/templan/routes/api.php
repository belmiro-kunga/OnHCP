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