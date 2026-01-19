<?php

namespace App\Models\Sipp;

use App\Enums\SyncStatus;
use App\Enums\SyncType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    /** @use HasFactory<\Database\Factories\SyncLogFactory> */
    use HasFactory;

    protected $fillable = [
        'type',
        'status',
        'started_at',
        'completed_at',
        'triggered_by',
        'error_message',
        'stats',
    ];

    protected $casts = [
        'type' => SyncType::class,
        'status' => SyncStatus::class,
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'stats' => 'array',
    ];

    protected $hidden = [];

    public function scopeSuccessful($query)
    {
        return $query->where('status', SyncStatus::SUCCESS);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', SyncStatus::FAILED);
    }

    public function scopeRunning($query)
    {
        return $query->where('status', SyncStatus::RUNNING);
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('started_at', '>=', now()->subDays($days));
    }

    public function scopeByType($query, SyncType $type)
    {
        return $query->where('type', $type);
    }

    public static function getLastSuccessfulSyncDate(): ?\Carbon\CarbonInterface
    {
        return static::successful()
            ->latest('completed_at')
            ->value('completed_at');
    }

    public static function getLastSync(): ?self
    {
        return static::latest('started_at')->first();
    }

    public static function getLastSuccessfulSync(): ?self
    {
        return static::successful()->latest('completed_at')->first();
    }

    public static function isSyncRunning(): bool
    {
        return static::running()->exists();
    }

    public function isSuccessful(): bool
    {
        return $this->status === SyncStatus::SUCCESS;
    }

    public function isFailed(): bool
    {
        return $this->status === SyncStatus::FAILED;
    }

    public function isRunning(): bool
    {
        return $this->status === SyncStatus::RUNNING;
    }

    public function getDurationInSeconds(): ?int
    {
        if (! $this->completed_at) {
            return null;
        }

        return $this->completed_at->diffInSeconds($this->started_at);
    }

    public function getDurationFormattedAttribute(): string
    {
        $seconds = $this->getDurationInSeconds();

        if ($seconds === null) {
            return '-';
        }

        if ($seconds < 60) {
            return "{$seconds} detik";
        }

        if ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            $secs = $seconds % 60;

            return "{$minutes}m {$secs}d";
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        return "{$hours}j {$minutes}m";
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            SyncStatus::PENDING => 'Pending',
            SyncStatus::RUNNING => 'Sedang Berjalan',
            SyncStatus::SYNCED => 'Tersinkron',
            SyncStatus::FAILED => 'Gagal',
            SyncStatus::SKIPPED => 'Dilewati',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type->label();
    }
}
