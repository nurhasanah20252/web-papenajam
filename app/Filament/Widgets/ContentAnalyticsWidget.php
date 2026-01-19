<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ContentAnalyticsWidget extends ChartWidget
{
    protected ?string $heading = 'Content Analytics (Last 7 Days)';

    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected function getData(): array
    {
        $days = [];
        $pagesData = [];
        $newsData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $days[] = $date->format('M d');

            $pagesData[] = DB::table('pages')
                ->whereDate('created_at', $date)
                ->count();

            $newsData[] = DB::table('news')
                ->whereDate('created_at', $date)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pages Created',
                    'data' => $pagesData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                ],
                [
                    'label' => 'News Created',
                    'data' => $newsData,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
                    'borderColor' => 'rgb(16, 185, 129)',
                ],
            ],
            'labels' => $days,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
