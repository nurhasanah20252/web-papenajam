<?php

namespace App\Models\Sipp;

use App\Enums\SyncStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourtSchedule extends Model
{
    /** @use HasFactory<\Database\Factories\CourtScheduleFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sipp_case_id',
        'case_number',
        'case_title',
        'case_type',
        'judge_name',
        'court_room',
        'scheduled_date',
        'scheduled_time',
        'status',
        'agenda',
        'notes',
        'sync_status',
        'last_synced_at',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'scheduled_time' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    protected $hidden = [];

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePostponed($query)
    {
        return $query->where('status', 'postponed');
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('scheduled_date', $date);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('scheduled_date', [$startDate, $endDate]);
    }

    public function scopeByCaseType($query, $caseType)
    {
        return $query->where('case_type', $caseType);
    }

    public function scopeByCourtRoom($query, $room)
    {
        return $query->where('court_room', $room);
    }

    public function scopePendingSync($query)
    {
        return $query->where('sync_status', SyncStatus::PENDING->value);
    }

    public function scopeFailedSync($query)
    {
        return $query->where('sync_status', SyncStatus::FAILED->value);
    }

    public function scopeSynced($query)
    {
        return $query->where('sync_status', SyncStatus::SYNCED->value);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_date', today());
    }

    public function scopeUpcoming($query, int $days = 7)
    {
        return $query->whereBetween('scheduled_date', [today(), today()->addDays($days)]);
    }

    public function isSynced(): bool
    {
        return $this->sync_status === SyncStatus::SYNCED->value;
    }

    public function isPending(): bool
    {
        return $this->sync_status === SyncStatus::PENDING->value;
    }

    public function isFailed(): bool
    {
        return $this->sync_status === SyncStatus::FAILED->value;
    }

    public function markAsSynced(): bool
    {
        return $this->update([
            'sync_status' => SyncStatus::SYNCED->value,
            'last_synced_at' => now(),
        ]);
    }

    public function markAsFailed(): bool
    {
        return $this->update([
            'sync_status' => SyncStatus::FAILED->value,
        ]);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'Dijadwalkan',
            'in_progress' => 'Sedang Disidangkan',
            'completed' => 'Selesai',
            'postponed' => 'Ditunda',
            'dismissed' => 'Diberhentikan',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    public function getFormattedTimeAttribute(): string
    {
        return $this->scheduled_time?->format('H:i') ?? '';
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->scheduled_date?->translatedFormat('l, d F Y') ?? '';
    }

    public function getCaseLabelAttribute(): string
    {
        return "{$this->case_number} - {$this->case_title}";
    }
}
