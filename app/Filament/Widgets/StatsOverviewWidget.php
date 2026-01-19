<?php

namespace App\Filament\Widgets;

use App\Models\Document;
use App\Models\News;
use App\Models\Page;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $totalPages = Page::count();
        $publishedPages = Page::where('status', 'published')->count();
        $draftPages = Page::where('status', 'draft')->count();

        $totalNews = News::count();
        $publishedNews = News::where('status', 'published')->count();

        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();

        return [
            Stat::make('Total Users', $totalUsers)
                ->description($activeUsers.' active users')
                ->descriptionIcon('heroicon-o-users')
                ->color('primary')
                ->chart([7, 12, 10, 14, 15, 18, $totalUsers]),

            Stat::make('Pages', $totalPages)
                ->description($publishedPages.' published, '.$draftPages.' draft')
                ->descriptionIcon('heroicon-o-document-text')
                ->color($draftPages > 5 ? 'warning' : 'success'),

            Stat::make('News Articles', $totalNews)
                ->description($publishedNews.' published')
                ->descriptionIcon('heroicon-o-newspaper')
                ->color('success'),

            Stat::make('Documents', Document::count())
                ->description(Document::where('is_public', true)->count().' public')
                ->descriptionIcon('heroicon-o-document')
                ->color('info'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
