<?php

namespace App\Console\Commands;

use App\Services\CacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class OptimizeApplication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:optimize-application
        {--clear : Clear all caches before optimizing}
        {--fresh : Clear all caches and rebuild from scratch}
        {--detailed : Show detailed output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize application performance with caching and compilation';

    public function __construct(
        private CacheService $cacheService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Optimizing PA Penajam Application...');

        if ($this->option('clear') || $this->option('fresh')) {
            $this->clearAllCaches();
        }

        $this->newLine();
        $this->optimizeConfig();

        try {
            $this->optimizeRoutes();
        } catch (\Exception $e) {
            $this->warn('  ⚠️  Route caching skipped due to route conflicts');
            if ($this->option('detailed')) {
                $this->line('    └─ Route conflicts must be resolved before caching');
            }
        }

        try {
            $this->optimizeViews();
        } catch (\Exception $e) {
            $this->warn('  ⚠️  View caching skipped - Filament components need to be published');
            if ($this->option('detailed')) {
                $this->line('    └─ Run php artisan filament:upgrade to fix');
            }
        }

        $this->warmupCache();
        $this->optimizeComposer();

        $this->newLine();
        $this->info('✅ Application optimized successfully!');
        $this->newLine();

        $this->displayOptimizationTips();

        return Command::SUCCESS;
    }

    private function clearAllCaches(): void
    {
        $this->info('🧹 Clearing all caches...');

        $this->line('  → Clearing application cache...');
        Artisan::call('cache:clear');

        $this->line('  → Clearing configuration cache...');
        Artisan::call('config:clear');

        $this->line('  → Clearing route cache...');
        Artisan::call('route:clear');

        $this->line('  → Clearing view cache...');
        Artisan::call('view:clear');

        $this->line('  → Clearing event cache...');
        Artisan::call('event:clear');

        $this->cacheService->clearAllCaches();

        $this->newLine();
        $this->info('✅ All caches cleared.');
    }

    private function optimizeConfig(): void
    {
        $this->line('  → Caching configuration...');
        Artisan::call('config:cache');

        if ($this->option('detailed')) {
            $this->line('    └─ Configuration files merged and cached');
        }
    }

    private function optimizeRoutes(): void
    {
        $this->line('  → Caching routes...');
        Artisan::call('route:cache');

        if ($this->option('detailed')) {
            $this->line('    └─ Route files cached for faster lookup');
        }
    }

    private function optimizeViews(): void
    {
        $this->line('  → Compiling views...');
        Artisan::call('view:cache');

        if ($this->option('detailed')) {
            $this->line('    └─ Blade templates compiled and cached');
        }
    }

    private function warmupCache(): void
    {
        $this->line('  → Warming up application cache...');
        $this->cacheService->getMenu('header');
        $this->cacheService->getMenu('footer');
        $this->cacheService->getMenu('sidebar');
        $this->cacheService->getPublicSettings();
        $this->cacheService->getCategories('news');
        $this->cacheService->getCategories('document');
        $this->cacheService->getCategories('page');
        $this->cacheService->getFeaturedNews(5);
        $this->cacheService->getLatestNews(5);

        if ($this->option('detailed')) {
            $this->line('    └─ Application cache warmed up with frequently accessed data');
        }
    }

    private function optimizeComposer(): void
    {
        $this->line('  → Clearing and caching events...');
        Artisan::call('event:cache');

        if ($this->option('detailed')) {
            $this->line('    └─ Events cached');
        }
    }

    private function displayOptimizationTips(): void
    {
        $this->info('📋 Optimization Tips:');
        $this->newLine();
        $this->line('  • For production, set CACHE_STORE=redis in .env');
        $this->line('  • Set QUEUE_CONNECTION=redis or database for background jobs');
        $this->line('  • Run php artisan queue:work for processing background jobs');
        $this->line('  • Enable OPcache in PHP configuration');
        $this->line('  • Use HTTP/2 and compression on your web server');
        $this->line('  • Enable CDN for static assets');
        $this->newLine();
        $this->line('  To monitor performance:');
        $this->line('  • Check logs for slow requests (>1000ms)');
        $this->line('  • Use X-Execution-Time and X-Memory-Usage headers');
        $this->line('  • Review query logs for N+1 problems');
    }
}
