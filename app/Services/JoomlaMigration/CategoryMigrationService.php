<?php

namespace App\Services\JoomlaMigration;

use App\Models\Category;
use Illuminate\Support\Str;

class CategoryMigrationService extends BaseMigrationService
{
    public function getType(): string
    {
        return JoomlaMigration::TYPE_CATEGORIES;
    }

    public function getModelClass(): string
    {
        return Category::class;
    }

    public function validateData(array $data): bool
    {
        return ! empty($data['title'] ?? $data['name'] ?? null);
    }

    public function transformData(array $data): array
    {
        $name = $data['title'] ?? $data['name'] ?? '';
        $alias = $data['alias'] ?? Str::slug($name);

        return [
            'name' => $name,
            'slug' => $this->generateSlug($alias),
            'description' => $data['description'] ?? $data['introtext'] ?? null,
            'parent_id' => $this->mapParentId($data['parent_id'] ?? null),
            'type' => $this->mapType($data['extension'] ?? ''),
            'order' => $data['lft'] ?? $data['ordering'] ?? 0,
        ];
    }

    /**
     * Map Joomla extension to category type.
     */
    protected function mapType(string $extension): string
    {
        return match ($extension) {
            'com_content' => 'news',
            'com_contact' => 'page',
            'com_banners' => 'news',
            'com_weblinks' => 'news',
            'com_newsfeeds' => 'news',
            default => 'news',
        };
    }

    /**
     * Map Joomla parent ID to new parent ID.
     */
    protected function mapParentId($joomlaParentId): ?int
    {
        if ($joomlaParentId === null || (int) $joomlaParentId <= 1) {
            return null;
        }

        $item = \App\Models\JoomlaMigrationItem::where('migration_id', $this->migration->id)
            ->where('type', $this->getType())
            ->where('joomla_id', (int) $joomlaParentId)
            ->where('status', \App\Models\JoomlaMigrationItem::STATUS_COMPLETED)
            ->first();

        return $item?->local_id;
    }
}
