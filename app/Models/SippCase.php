<?php

namespace App\Models;

use App\Enums\CaseStatus;
use App\Enums\SyncStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SippCase extends Model
{
    use HasFactory;

    /**
     * The table name.
     *
     * @var string
     */
    protected $table = 'sipp_cases';

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
        'register_date',
        'register_number',
        'case_status',
        'priority',
        'plaintiff',
        'defendant',
        'attorney',
        'subject_matter',
        'last_hearing_date',
        'next_hearing_date',
        'final_decision_date',
        'decision_summary',
        'document_references',
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
            'plaintiff' => 'array',
            'defendant' => 'array',
            'attorney' => 'array',
            'document_references' => 'array',
            'register_date' => 'date',
            'last_hearing_date' => 'date',
            'next_hearing_date' => 'date',
            'final_decision_date' => 'date',
            'case_status' => CaseStatus::class,
            'sync_status' => SyncStatus::class,
            'last_sync_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Scope query by case number.
     */
    public function scopeByCaseNumber($query, string $caseNumber)
    {
        return $query->where('case_number', $caseNumber);
    }

    /**
     * Scope query by status.
     */
    public function scopeByStatus($query, CaseStatus $status)
    {
        return $query->where('case_status', $status);
    }

    /**
     * Scope query for pending sync.
     */
    public function scopePendingSync($query)
    {
        return $query->where('sync_status', SyncStatus::Pending);
    }

    /**
     * Get formatted parties.
     */
    public function getPlaintiffNames(): string
    {
        if (! $this->plaintiff) {
            return '-';
        }

        return implode(', ', array_column($this->plaintiff, 'name'));
    }

    /**
     * Get formatted defendant names.
     */
    public function getDefendantNames(): string
    {
        if (! $this->defendant) {
            return '-';
        }

        return implode(', ', array_column($this->defendant, 'name'));
    }

    /**
     * Check if case has upcoming hearing.
     */
    public function hasUpcomingHearing(): bool
    {
        return $this->next_hearing_date !== null
            && $this->next_hearing_date->isFuture();
    }

    /**
     * Get case age in days.
     */
    public function getCaseAge(): ?int
    {
        if (! $this->register_date) {
            return null;
        }

        return now()->diffInDays($this->register_date);
    }
}
