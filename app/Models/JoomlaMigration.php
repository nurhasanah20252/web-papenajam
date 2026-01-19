<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JoomlaMigration extends Model
{
    use HasFactory;

    /**
     * The table name.
     *
     * @var string
     */
    protected $table = 'joomla_migrations';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'status',
        'metadata',
        'total_records',
        'processed_records',
        'failed_records',
        'progress',
        'errors',
        'started_at',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'errors' => 'array',
            'progress' => 'integer',
            'total_records' => 'integer',
            'processed_records' => 'integer',
            'failed_records' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public const TYPE_CATEGORIES = 'categories';

    public const TYPE_PAGES = 'pages';

    public const TYPE_NEWS = 'news';

    public const TYPE_MENUS = 'menus';

    public const TYPE_MENU_ITEMS = 'menu_items';

    public const TYPE_DOCUMENTS = 'documents';

    public const TYPE_USERS = 'users';

    public const STATUS_PENDING = 'pending';

    public const STATUS_RUNNING = 'running';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_ROLLED_BACK = 'rolled_back';

    /**
     * Get the items for this migration.
     */
    public function items(): HasMany
    {
        return $this->hasMany(JoomlaMigrationItem::class);
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
            'progress' => 100,
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
     * Update migration progress.
     */
    public function updateProgress(int $processed, int $failed): void
    {
        $progress = $this->total_records > 0
            ? (int) (($processed / $this->total_records) * 100)
            : 0;

        $this->update([
            'processed_records' => $processed,
            'failed_records' => $failed,
            'progress' => $progress,
        ]);
    }

    /**
     * Check if migration is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if migration is running.
     */
    public function isRunning(): bool
    {
        return $this->status === self::STATUS_RUNNING;
    }

    /**
     * Check if migration is complete.
     */
    public function isComplete(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if migration failed.
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if migration is rolled back.
     */
    public function isRolledBack(): bool
    {
        return $this->status === self::STATUS_ROLLED_BACK;
    }

    /**
     * Scope query by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope query for pending migrations.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope query for running migrations.
     */
    public function scopeRunning($query)
    {
        return $query->where('status', self::STATUS_RUNNING);
    }

    /**
     * Scope query for completed migrations.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope query for failed migrations.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }
}
