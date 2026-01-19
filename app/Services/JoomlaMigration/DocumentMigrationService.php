<?php

namespace App\Services\JoomlaMigration;

use App\Models\Document;

class DocumentMigrationService extends BaseMigrationService
{
    public function getType(): string
    {
        return JoomlaMigration::TYPE_DOCUMENTS;
    }

    public function getModelClass(): string
    {
        return Document::class;
    }

    public function validateData(array $data): bool
    {
        return ! empty($data['title'] ?? $data['filename'] ?? null);
    }

    public function transformData(array $data): array
    {
        $title = $data['title'] ?? $data['name'] ?? $data['filename'] ?? '';
        $path = $data['path'] ?? $data['filename'] ?? $data['file'] ?? '';
        $filename = basename($path);

        return [
            'title' => $title,
            'description' => $data['description'] ?? $data['introtext'] ?? null,
            'file_path' => $this->processFilePath($path),
            'file_name' => $filename,
            'file_type' => strtolower(pathinfo($filename, PATHINFO_EXTENSION)),
            'file_size' => $data['filesize'] ?? $data['file_size'] ?? 0,
            'mime_type' => $this->getMimeType($filename),
            'category_id' => $this->mapCategory($data['catid'] ?? $data['category_id'] ?? null),
            'is_public' => $this->isPublic($data),
            'download_count' => $data['hits'] ?? 0,
            'uploaded_by' => $this->mapAuthor($data['created_by'] ?? null),
            'published_at' => now(),
        ];
    }

    public function saveData(array $data): int
    {
        $document = Document::create($data);

        return $document->id;
    }

    /**
     * Process file path.
     */
    protected function processFilePath(string $path): string
    {
        if (empty($path)) {
            return '';
        }

        return app(JoomlaDataCleaner::class)->processImagePath($path);
    }

    /**
     * Get MIME type from filename.
     */
    protected function getMimeType(string $filename): string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return match ($extension) {
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            default => 'application/octet-stream',
        };
    }

    /**
     * Map Joomla category to local category.
     */
    protected function mapCategory(?int $joomlaCategoryId): ?int
    {
        if ($joomlaCategoryId === null) {
            return null;
        }

        $item = JoomlaMigrationItem::where('migration_id', $this->migration->id)
            ->where('type', JoomlaMigration::TYPE_CATEGORIES)
            ->where('joomla_id', $joomlaCategoryId)
            ->where('status', JoomlaMigrationItem::STATUS_COMPLETED)
            ->first();

        return $item?->local_id;
    }

    /**
     * Map Joomla author to local author.
     */
    protected function mapAuthor(?int $joomlaAuthorId): ?int
    {
        if ($joomlaAuthorId === null) {
            return null;
        }

        $item = JoomlaMigrationItem::where('migration_id', $this->migration->id)
            ->where('type', 'users')
            ->where('joomla_id', $joomlaAuthorId)
            ->where('status', JoomlaMigrationItem::STATUS_COMPLETED)
            ->first();

        return $item?->local_id;
    }

    /**
     * Check if document is public.
     */
    protected function isPublic(array $data): bool
    {
        // Joomla uses access levels (1 = public, 2 = registered, 3 = special)
        $access = $data['access'] ?? 1;

        return $access == 1;
    }
}
