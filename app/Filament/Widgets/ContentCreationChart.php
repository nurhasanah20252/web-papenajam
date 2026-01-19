<?php

namespace App\Filament\Widgets;

use App\Models\News;
use App\Models\Page;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ContentCreationChart extends ChartWidget
{
    protected ?string $heading = 'Content Creation (Monthly)';

    protected static ?int $sort = 8;

    protected function getData(): array
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $dateGroupRaw = $isSqlite
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";

        $pagesData = Page::select(
            DB::raw('count(*) as count'),
            DB::raw("$dateGroupRaw as month")
        )
            ->where('created_at', '>=', now()->subYear())
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $newsData = News::select(
            DB::raw('count(*) as count'),
            DB::raw("$dateGroupRaw as month")
        )
            ->where('created_at', '>=', now()->subYear())
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Combine labels
        $labels = collect($pagesData->pluck('month'))
            ->merge($newsData->pluck('month'))
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        $pagesCounts = [];
        $newsCounts = [];

        foreach ($labels as $label) {
            $pagesCounts[] = $pagesData->firstWhere('month', $label)?->count ?? 0;
            $newsCounts[] = $newsData->firstWhere('month', $label)?->count ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pages',
                    'data' => $pagesCounts,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                ],
                [
                    'label' => 'News',
                    'data' => $newsCounts,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
                    'borderColor' => 'rgb(16, 185, 129)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
