<?php

namespace App\Http\Controllers;

use App\Models\ExternalIntegration;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ExternalIntegrationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ExternalIntegration::query();

        // Filter by active status if requested
        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->get('type'));
        }

        // Filter by provider
        if ($request->has('provider')) {
            $query->where('provider', $request->get('provider'));
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter integrations that need sync
        if ($request->boolean('needs_sync')) {
            $query->needsSync();
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('provider', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Load relationships and counts
        $query->withCount(['syncLogs', 'latestSyncLogs']);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $integrations = $query->paginate($perPage);

        return response()->json($integrations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:external_integrations,name',
            'type' => 'required|string|max:100',
            'provider' => 'required|string|max:100',
            'description' => 'nullable|string',
            'configuration' => 'nullable|array',
            'credentials' => 'nullable|array',
            'status' => 'required|in:active,inactive,error,testing',
            'sync_frequency' => 'nullable|string|max:50',
            'sync_settings' => 'nullable|array',
            'field_mappings' => 'nullable|array',
            'filters' => 'nullable|array',
            'auto_sync' => 'boolean',
            'bidirectional' => 'boolean',
            'timeout' => 'nullable|integer|min:1|max:3600',
            'retry_attempts' => 'nullable|integer|min:0|max:10',
            'error_handling' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'active' => 'boolean'
        ]);

        // Set next sync time if auto_sync is enabled
        if ($validated['auto_sync'] ?? false) {
            $validated['next_sync_at'] = $this->calculateNextSyncTime($validated['sync_frequency'] ?? 'daily');
        }

        $integration = ExternalIntegration::create($validated);
        $integration->loadCount(['syncLogs', 'latestSyncLogs']);

        return response()->json([
            'message' => 'Integração externa criada com sucesso.',
            'data' => $integration
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ExternalIntegration $externalIntegration): JsonResponse
    {
        $externalIntegration->load([
            'syncLogs' => function ($query) {
                $query->latest()->limit(10);
            }
        ])->loadCount(['syncLogs', 'latestSyncLogs']);

        return response()->json($externalIntegration);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExternalIntegration $externalIntegration): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('external_integrations')->ignore($externalIntegration->id)],
            'type' => 'required|string|max:100',
            'provider' => 'required|string|max:100',
            'description' => 'nullable|string',
            'configuration' => 'nullable|array',
            'credentials' => 'nullable|array',
            'status' => 'required|in:active,inactive,error,testing',
            'sync_frequency' => 'nullable|string|max:50',
            'sync_settings' => 'nullable|array',
            'field_mappings' => 'nullable|array',
            'filters' => 'nullable|array',
            'auto_sync' => 'boolean',
            'bidirectional' => 'boolean',
            'timeout' => 'nullable|integer|min:1|max:3600',
            'retry_attempts' => 'nullable|integer|min:0|max:10',
            'error_handling' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'active' => 'boolean'
        ]);

        // Update next sync time if auto_sync settings changed
        if (isset($validated['auto_sync']) || isset($validated['sync_frequency'])) {
            if ($validated['auto_sync'] ?? $externalIntegration->auto_sync) {
                $validated['next_sync_at'] = $this->calculateNextSyncTime(
                    $validated['sync_frequency'] ?? $externalIntegration->sync_frequency ?? 'daily'
                );
            } else {
                $validated['next_sync_at'] = null;
            }
        }

        $externalIntegration->update($validated);
        $externalIntegration->loadCount(['syncLogs', 'latestSyncLogs']);

        return response()->json([
            'message' => 'Integração externa atualizada com sucesso.',
            'data' => $externalIntegration
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExternalIntegration $externalIntegration): JsonResponse
    {
        // Check if integration has recent sync logs
        $recentLogs = $externalIntegration->syncLogs()
                                          ->where('created_at', '>=', Carbon::now()->subDays(30))
                                          ->count();

        if ($recentLogs > 0) {
            return response()->json([
                'message' => 'Não é possível excluir esta integração pois existem logs de sincronização recentes. Desative a integração em vez de excluí-la.'
            ], 422);
        }

        $externalIntegration->delete();

        return response()->json([
            'message' => 'Integração externa excluída com sucesso.'
        ]);
    }

    /**
     * Test connection for an integration
     */
    public function testConnection(ExternalIntegration $externalIntegration): JsonResponse
    {
        // This would typically call the actual integration service
        // For now, we'll simulate a test
        
        $externalIntegration->update([
            'status' => 'testing',
            'last_sync_at' => Carbon::now()
        ]);

        // Simulate test result (in real implementation, this would test actual connection)
        $testResult = [
            'success' => true,
            'message' => 'Conexão testada com sucesso.',
            'response_time' => rand(100, 500) . 'ms',
            'tested_at' => Carbon::now()->toISOString()
        ];

        if ($testResult['success']) {
            $externalIntegration->update(['status' => 'active']);
        } else {
            $externalIntegration->update(['status' => 'error']);
        }

        return response()->json([
            'message' => $testResult['message'],
            'data' => $testResult
        ]);
    }

    /**
     * Trigger manual sync for an integration
     */
    public function triggerSync(ExternalIntegration $externalIntegration): JsonResponse
    {
        if (!$externalIntegration->isReadyForSync()) {
            return response()->json([
                'message' => 'Esta integração não está pronta para sincronização. Verifique o status e configurações.'
            ], 422);
        }

        // This would typically trigger the actual sync job
        // For now, we'll simulate starting a sync
        
        $externalIntegration->syncLogs()->create([
            'sync_type' => 'manual',
            'direction' => $externalIntegration->bidirectional ? 'bidirectional' : 'inbound',
            'status' => 'running',
            'started_at' => Carbon::now(),
            'triggered_by' => 'manual',
            'user_id' => auth()->id()
        ]);

        $externalIntegration->updateNextSyncTime();

        return response()->json([
            'message' => 'Sincronização iniciada com sucesso.',
            'data' => [
                'sync_started_at' => Carbon::now()->toISOString(),
                'next_sync_at' => $externalIntegration->next_sync_at
            ]
        ]);
    }

    /**
     * Get integration statistics
     */
    public function statistics(ExternalIntegration $externalIntegration): JsonResponse
    {
        $stats = [
            'total_syncs' => $externalIntegration->syncLogs()->count(),
            'successful_syncs' => $externalIntegration->syncLogs()->successful()->count(),
            'failed_syncs' => $externalIntegration->syncLogs()->failed()->count(),
            'last_successful_sync' => $externalIntegration->getLastSuccessfulSync(),
            'last_failed_sync' => $externalIntegration->getLastFailedSync(),
            'average_duration' => $externalIntegration->syncLogs()
                                                     ->whereNotNull('duration')
                                                     ->avg('duration'),
            'records_processed_total' => $externalIntegration->syncLogs()
                                                             ->sum('records_processed'),
            'last_30_days' => [
                'total_syncs' => $externalIntegration->syncLogs()
                                                     ->where('created_at', '>=', Carbon::now()->subDays(30))
                                                     ->count(),
                'successful_syncs' => $externalIntegration->syncLogs()
                                                          ->successful()
                                                          ->where('created_at', '>=', Carbon::now()->subDays(30))
                                                          ->count(),
                'failed_syncs' => $externalIntegration->syncLogs()
                                                      ->failed()
                                                      ->where('created_at', '>=', Carbon::now()->subDays(30))
                                                      ->count()
            ]
        ];

        return response()->json($stats);
    }

    /**
     * Toggle integration status
     */
    public function toggleStatus(ExternalIntegration $externalIntegration): JsonResponse
    {
        $externalIntegration->update(['active' => !$externalIntegration->active]);
        $externalIntegration->loadCount(['syncLogs', 'latestSyncLogs']);

        $status = $externalIntegration->active ? 'ativada' : 'desativada';
        
        return response()->json([
            'message' => "Integração {$status} com sucesso.",
            'data' => $externalIntegration
        ]);
    }

    /**
     * Get integrations that need sync
     */
    public function needsSync(): JsonResponse
    {
        $integrations = ExternalIntegration::needsSync()
                                          ->with('latestSyncLogs')
                                          ->get();

        return response()->json($integrations);
    }

    /**
     * Calculate next sync time based on frequency
     */
    private function calculateNextSyncTime(string $frequency): Carbon
    {
        $now = Carbon::now();
        
        return match($frequency) {
            'hourly' => $now->addHour(),
            'daily' => $now->addDay(),
            'weekly' => $now->addWeek(),
            'monthly' => $now->addMonth(),
            default => $now->addDay()
        };
    }
}
