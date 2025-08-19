<?php

namespace App\Http\Controllers;

use App\Models\SyncLog;
use App\Models\ExternalIntegration;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class SyncLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SyncLog::query();

        // Filter by integration
        if ($request->has('integration_id')) {
            $query->byIntegration($request->get('integration_id'));
        }

        // Filter by status
        if ($request->has('status')) {
            $query->byStatus($request->get('status'));
        }

        // Filter by sync type
        if ($request->has('sync_type')) {
            $query->bySyncType($request->get('sync_type'));
        }

        // Filter by direction
        if ($request->has('direction')) {
            $query->where('direction', $request->get('direction'));
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->dateRange($request->get('start_date'), $request->get('end_date'));
        }

        // Filter only successful logs
        if ($request->boolean('successful_only')) {
            $query->successful();
        }

        // Filter only failed logs
        if ($request->boolean('failed_only')) {
            $query->failed();
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('sync_type', 'like', "%{$search}%")
                  ->orWhere('error_message', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('externalIntegration', function ($subQuery) use ($search) {
                      $subQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('provider', 'like', "%{$search}%");
                  });
            });
        }

        // Load relationships
        $query->with(['externalIntegration:id,name,provider,type', 'user:id,name,email']);

        // Order by latest first
        $query->latest();

        // Pagination
        $perPage = $request->get('per_page', 15);
        $logs = $query->paginate($perPage);

        return response()->json($logs);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'external_integration_id' => 'required|exists:external_integrations,id',
            'sync_type' => 'required|string|max:100',
            'direction' => 'required|in:inbound,outbound,bidirectional',
            'status' => 'required|in:pending,running,completed,failed,cancelled',
            'started_at' => 'nullable|date',
            'completed_at' => 'nullable|date|after_or_equal:started_at',
            'duration' => 'nullable|integer|min:0',
            'records_processed' => 'nullable|integer|min:0',
            'records_created' => 'nullable|integer|min:0',
            'records_updated' => 'nullable|integer|min:0',
            'records_deleted' => 'nullable|integer|min:0',
            'records_failed' => 'nullable|integer|min:0',
            'summary' => 'nullable|array',
            'errors' => 'nullable|array',
            'warnings' => 'nullable|array',
            'error_message' => 'nullable|string',
            'metadata' => 'nullable|array',
            'triggered_by' => 'required|string|max:100',
            'user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string'
        ]);

        // Set default values
        $validated['started_at'] = $validated['started_at'] ?? Carbon::now();
        $validated['user_id'] = $validated['user_id'] ?? auth()->id();

        $syncLog = SyncLog::create($validated);
        $syncLog->load(['externalIntegration:id,name,provider,type', 'user:id,name,email']);

        return response()->json([
            'message' => 'Log de sincronização criado com sucesso.',
            'data' => $syncLog
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(SyncLog $syncLog): JsonResponse
    {
        $syncLog->load([
            'externalIntegration:id,name,provider,type,description',
            'user:id,name,email'
        ]);

        return response()->json($syncLog);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SyncLog $syncLog): JsonResponse
    {
        // Only allow updating certain fields for running syncs
        $allowedFields = ['status', 'completed_at', 'duration', 'records_processed', 
                         'records_created', 'records_updated', 'records_deleted', 
                         'records_failed', 'summary', 'errors', 'warnings', 
                         'error_message', 'metadata', 'notes'];

        $validated = $request->validate([
            'status' => 'sometimes|in:pending,running,completed,failed,cancelled',
            'completed_at' => 'nullable|date|after_or_equal:started_at',
            'duration' => 'nullable|integer|min:0',
            'records_processed' => 'nullable|integer|min:0',
            'records_created' => 'nullable|integer|min:0',
            'records_updated' => 'nullable|integer|min:0',
            'records_deleted' => 'nullable|integer|min:0',
            'records_failed' => 'nullable|integer|min:0',
            'summary' => 'nullable|array',
            'errors' => 'nullable|array',
            'warnings' => 'nullable|array',
            'error_message' => 'nullable|string',
            'metadata' => 'nullable|array',
            'notes' => 'nullable|string'
        ]);

        // Auto-set completed_at if status is completed or failed
        if (isset($validated['status']) && in_array($validated['status'], ['completed', 'failed'])) {
            $validated['completed_at'] = $validated['completed_at'] ?? Carbon::now();
        }

        // Calculate duration if not provided
        if (isset($validated['completed_at']) && !isset($validated['duration'])) {
            $startedAt = Carbon::parse($syncLog->started_at);
            $completedAt = Carbon::parse($validated['completed_at']);
            $validated['duration'] = $completedAt->diffInSeconds($startedAt);
        }

        $syncLog->update(array_intersect_key($validated, array_flip($allowedFields)));
        $syncLog->load(['externalIntegration:id,name,provider,type', 'user:id,name,email']);

        return response()->json([
            'message' => 'Log de sincronização atualizado com sucesso.',
            'data' => $syncLog
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SyncLog $syncLog): JsonResponse
    {
        // Don't allow deletion of running syncs
        if ($syncLog->isRunning()) {
            return response()->json([
                'message' => 'Não é possível excluir um log de sincronização em execução.'
            ], 422);
        }

        $syncLog->delete();

        return response()->json([
            'message' => 'Log de sincronização excluído com sucesso.'
        ]);
    }

    /**
     * Get sync statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $query = SyncLog::query();

        // Filter by integration if specified
        if ($request->has('integration_id')) {
            $query->byIntegration($request->get('integration_id'));
        }

        // Filter by date range if specified
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->dateRange($request->get('start_date'), $request->get('end_date'));
        } else {
            // Default to last 30 days
            $query->dateRange(Carbon::now()->subDays(30), Carbon::now());
        }

        $stats = [
            'total_syncs' => $query->count(),
            'successful_syncs' => (clone $query)->successful()->count(),
            'failed_syncs' => (clone $query)->failed()->count(),
            'running_syncs' => (clone $query)->where('status', 'running')->count(),
            'average_duration' => (clone $query)->whereNotNull('duration')->avg('duration'),
            'total_records_processed' => (clone $query)->sum('records_processed'),
            'total_records_created' => (clone $query)->sum('records_created'),
            'total_records_updated' => (clone $query)->sum('records_updated'),
            'total_records_deleted' => (clone $query)->sum('records_deleted'),
            'total_records_failed' => (clone $query)->sum('records_failed'),
            'by_status' => (clone $query)->selectRaw('status, COUNT(*) as count')
                                        ->groupBy('status')
                                        ->pluck('count', 'status'),
            'by_sync_type' => (clone $query)->selectRaw('sync_type, COUNT(*) as count')
                                            ->groupBy('sync_type')
                                            ->pluck('count', 'sync_type'),
            'by_integration' => (clone $query)->with('externalIntegration:id,name')
                                              ->get()
                                              ->groupBy('external_integration_id')
                                              ->map(function ($logs) {
                                                  return [
                                                      'name' => $logs->first()->externalIntegration->name ?? 'Unknown',
                                                      'count' => $logs->count(),
                                                      'successful' => $logs->where('status', 'completed')->count(),
                                                      'failed' => $logs->where('status', 'failed')->count()
                                                  ];
                                              })
        ];

        return response()->json($stats);
    }

    /**
     * Get recent sync logs for dashboard
     */
    public function recent(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        
        $logs = SyncLog::with(['externalIntegration:id,name,provider', 'user:id,name'])
                       ->latest()
                       ->limit($limit)
                       ->get();

        return response()->json($logs);
    }

    /**
     * Get sync logs by integration
     */
    public function byIntegration(ExternalIntegration $integration, Request $request): JsonResponse
    {
        $query = $integration->syncLogs();

        // Filter by status if specified
        if ($request->has('status')) {
            $query->byStatus($request->get('status'));
        }

        // Filter by date range if specified
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->dateRange($request->get('start_date'), $request->get('end_date'));
        }

        $query->with('user:id,name,email')->latest();

        $perPage = $request->get('per_page', 15);
        $logs = $query->paginate($perPage);

        return response()->json($logs);
    }

    /**
     * Cancel a running sync
     */
    public function cancel(SyncLog $syncLog): JsonResponse
    {
        if (!$syncLog->isRunning()) {
            return response()->json([
                'message' => 'Esta sincronização não está em execução e não pode ser cancelada.'
            ], 422);
        }

        $syncLog->update([
            'status' => 'cancelled',
            'completed_at' => Carbon::now(),
            'error_message' => 'Sincronização cancelada pelo usuário.',
            'notes' => ($syncLog->notes ?? '') . "\n" . 'Cancelada em ' . Carbon::now()->format('d/m/Y H:i:s')
        ]);

        return response()->json([
            'message' => 'Sincronização cancelada com sucesso.',
            'data' => $syncLog
        ]);
    }

    /**
     * Retry a failed sync
     */
    public function retry(SyncLog $syncLog): JsonResponse
    {
        if (!$syncLog->isFailed()) {
            return response()->json([
                'message' => 'Apenas sincronizações com falha podem ser reexecutadas.'
            ], 422);
        }

        // Create a new sync log based on the failed one
        $newSyncLog = $syncLog->externalIntegration->syncLogs()->create([
            'sync_type' => $syncLog->sync_type,
            'direction' => $syncLog->direction,
            'status' => 'pending',
            'started_at' => Carbon::now(),
            'triggered_by' => 'retry',
            'user_id' => auth()->id(),
            'notes' => 'Reexecução do log #' . $syncLog->id
        ]);

        $newSyncLog->load(['externalIntegration:id,name,provider,type', 'user:id,name,email']);

        return response()->json([
            'message' => 'Nova sincronização iniciada com sucesso.',
            'data' => $newSyncLog
        ], 201);
    }
}
