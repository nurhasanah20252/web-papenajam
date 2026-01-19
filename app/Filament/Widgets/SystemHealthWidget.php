<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class SystemHealthWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $databaseSize = $this->getDatabaseSize();
        $diskUsage = $this->getDiskUsage();
        $memoryUsage = $this->getMemoryUsage();

        return [
            Stat::make('Database Size', $databaseSize)
                ->description('Total database size')
                ->descriptionIcon('heroicon-o-circle-stack')
                ->color('primary'),

            Stat::make('Disk Usage', $diskUsage)
                ->description('Storage used')
                ->descriptionIcon('heroicon-o-server')
                ->color($this->getDiskUsageColor($diskUsage)),

            Stat::make('Memory Usage', $memoryUsage)
                ->description('PHP memory limit')
                ->descriptionIcon('heroicon-o-cpu-chip')
                ->color($this->getMemoryUsageColor($memoryUsage)),

            Stat::make('PHP Version', PHP_VERSION)
                ->description('Current version')
                ->descriptionIcon('heroicon-o-code-bracket')
                ->color('success'),

            Stat::make('Laravel Version', app()->version())
                ->description('Framework version')
                ->descriptionIcon('heroicon-o-rectangle-stack')
                ->color('info'),

            Stat::make('Database Connection', 'OK')
                ->description('Connection status')
                ->descriptionIcon('heroicon-o-signal')
                ->color('success')
                ->chart([10, 10, 10, 10, 10, 10, 10]),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }

    private function getDatabaseSize(): string
    {
        try {
            $result = DB::select('SELECT SUM(data_length + index_length) AS size FROM information_schema.TABLES WHERE table_schema = ?', [env('DB_DATABASE', 'laravel')]);

            if (isset($result[0]) && $result[0]->size) {
                $sizeInMB = $result[0]->size / 1024 / 1024;

                return $sizeInMB < 1024
                    ? number_format($sizeInMB, 2).' MB'
                    : number_format($sizeInMB / 1024, 2).' GB';
            }
        } catch (\Exception $e) {
            return 'N/A';
        }

        return '0 MB';
    }

    private function getDiskUsage(): string
    {
        $bytes = disk_total_space('.');
        $free = disk_free_space('.');

        if ($bytes === false || $free === false) {
            return 'N/A';
        }

        $used = $bytes - $free;
        $percentage = ($used / $bytes) * 100;

        return number_format($percentage, 1).'%';
    }

    private function getMemoryUsage(): string
    {
        $memoryLimit = ini_get('memory_limit');
        $currentUsage = memory_get_usage(true);

        if ($memoryLimit === '-1') {
            return 'Unlimited';
        }

        $currentInMB = $currentUsage / 1024 / 1024;
        $limitInMB = $this->convertToMB($memoryLimit);

        return number_format($currentInMB, 2).' / '.$limitInMB.' MB';
    }

    private function convertToMB(string $value): float
    {
        $unit = strtoupper(substr($value, -1));
        $number = (float) substr($value, 0, -1);

        return match ($unit) {
            'G' => $number * 1024,
            'M' => $number,
            'K' => $number / 1024,
            default => $number / 1024 / 1024,
        };
    }

    private function getDiskUsageColor(string $usage): string
    {
        $percentage = (float) str_replace('%', '', $usage);

        return match (true) {
            $percentage >= 90 => 'danger',
            $percentage >= 70 => 'warning',
            default => 'success',
        };
    }

    private function getMemoryUsageColor(string $usage): string
    {
        if (str_contains($usage, 'Unlimited')) {
            return 'success';
        }

        preg_match('/([\d.]+)\s*\/\s*([\d.]+)/', $usage, $matches);
        if (isset($matches[1], $matches[2])) {
            $percentage = ($matches[1] / $matches[2]) * 100;

            return match (true) {
                $percentage >= 90 => 'danger',
                $percentage >= 70 => 'warning',
                default => 'success',
            };
        }

        return 'info';
    }
}
