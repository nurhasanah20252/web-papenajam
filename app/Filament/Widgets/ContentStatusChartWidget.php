<?php

namespace App\Filament\Widgets;

use App\Models\News;
use App\Models\Page;
use Filament\Widgets\DoughnutChartWidget;

class ContentStatusChartWidget extends DoughnutChartWidget
{
    protected ?string $heading = 'Content Status Overview';

    protected static ?int $sort = 3;

    protected static bool $isLazy = false;

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'data' => [
                        Page::where('status', 'published')->count(),
                        Page::where('status', 'draft')->count(),
                        News::where('status', 'published')->count(),
                        News::where('status', 'draft')->count(),
                    ],
                    'backgroundColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(245, 158, 11)',
                        'rgb(16, 185, 129)',
                        'rgb(239, 68, 68)',
                    ],
                ],
            ],
            'labels' => [
                'Published Pages',
                'Draft Pages',
                'Published News',
                'Draft News',
            ],
        ];
    }
}
