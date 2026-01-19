<?php

namespace App\Filament\Widgets;

use App\Models\UserActivityLog;
use Filament\Widgets\ChartWidget;

class UserActivityChartWidget extends ChartWidget
{
    protected ?string $heading = 'User Activity (Last 7 Days)';

    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    protected function getData(): array
    {
        $days = [];
        $activityData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $days[] = $date->format('M d');

            $activityData[] = UserActivityLog::whereDate('created_at', $date)->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Activities',
                    'data' => $activityData,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.5)',
                    'borderColor' => 'rgb(245, 158, 11)',
                ],
            ],
            'labels' => $days,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
