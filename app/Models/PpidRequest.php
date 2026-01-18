<?php

namespace App\Models;

use App\Enums\PPIDPriority;
use App\Enums\PPIDStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpidRequest extends Model
{
    use HasFactory;

    /**
     * The table name.
     *
     * @var string
     */
    protected $table = 'ppid_requests';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'request_number',
        'applicant_name',
        'nik',
        'address',
        'phone',
        'email',
        'request_type',
        'subject',
        'description',
        'status',
        'response',
        'responded_at',
        'processed_by',
        'attachments',
        'priority',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'notes' => 'array',
            'status' => PPIDStatus::class,
            'priority' => PPIDPriority::class,
            'responded_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the processor of the request.
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Scope query by status.
     */
    public function scopeByStatus($query, PPIDStatus $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope query by priority.
     */
    public function scopeByPriority($query, PPIDPriority $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope query for pending requests.
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', [PPIDStatus::Submitted, PPIDStatus::Reviewed]);
    }

    /**
     * Scope query for high priority.
     */
    public function scopeHighPriority($query)
    {
        return $query->where('priority', PPIDPriority::High);
    }

    /**
     * Check if request is pending.
     */
    public function isPending(): bool
    {
        return $this->status->isPending();
    }

    /**
     * Check if request is high priority.
     */
    public function isHighPriority(): bool
    {
        return $this->priority === PPIDPriority::High;
    }

    /**
     * Get days since submission.
     */
    public function getDaysSinceSubmission(): int
    {
        return now()->diffInDays($this->created_at);
    }

    /**
     * Get days pending.
     */
    public function getDaysPending(): ?int
    {
        if (!$this->isPending()) {
            return null;
        }

        return abs(now()->diffInDays($this->created_at));
    }

    /**
     * Get attachment count.
     */
    public function getAttachmentCount(): int
    {
        return count($this->attachments ?? []);
    }

    /**
     * Generate request number.
     */
    public static function generateRequestNumber(): string
    {
        $prefix = 'PPID';
        $year = now()->format('Y');
        $month = now()->format('m');
        $sequence = str_pad(self::whereYear('created_at', now()->year)->count() + 1, 4, '0', STR_PAD_LEFT);

        return "{$prefix}/{$year}/{$month}/{$sequence}";
    }

    /**
     * Mark as responded.
     */
    public function markAsResponded(User $user, string $response): void
    {
        $this->update([
            'status' => PPIDStatus::Completed,
            'response' => $response,
            'responded_at' => now(),
            'processed_by' => $user->id,
        ]);
    }
}
