<?php

namespace App\Providers;

use App\Services\JoomlaMigration\CategoryMigrationService;
use App\Services\JoomlaMigration\ContentMigrationService;
use App\Services\JoomlaMigration\DocumentMigrationService;
use App\Services\JoomlaMigration\JoomlaDataCleaner;
use App\Services\JoomlaMigration\JoomlaMigrationManager;
use App\Services\JoomlaMigration\MenuMigrationService;
use App\Services\JoomlaMigration\NewsMigrationService;
use Illuminate\Support\ServiceProvider;

class JoomlaMigrationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(JoomlaDataCleaner::class, function () {
            return new JoomlaDataCleaner();
        });

        $this->app->singleton(CategoryMigrationService::class, function ($app) {
            return new CategoryMigrationService();
        });

        $this->app->singleton(ContentMigrationService::class, function ($app) {
            return new ContentMigrationService();
        });

        $this->app->singleton(NewsMigrationService::class, function ($app) {
            return new NewsMigrationService();
        });

        $this->app->singleton(MenuMigrationService::class, function ($app) {
            return new MenuMigrationService();
        });

        $this->app->singleton(DocumentMigrationService::class, function ($app) {
            return new DocumentMigrationService();
        });

        $this->app->singleton(JoomlaMigrationManager::class, function ($app) {
            return new JoomlaMigrationManager(
                $app->make(CategoryMigrationService::class),
                $app->make(ContentMigrationService::class),
                $app->make(NewsMigrationService::class),
                $app->make(MenuMigrationService::class),
                $app->make(DocumentMigrationService::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
