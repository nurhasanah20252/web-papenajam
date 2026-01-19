<?php

namespace App\Filament;

use App\Filament\Pages\JoomlaMigrationPage;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\DocumentResource;
use App\Filament\Resources\JoomlaMigrationResource;
use App\Filament\Resources\MenuItemResource;
use App\Filament\Resources\MenuResource;
use App\Filament\Resources\NewsResource;
use App\Filament\Resources\PageResource;
use App\Filament\Resources\PageTemplateResource;
use App\Filament\Resources\UserResource;
use App\Filament\Widgets\ContentAnalyticsWidget;
use App\Filament\Widgets\ContentCreationChart;
use App\Filament\Widgets\ContentStatusChartWidget;
use App\Filament\Widgets\RecentActivityWidget;
use App\Filament\Widgets\RecentContentWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\SystemHealthWidget;
use App\Filament\Widgets\UserActivityChartWidget;
use App\Filament\Widgets\UserRegistrationChart;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
                'secondary' => Color::Slate,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
                'danger' => Color::Red,
                'info' => Color::Blue,
            ])
            ->brandName('Pengadilan Agama Penajam')
            ->brandLogo(asset('images/logo.png'))
            ->favicon(asset('images/favicon.ico'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
                JoomlaMigrationPage::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                StatsOverviewWidget::class,
                ContentAnalyticsWidget::class,
                UserActivityChartWidget::class,
                ContentStatusChartWidget::class,
                SystemHealthWidget::class,
                UserRegistrationChart::class,
                ContentCreationChart::class,
                RecentActivityWidget::class,
                RecentContentWidget::class,
            ])
            ->resources([
                UserResource::class,
                PageResource::class,
                PageTemplateResource::class,
                MenuResource::class,
                MenuItemResource::class,
                CategoryResource::class,
                NewsResource::class,
                DocumentResource::class,
                JoomlaMigrationResource::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Content')
                    ->icon('heroicon-o-document-text'),
                NavigationGroup::make()
                    ->label('Structure')
                    ->icon('heroicon-o-squares-2x2'),
                NavigationGroup::make()
                    ->label('Court Data')
                    ->icon('heroicon-o-building-office-2'),
                NavigationGroup::make()
                    ->label('Transparency')
                    ->icon('heroicon-o-shield-check'),
                NavigationGroup::make()
                    ->label('System')
                    ->icon('heroicon-o-cog'),
            ]);
    }
}
