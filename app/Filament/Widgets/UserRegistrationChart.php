<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class UserRegistrationChart extends ChartWidget
{
    protected ?string $heading = 'User Registrations (Monthly)';

    protected static ?int $sort = 7;

    protected function getData(): array
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $dateGroupRaw = $isSqlite
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";

        $data = User::select(
            DB::raw('count(*) as count'),
            DB::raw("$dateGroupRaw as month")
        )
            ->where('created_at', '>=', now()->subYear())
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Users Registered',
                    'data' => $data->pluck('count')->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                ],
            ],
            'labels' => $data->pluck('month')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
