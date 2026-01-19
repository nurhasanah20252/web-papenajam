<?php

namespace App\Models\Sipp;

use App\Enums\SyncStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CaseModel extends Model
{
    /** @use HasFactory<\Database\Factories\CaseModelFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'sipp_cases';

    protected $fillable = [
        'sipp_case_id',
        'case_number',
        'case_title',
        'case_type',
        'registration_date',
        'closing_date',
        'status',
        'judge_name',
        'plaintiff',
        'defendant',
        'claim_amount',
        'decision',
        'sync_status',
        'last_synced_at',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'closing_date' => 'date',
        'claim_amount' => 'decimal:2',
        'last_synced_at' => 'datetime',
    ];

    protected $hidden = [];

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeByCaseType($query, $caseType)
    {
        return $query->where('case_type', $caseType);
    }

    public function scopeByJudge($query, $judgeName)
    {
        return $query->where('judge_name', $judgeName);
    }

    public function scopeRegisteredThisMonth($query)
    {
        return $query->whereBetween('registration_date', [now()->startOfMonth(), now()->endOfMonth()]);
    }

    public function scopeClosedThisMonth($query)
    {
        return $query->whereBetween('closing_date', [now()->startOfMonth(), now()->endOfMonth()]);
    }

    public function scopePendingSync($query)
    {
        return $query->where('sync_status', SyncStatus::PENDING->value);
    }

    public function scopeSynced($query)
    {
        return $query->where('sync_status', SyncStatus::SYNCED->value);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function markAsSynced(): bool
    {
        return $this->update([
            'sync_status' => SyncStatus::SYNCED->value,
            'last_synced_at' => now(),
        ]);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'open' => 'Berlangsung',
            'closed' => 'Selesai',
            'pending' => 'Pending',
            'on_appeal' => 'Banding',
            default => ucfirst($this->status),
        };
    }

    public function getFormattedClaimAmountAttribute(): string
    {
        if (! $this->claim_amount) {
            return '-';
        }

        return 'Rp '.number_format($this->claim_amount, 0, ',', '.');
    }

    public function getCaseLabelAttribute(): string
    {
        return "{$this->case_number} - {$this->case_title}";
    }
}
