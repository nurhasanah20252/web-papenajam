<?php

namespace App\Models;

use App\Enums\ScheduleStatus;
use App\Enums\SyncStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourtSchedule extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'external_id',
        'case_number',
        'case_title',
        'case_type',
        'judge_name',
        'court_room',
        'room_code',
        'schedule_date',
        'schedule_time',
        'schedule_status',
        'parties',
        'agenda',
        'notes',
        'last_sync_at',
        'sync_status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'parties' => 'array',
            'schedule_date' => 'date',
            'schedule_time' => 'string',
            'schedule_status' => ScheduleStatus::class,
            'sync_status' => SyncStatus::class,
            'last_sync_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Scope query by date.
     */
    public function scopeByDate($query, $date)
    {
        return $query->where('schedule_date', $date);
    }

    /**
     * Scope query by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('schedule_date', [$startDate, $endDate]);
    }

    /**
     * Scope query by room.
     */
    public function scopeByRoom($query, string $room)
    {
        return $query->where('court_room', $room);
    }

    /**
     * Scope query by judge.
     */
    public function scopeByJudge($query, string $judge)
    {
        return $query->where('judge_name', $judge);
    }

    /**
     * Scope query by status.
     */
    public function scopeByStatus($query, ScheduleStatus $status)
    {
        return $query->where('schedule_status', $status);
    }

    /**
     * Scope query for pending sync.
     */
    public function scopePendingSync($query)
    {
        return $query->where('sync_status', SyncStatus::Pending);
    }

    /**
     * Check if schedule is from SIPP.
     */
    public function isFromSipp(): bool
    {
        return $this->external_id !== null;
    }

    /**
     * Get parties as array.
     */
    public function getParties(): array
    {
        return $this->parties ?? [];
    }

    /**
     * Get formatted schedule time.
     */
    public function getFormattedTime(): ?string
    {
        if (!$this->schedule_time) {
            return null;
        }

        // schedule_time is stored as string
        return $this->schedule_time;
    }

    /**
     * Get formatted schedule date.
     */
    public function getFormattedDate(): ?string
    {
        if (!$this->schedule_date) {
            return null;
        }

        return $this->schedule_date->format('d F Y');
    }

    /**
     * Check if schedule is upcoming.
     */
    public function isUpcoming(): bool
    {
        if (!$this->schedule_date) {
            return false;
        }

        return $this->schedule_date->isFuture()
            && $this->schedule_status === ScheduleStatus::Scheduled;
    }

    /**
     * Check if schedule is today.
     */
    public function isToday(): bool
    {
        if (!$this->schedule_date) {
            return false;
        }

        return $this->schedule_date->isToday();
    }
}
