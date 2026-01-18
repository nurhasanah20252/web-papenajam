<?php

namespace App\Http\Controllers\Api\Sipp;

use App\Http\Controllers\Controller;
use App\Models\Sipp\CourtSchedule;
use App\Resources\Sipp\ScheduleResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SippScheduleController extends Controller
{
    /**
     * Get court schedules with filtering and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = CourtSchedule::query();

            if ($request->filled('date')) {
                $query->byDate($request->date);
            }

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->byDateRange($request->start_date, $request->end_date);
            } elseif ($request->filled('start_date')) {
                $query->whereDate('scheduled_date', '>=', $request->start_date);
            } elseif ($request->filled('end_date')) {
                $query->whereDate('scheduled_date', '<=', $request->end_date);
            }

            if ($request->filled('case_type')) {
                $query->byCaseType($request->case_type);
            }

            if ($request->filled('court_room')) {
                $query->byCourtRoom($request->court_room);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('case_number', 'like', "%{$search}%")
                        ->orWhere('case_title', 'like', "%{$search}%");
                });
            }

            $sortField = $request->input('sort_field', 'scheduled_date');
            $sortDirection = $request->input('sort_direction', 'asc');
            $query->orderBy($sortField, $sortDirection);

            $perPage = min($request->input('per_page', 15), 100);
            $schedules = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => ScheduleResource::collection($schedules->items()),
                'meta' => [
                    'current_page' => $schedules->currentPage(),
                    'last_page' => $schedules->lastPage(),
                    'per_page' => $schedules->perPage(),
                    'total' => $schedules->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch schedules', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch schedules',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get today's schedules.
     */
    public function today(Request $request): JsonResponse
    {
        try {
            $schedules = CourtSchedule::today()
                ->orderBy('scheduled_time')
                ->get();

            return response()->json([
                'success' => true,
                'data' => ScheduleResource::collection($schedules),
                'meta' => [
                    'date' => now()->toDateString(),
                    'total' => $schedules->count(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch today schedules', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch today schedules',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get upcoming schedules.
     */
    public function upcoming(Request $request): JsonResponse
    {
        try {
            $days = min($request->input('days', 7), 30);

            $schedules = CourtSchedule::upcoming($days)
                ->orderBy('scheduled_date')
                ->orderBy('scheduled_time')
                ->get();

            return response()->json([
                'success' => true,
                'data' => ScheduleResource::collection($schedules),
                'meta' => [
                    'start_date' => now()->toDateString(),
                    'end_date' => now()->addDays($days)->toDateString(),
                    'total' => $schedules->count(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch upcoming schedules', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch upcoming schedules',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single schedule details.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $schedule = CourtSchedule::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new ScheduleResource($schedule),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch schedule', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Schedule not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get schedule calendar view (grouped by date).
     */
    public function calendar(Request $request): JsonResponse
    {
        try {
            $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

            $schedules = CourtSchedule::byDateRange($startDate, $endDate)
                ->orderBy('scheduled_date')
                ->orderBy('scheduled_time')
                ->get()
                ->groupBy('scheduled_date');

            $calendar = [];
            foreach ($schedules as $date => $daySchedules) {
                $calendar[$date] = ScheduleResource::collection($daySchedules);
            }

            return response()->json([
                'success' => true,
                'data' => $calendar,
                'meta' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'total_days' => count($calendar),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch calendar', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch calendar data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
