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
        return !empty($data['title'] ?? $data['name'] ?? null);
    }

    public function transformData(array $data): array
    {
        $name = $data['title'] ?? $data['name'] ?? '';
        $alias = $data['alias'] ?? Str::slug($name);

        return [
            'name' => $name,
            'slug' => $this->generateSlugFromExisting($alias),
            'description' => $data['description'] ?? $data['introtext'] ?? null,
            'parent_id' => $this->mapParentId($data['parent_id'] ?? null),
            'order' => $data['lft'] ?? $data['ordering'] ?? 0,
        ];
    }

    public function saveData(array $data): int
    {
        $category = Category::create($data);

        return $category->id;
    }

    /**
     * Generate unique slug based on existing categories.
     */
    protected function generateSlugFromExisting(string $baseSlug): string
    {
        $slug = $baseSlug;
        $i = 1;

        while (Category::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$i;
            $i++;
        }

        return $slug;
    }

    /**
     * Map Joomla parent ID to new parent ID.
     */
    protected function mapParentId(?int $joomlaParentId): ?int
    {
        if ($joomlaParentId === null) {
            return null;
        }

        $item = JoomlaMigrationItem::where('migration_id', $this->migration->id)
            ->where('type', $this->getType())
            ->where('joomla_id', $joomlaParentId)
            ->where('status', JoomlaMigrationItem::STATUS_COMPLETED)
            ->first();

        return $item?->local_id;
    }
}
