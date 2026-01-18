<?php

namespace App\Filament;

use App\Filament\Pages\JoomlaMigrationPage;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\JoomlaMigrationResource;
use App\Filament\Resources\MenuItemResource;
use App\Filament\Resources\MenuResource;
use App\Filament\Resources\PageResource;
use App\Filament\Resources\PageTemplateResource;
use App\Filament\Resources\UserResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeCustomizations;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
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
            ->resources([
                UserResource::class,
                PageResource::class,
                PageTemplateResource::class,
                MenuResource::class,
                MenuItemResource::class,
                CategoryResource::class,
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
                DisableBladeCustomizations::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->navigationGroups([
                'Content' => [
                    'icon' => 'heroicon-o-document-text',
                    'sort' => 1,
                ],
                'Structure' => [
                    'icon' => 'heroicon-o-squares-2x2',
                    'sort' => 2,
                ],
                'Court Data' => [
                    'icon' => 'heroicon-o-building-office-2',
                    'sort' => 3,
                ],
                'Transparency' => [
                    'icon' => 'heroicon-o-shield-check',
                    'sort' => 4,
                ],
                'System' => [
                    'icon' => 'heroicon-o-cog',
                    'sort' => 5,
                ],
            ]);
    }
}
