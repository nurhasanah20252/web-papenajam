<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SippSyncLog extends Model
{
    use HasFactory;

    /**
     * The table name.
     *
     * @var string
     */
    protected $table = 'sipp_sync_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'sync_type',
        'start_time',
        'end_time',
        'records_fetched',
        'records_updated',
        'records_created',
        'error_message',
        'created_by',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'records_fetched' => 'integer',
            'records_updated' => 'integer',
            'records_created' => 'integer',
            'metadata' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Scope query by sync type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('sync_type', $type);
    }

    /**
     * Scope query for failed syncs.
     */
    public function scopeFailed($query)
    {
        return $query->whereNotNull('error_message');
    }

    /**
     * Scope query for successful syncs.
     */
    public function scopeSuccessful($query)
    {
        return $query->whereNull('error_message');
    }

    /**
     * Get duration in seconds.
     */
    public function getDuration(): ?float
    {
        if (!$this->start_time || !$this->end_time) {
            return null;
        }

        return $this->end_time->diffInSeconds($this->start_time);
    }

    /**
     * Get formatted duration.
     */
    public function getFormattedDuration(): string
    {
        $duration = $this->getDuration();

        if ($duration === null) {
            return '-';
        }

        if ($duration < 60) {
            return $duration . 's';
        }

        if ($duration < 3600) {
            return round($duration / 60, 2) . 'm';
        }

        return round($duration / 3600, 2) . 'h';
    }

    /**
     * Check if sync was successful.
     */
    public function wasSuccessful(): bool
    {
        return $this->error_message === null;
    }

    /**
     * Get total records processed.
     */
    public function getTotalProcessed(): int
    {
        return ($this->records_fetched ?? 0)
            + ($this->records_updated ?? 0)
            + ($this->records_created ?? 0);
    }
}
