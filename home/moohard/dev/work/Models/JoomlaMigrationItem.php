<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JoomlaMigrationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'migration_id',
        'type',
        'joomla_id',
        'joomla_data',
        'local_model',
        'local_id',
        'status',
        'error_message',
    ];

    protected $casts = [
        'joomla_data' => 'array',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SKIPPED = 'skipped';

    /**
     * Get the migration this item belongs to.
     */
    public function migration(): BelongsTo
    {
        return $this->belongsTo(JoomlaMigration::class);
    }

    /**
     * Mark item as completed.
     */
    public function markAsCompleted(int $localId): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'local_id' => $localId,
        ]);
    }

    /**
     * Mark item as failed.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Mark item as skipped.
     */
    public function markAsSkipped(string $reason): void
    {
        $this->update([
            'status' => self::STATUS_SKIPPED,
            'error_message' => $reason,
        ]);
    }
}
