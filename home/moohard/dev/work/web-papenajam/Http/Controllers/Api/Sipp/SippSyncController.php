<?php

namespace App\Http\Controllers\Api\Sipp;

use App\Enums\SyncType;
use App\Http\Controllers\Controller;
use App\Models\Sipp\SyncLog;
use App\Services\Sipp\SippApiClient;
use App\Services\Sipp\SippDataSync;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SippSyncController extends Controller
{
    protected SippDataSync $syncService;

    protected SippApiClient $apiClient;

    public function __construct(?SippDataSync $syncService = null, ?SippApiClient $apiClient = null)
    {
        $this->syncService = $syncService ?? new SippDataSync();
        $this->apiClient = $apiClient ?? new SippApiClient();
    }

    /**
     * Trigger manual sync.
     */
    public function trigger(Request $request): JsonResponse
    {
        try {
            $type = $request->input('type', 'incremental');

            if (!$this->apiClient->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'message' => 'SIPP API is not configured. Please check your configuration.',
                ], 400);
            }

            if (SyncLog::isSyncRunning()) {
                return response()->json([
                    'success' => false,
                    'message' => 'A sync operation is already running.',
                ], 409);
            }

            if ($type === 'full') {
                $result = $this->syncService->fullSync(true);
            } else {
                $result = $this->syncService->incrementalSync();
            }

            return response()->json([
                'success' => true,
                'message' => 'Sync completed successfully',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to trigger sync', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Sync failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get sync status.
     */
    public function status(): JsonResponse
    {
        try {
            $lastSync = SyncLog::getLastSync();
            $lastSuccessfulSync = SyncLog::getLastSuccessfulSync();
            $isRunning = SyncLog::isSyncRunning();
            $isSyncServiceRunning = $this->syncService->isRunning();

            $recentSyncs = SyncLog::recent(7)
                ->limit(10)
                ->get()
                ->map(function ($sync) {
                    return [
                        'id' => $sync->id,
                        'type' => $sync->type->value,
                        'status' => $sync->status->value,
                        'started_at' => $sync->started_at->toIso8601String(),
                        'completed_at' => $sync->completed_at?->toIso8601String(),
                        'duration' => $sync->getDurationFormatted(),
                        'stats' => $sync->stats,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'is_running' => $isRunning || $isSyncServiceRunning,
                    'last_sync' => $lastSync ? [
                        'id' => $lastSync->id,
                        'type' => $lastSync->type->value,
                        'status' => $lastSync->status->value,
                        'started_at' => $lastSync->started_at->toIso8601String(),
                        'completed_at' => $lastSync->completed_at?->toIso8601String(),
                        'duration' => $lastSync->getDurationFormatted(),
                    ] : null,
                    'last_successful_sync' => $lastSuccessfulSync ? [
                        'id' => $lastSuccessfulSync->id,
                        'type' => $lastSuccessfulSync->type->value,
                        'completed_at' => $lastSuccessfulSync->completed_at->toIso8601String(),
                        'duration' => $lastSuccessfulSync->getDurationFormatted(),
                    ] : null,
                    'recent_syncs' => $recentSyncs,
                    'api_status' => $this->apiClient->isAvailable(),
                    'api_configured' => $this->apiClient->isConfigured(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get sync status', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get sync status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get sync history.
     */
    public function history(Request $request): JsonResponse
    {
        try {
            $days = min($request->input('days', 30), 90);

            $syncs = SyncLog::recent($days)
                ->orderBy('started_at', 'desc')
                ->paginate($request->input('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $syncs->items(),
                'meta' => [
                    'current_page' => $syncs->currentPage(),
                    'last_page' => $syncs->lastPage(),
                    'per_page' => $syncs->perPage(),
                    'total' => $syncs->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get sync history', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get sync history',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear API cache.
     */
    public function clearCache(): JsonResponse
    {
        try {
            $this->apiClient->clearCache('*');

            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to clear cache', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
