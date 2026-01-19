<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CaseTypeCategory;
use App\Models\CaseStatistics;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class CaseStatisticsController extends Controller
{
    /**
     * Display a listing of case statistics.
     */
    public function index(Request $request): Response
    {
        $query = CaseStatistics::query()
            ->latest('year')
            ->latest('month');

        // Filter by year
        if ($request->has('year') && $request->filled('year')) {
            $query->byYear((int) $request->input('year'));
        }

        // Filter by month
        if ($request->has('month') && $request->filled('month')) {
            $query->byMonth((int) $request->input('month'));
        }

        // Filter by court type
        if ($request->has('court_type') && $request->filled('court_type')) {
            $courtType = CaseTypeCategory::tryFrom($request->input('court_type'));
            if ($courtType) {
                $query->byCourtType($courtType);
            }
        }

        // Paginate results
        $statistics = $query->paginate(20);

        // Get available years for filter
        $availableYears = CaseStatistics::query()
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        // Get aggregated statistics for overview
        $overview = $this->getOverviewStats($request);

        return Inertia::render('case-statistics/index', [
            'statistics' => $statistics,
            'availableYears' => $availableYears,
            'overview' => $overview,
            'filters' => [
                'year' => $request->input('year'),
                'month' => $request->input('month'),
                'court_type' => $request->input('court_type'),
            ],
        ]);
    }

    /**
     * Export case statistics to Excel.
     */
    public function export(Request $request): BinaryFileResponse
    {
        $query = CaseStatistics::query()
            ->latest('year')
            ->latest('month');

        // Apply filters
        if ($request->has('year') && $request->filled('year')) {
            $query->byYear((int) $request->input('year'));
        }

        if ($request->has('month') && $request->filled('month')) {
            $query->byMonth((int) $request->input('month'));
        }

        if ($request->has('court_type') && $request->filled('court_type')) {
            $courtType = CaseTypeCategory::tryFrom($request->input('court_type'));
            if ($courtType) {
                $query->byCourtType($courtType);
            }
        }

        $statistics = $query->get();

        // Generate CSV
        $fileName = 'statistik-perkara-'.now()->format('Y-m-d').'.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($statistics) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fprintf($file, "\xEF\xBB\xBF");

            // Header row
            fputcsv($file, [
                'Tahun',
                'Bulan',
                'Jenis Peradilan',
                'Total Diajukan',
                'Total Selesai',
                'Sisa Perkara',
                'Rata-rata Hari Selesai',
                'Tingkat Penyelesaian (%)',
            ]);

            // Data rows
            foreach ($statistics as $stat) {
                fputcsv($file, [
                    $stat->year,
                    $stat->getMonthName(),
                    $stat->court_type?->label() ?? '-',
                    $stat->total_filed,
                    $stat->total_resolved,
                    $stat->pending_carryover,
                    $stat->avg_resolution_days ?? '-',
                    $stat->settlement_rate ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get overview statistics for charts.
     */
    private function getOverviewStats(Request $request): array
    {
        $query = CaseStatistics::query();

        // Apply year filter if specified
        if ($request->has('year') && $request->filled('year')) {
            $query->byYear((int) $request->input('year'));
        }

        // Get monthly trends
        $monthlyTrends = CaseStatistics::query()
            ->selectRaw('month, SUM(total_filed) as total_filed, SUM(total_resolved) as total_resolved')
            ->when($request->input('year'), fn ($q) => $q->where('year', $request->input('year')))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Get court type distribution
        $courtDistribution = CaseStatistics::query()
            ->selectRaw('court_type, SUM(total_filed) as total, SUM(total_resolved) as resolved')
            ->when($request->input('year'), fn ($q) => $q->where('year', $request->input('year')))
            ->groupBy('court_type')
            ->get();

        // Get yearly trends (last 5 years)
        $yearlyTrends = CaseStatistics::query()
            ->selectRaw('year, SUM(total_filed) as total_filed, SUM(total_resolved) as total_resolved')
            ->where('year', '>=', now()->year - 5)
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        return [
            'monthlyTrends' => $monthlyTrends,
            'courtDistribution' => $courtDistribution,
            'yearlyTrends' => $yearlyTrends,
        ];
    }
}
