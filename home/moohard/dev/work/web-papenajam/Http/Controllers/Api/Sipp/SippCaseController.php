<?php

namespace App\Http\Controllers\Api\Sipp;

use App\Http\Controllers\Controller;
use App\Models\Sipp\CaseModel;
use App\Resources\Sipp\CaseResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SippCaseController extends Controller
{
    /**
     * Get cases with filtering and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = CaseModel::query();

            if ($request->filled('status')) {
                if ($request->status === 'open') {
                    $query->open();
                } elseif ($request->status === 'closed') {
                    $query->closed();
                }
            }

            if ($request->filled('case_type')) {
                $query->byCaseType($request->case_type);
            }

            if ($request->filled('judge')) {
                $query->byJudge($request->judge);
            }

            if ($request->filled('registration_month')) {
                $month = $request->registration_month;
                $year = $request->input('registration_year', now()->year);
                $query->whereYear('registration_date', $year)
                    ->whereMonth('registration_date', $month);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('case_number', 'like', "%{$search}%")
                        ->orWhere('case_title', 'like', "%{$search}%")
                        ->orWhere('plaintiff', 'like', "%{$search}%")
                        ->orWhere('defendant', 'like', "%{$search}%");
                });
            }

            $sortField = $request->input('sort_field', 'registration_date');
            $sortDirection = $request->input('sort_direction', 'desc');
            $query->orderBy($sortField, $sortDirection);

            $perPage = min($request->input('per_page', 15), 100);
            $cases = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => CaseResource::collection($cases->items()),
                'meta' => [
                    'current_page' => $cases->currentPage(),
                    'last_page' => $cases->lastPage(),
                    'per_page' => $cases->perPage(),
                    'total' => $cases->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch cases', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch cases',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single case details.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $case = CaseModel::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new CaseResource($case),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch case', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Case not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get case statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $year = $request->input('year', now()->year);
            $month = $request->input('month');

            $query = CaseModel::query();

            if ($month) {
                $query->whereYear('registration_date', $year)
                    ->whereMonth('registration_date', $month);
            } else {
                $query->whereYear('registration_date', $year);
            }

            $totalCases = $query->count();
            $openCases = (clone $query)->where('status', 'open')->count();
            $closedCases = (clone $query)->where('status', 'closed')->count();

            $byType = CaseModel::whereYear('registration_date', $year)
                ->when($month, fn($q) => $q->whereMonth('registration_date', $month))
                ->selectRaw('case_type, COUNT(*) as count')
                ->groupBy('case_type')
                ->pluck('count', 'case_type')
                ->toArray();

            return response()->json([
                'success' => true,
                'data' => [
                    'period' => [
                        'year' => $year,
                        'month' => $month,
                        'label' => $month ? now()->create($year, $month)->translatedFormat('F Y') : (string) $year,
                    ],
                    'total_cases' => $totalCases,
                    'open_cases' => $openCases,
                    'closed_cases' => $closedCases,
                    'closure_rate' => $totalCases > 0 ? round(($closedCases / $totalCases) * 100, 2) : 0,
                    'by_case_type' => $byType,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch case statistics', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch case statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
