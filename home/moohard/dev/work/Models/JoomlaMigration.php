<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JoomlaMigration extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'total_records',
        'processed_records',
        'failed_records',
        'errors',
        'started_at',
        'completed_at',
        'metadata',
    ];

    protected $casts = [
        'errors' => 'array',
        'metadata' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_ROLLED_BACK = 'rolled_back';

    public const TYPE_CATEGORIES = 'categories';
    public const TYPE_PAGES = 'pages';
    public const TYPE_NEWS = 'news';
    public const TYPE_MENUS = 'menus';
    public const TYPE_DOCUMENTS = 'documents';

    /**
     * Get items for this migration.
     */
    public function items(): HasMany
    {
        return $this->hasMany(JoomlaMigrationItem::class);
    }

    /**
     * Calculate progress percentage.
     */
    public function getProgressAttribute(): int
    {
        if ($this->total_records === 0) {
            return 0;
        }

        return (int) round(($this->processed_records / $this->total_records) * 100);
    }

    /**
     * Check if migration is complete.
     */
    public function isComplete(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if migration has failed.
     */
    public function hasFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Mark migration as running.
     */
    public function markAsRunning(): void
    {
        $this->update([
            'status' => self::STATUS_RUNNING,
            'started_at' => now(),
        ]);
    }

    /**
     * Mark migration as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark migration as failed.
     */
    public function markAsFailed(array $errors = []): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'completed_at' => now(),
            'errors' => array_merge($this->errors ?? [], $errors),
        ]);
    }

    /**
     * Mark migration as rolled back.
     */
    public function markAsRolledBack(): void
    {
        $this->update([
            'status' => self::STATUS_ROLLED_BACK,
            'completed_at' => now(),
        ]);
    }

    /**
     * Update progress.
     */
    public function updateProgress(int $processed, int $failed = 0): void
    {
        $this->update([
            'processed_records' => $processed,
            'failed_records' => $failed,
        ]);
    }
}
