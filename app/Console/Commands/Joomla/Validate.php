<?php

namespace App\Console\Commands\Joomla;

use App\Models\Category;
use App\Models\JoomlaMigration;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\News;
use App\Models\Page;
use Illuminate\Console\Command;

class Validate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'joomla:validate
                            {--detailed : Show detailed validation results}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate Joomla migration data integrity';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $detailed = $this->option('detailed');

        $this->info('Validating Joomla migrations...');
        $this->newLine();

        $issues = [];
        $warnings = [];

        // Validate categories
        $this->info('Validating categories...');
        $categoryMigrations = JoomlaMigration::bySourceTable('categories')->successful()->get();
        $categoryIssues = $this->validateCategories($categoryMigrations, $detailed);
        $issues = array_merge($issues, $categoryIssues['issues']);
        $warnings = array_merge($warnings, $categoryIssues['warnings']);
        $this->line("  Migrated: {$categoryMigrations->count()}, Issues: ".count($categoryIssues['issues']));

        // Validate pages
        $this->info('Validating pages...');
        $pageMigrations = JoomlaMigration::bySourceTable('pages')->successful()->get();
        $pageIssues = $this->validatePages($pageMigrations, $detailed);
        $issues = array_merge($issues, $pageIssues['issues']);
        $warnings = array_merge($warnings, $pageIssues['warnings']);
        $this->line("  Migrated: {$pageMigrations->count()}, Issues: ".count($pageIssues['issues']));

        // Validate news
        $this->info('Validating news...');
        $newsMigrations = JoomlaMigration::bySourceTable('news')->successful()->get();
        $newsIssues = $this->validateNews($newsMigrations, $detailed);
        $issues = array_merge($issues, $newsIssues['issues']);
        $warnings = array_merge($warnings, $newsIssues['warnings']);
        $this->line("  Migrated: {$newsMigrations->count()}, Issues: ".count($newsIssues['issues']));

        // Validate menus
        $this->info('Validating menus...');
        $menuMigrations = JoomlaMigration::bySourceTable('menus')->successful()->get();
        $menuIssues = $this->validateMenus($menuMigrations, $detailed);
        $issues = array_merge($issues, $menuIssues['issues']);
        $warnings = array_merge($warnings, $menuIssues['warnings']);
        $this->line("  Migrated: {$menuMigrations->count()}, Issues: ".count($menuIssues['issues']));

        // Validate menu items
        $this->info('Validating menu items...');
        $menuItemMigrations = JoomlaMigration::bySourceTable('menu_items')->successful()->get();
        $menuItemIssues = $this->validateMenuItems($menuItemMigrations, $detailed);
        $issues = array_merge($issues, $menuItemIssues['issues']);
        $warnings = array_merge($warnings, $menuItemIssues['warnings']);
        $this->line("  Migrated: {$menuItemMigrations->count()}, Issues: ".count($menuItemIssues['issues']));

        $this->newLine();

        // Calculate integrity percentage
        $totalMigrations = $categoryMigrations->count()
            + $pageMigrations->count()
            + $newsMigrations->count()
            + $menuMigrations->count()
            + $menuItemMigrations->count();

        $integrity = $totalMigrations > 0
            ? round((($totalMigrations - count($issues)) / $totalMigrations) * 100, 2)
            : 100;

        $this->info("Data Integrity: {$integrity}%");

        // Show summary
        $this->newLine();
        $this->table(
            ['Type', 'Count'],
            [
                ['Issues', count($issues)],
                ['Warnings', count($warnings)],
                ['Total Migrations', $totalMigrations],
            ]
        );

        // Show detailed results if requested
        if ($detailed && (count($issues) > 0 || count($warnings) > 0)) {
            $this->newLine();
            $this->info('Detailed Results:');

            if (count($issues) > 0) {
                $this->newLine();
                $this->error('Issues:');
                foreach ($issues as $issue) {
                    $this->line("  - {$issue}");
                }
            }

            if (count($warnings) > 0) {
                $this->newLine();
                $this->warn('Warnings:');
                foreach ($warnings as $warning) {
                    $this->line("  - {$warning}");
                }
            }
        }

        $this->newLine();

        if ($integrity >= 95) {
            $this->info('Validation passed! Data integrity is acceptable.');

            return self::SUCCESS;
        } elseif ($integrity >= 80) {
            $this->warn('Validation passed with warnings. Data integrity is below 95%.');

            return count($issues) > 0 ? self::FAILURE : self::SUCCESS;
        } else {
            $this->error('Validation failed! Data integrity is below 80%.');

            return self::FAILURE;
        }
    }

    /**
     * Validate category migrations.
     */
    protected function validateCategories($migrations, bool $detailed): array
    {
        $issues = [];
        $warnings = [];

        foreach ($migrations as $migration) {
            $category = Category::find($migration->target_id);

            if (! $category) {
                $issues[] = "Category migration record #{$migration->id} references non-existent category #{$migration->target_id}";

                continue;
            }

            // Check parent relationship
            if ($category->parent_id) {
                $parentExists = Category::where('id', $category->parent_id)->exists();
                if (! $parentExists) {
                    $issues[] = "Category #{$category->id} ({$category->name}) has non-existent parent #{$category->parent_id}";
                }
            }

            // Check for empty slugs
            if (empty($category->slug)) {
                $issues[] = "Category #{$category->id} ({$category->name}) has empty slug";
            }

            // Check for duplicate slugs
            $duplicateSlug = Category::where('slug', $category->slug)
                ->where('id', '!=', $category->id)
                ->exists();
            if ($duplicateSlug) {
                $warnings[] = "Category #{$category->id} ({$category->name}) has duplicate slug: {$category->slug}";
            }
        }

        return ['issues' => $issues, 'warnings' => $warnings];
    }

    /**
     * Validate page migrations.
     */
    protected function validatePages($migrations, bool $detailed): array
    {
        $issues = [];
        $warnings = [];

        foreach ($migrations as $migration) {
            $page = Page::find($migration->target_id);

            if (! $page) {
                $issues[] = "Page migration record #{$migration->id} references non-existent page #{$migration->target_id}";

                continue;
            }

            // Check for required fields
            if (empty($page->title)) {
                $issues[] = "Page #{$page->id} has empty title";
            }

            if (empty($page->slug)) {
                $issues[] = "Page #{$page->id} has empty slug";
            }

            // Check for duplicate slugs
            $duplicateSlug = Page::where('slug', $page->slug)
                ->where('id', '!=', $page->id)
                ->exists();
            if ($duplicateSlug) {
                $warnings[] = "Page #{$page->id} ({$page->title}) has duplicate slug: {$page->slug}";
            }

            // Check content
            if (empty($page->content)) {
                $warnings[] = "Page #{$page->id} ({$page->title}) has empty content";
            }
        }

        return ['issues' => $issues, 'warnings' => $warnings];
    }

    /**
     * Validate news migrations.
     */
    protected function validateNews($migrations, bool $detailed): array
    {
        $issues = [];
        $warnings = [];

        foreach ($migrations as $migration) {
            $news = News::find($migration->target_id);

            if (! $news) {
                $issues[] = "News migration record #{$migration->id} references non-existent news #{$migration->target_id}";

                continue;
            }

            // Check for required fields
            if (empty($news->title)) {
                $issues[] = "News #{$news->id} has empty title";
            }

            if (empty($news->slug)) {
                $issues[] = "News #{$news->id} has empty slug";
            }

            // Check category relationship
            if ($news->category_id) {
                $categoryExists = Category::where('id', $news->category_id)->exists();
                if (! $categoryExists) {
                    $issues[] = "News #{$news->id} ({$news->title}) references non-existent category #{$news->category_id}";
                }
            }

            // Check for duplicate slugs
            $duplicateSlug = News::where('slug', $news->slug)
                ->where('id', '!=', $news->id)
                ->exists();
            if ($duplicateSlug) {
                $warnings[] = "News #{$news->id} ({$news->title}) has duplicate slug: {$news->slug}";
            }

            // Check content
            if (empty($news->content)) {
                $warnings[] = "News #{$news->id} ({$news->title}) has empty content";
            }
        }

        return ['issues' => $issues, 'warnings' => $warnings];
    }

    /**
     * Validate menu migrations.
     */
    protected function validateMenus($migrations, bool $detailed): array
    {
        $issues = [];
        $warnings = [];

        foreach ($migrations as $migration) {
            $menu = Menu::find($migration->target_id);

            if (! $menu) {
                $issues[] = "Menu migration record #{$migration->id} references non-existent menu #{$migration->target_id}";

                continue;
            }

            // Check for menu items
            $itemCount = MenuItem::where('menu_id', $menu->id)->count();
            if ($itemCount === 0) {
                $warnings[] = "Menu #{$menu->id} ({$menu->name}) has no menu items";
            }
        }

        return ['issues' => $issues, 'warnings' => $warnings];
    }

    /**
     * Validate menu item migrations.
     */
    protected function validateMenuItems($migrations, bool $detailed): array
    {
        $issues = [];
        $warnings = [];

        foreach ($migrations as $migration) {
            $menuItem = MenuItem::find($migration->target_id);

            if (! $menuItem) {
                $issues[] = "Menu item migration record #{$migration->id} references non-existent menu item #{$migration->target_id}";

                continue;
            }

            // Check menu relationship
            if ($menuItem->menu_id) {
                $menuExists = Menu::where('id', $menuItem->menu_id)->exists();
                if (! $menuExists) {
                    $issues[] = "Menu item #{$menuItem->id} ({$menuItem->label}) references non-existent menu #{$menuItem->menu_id}";
                }
            }

            // Check parent relationship
            if ($menuItem->parent_id) {
                $parentExists = MenuItem::where('id', $menuItem->parent_id)->exists();
                if (! $parentExists) {
                    $issues[] = "Menu item #{$menuItem->id} ({$menuItem->label}) has non-existent parent #{$menuItem->parent_id}";
                }
            }

            // Check for empty labels
            if (empty($menuItem->label)) {
                $warnings[] = "Menu item #{$menuItem->id} has empty label";
            }

            // Check for circular references
            if ($menuItem->parent_id) {
                $parent = MenuItem::find($menuItem->parent_id);
                if ($parent && $parent->parent_id == $menuItem->id) {
                    $issues[] = "Circular reference detected between menu items #{$menuItem->id} and #{$parent->id}";
                }
            }
        }

        return ['issues' => $issues, 'warnings' => $warnings];
    }
}
