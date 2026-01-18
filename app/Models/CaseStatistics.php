<?php

namespace App\Models;

use App\Enums\CaseTypeCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseStatistics extends Model
{
    use HasFactory;

    /**
     * The table name.
     *
     * @var string
     */
    protected $table = 'case_statistics';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'year',
        'month',
        'case_type',
        'court_type',
        'total_filed',
        'total_resolved',
        'pending_carryover',
        'avg_resolution_days',
        'settlement_rate',
        'external_data_hash',
        'last_sync_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'total_filed' => 'integer',
            'total_resolved' => 'integer',
            'pending_carryover' => 'integer',
            'avg_resolution_days' => 'decimal:2',
            'settlement_rate' => 'decimal:2',
            'court_type' => CaseTypeCategory::class,
            'last_sync_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Scope query by year.
     */
    public function scopeByYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Scope query by month.
     */
    public function scopeByMonth($query, int $month)
    {
        return $query->where('month', $month);
    }

    /**
     * Scope query by court type.
     */
    public function scopeByCourtType($query, CaseTypeCategory $courtType)
    {
        return $query->where('court_type', $courtType);
    }

    /**
     * Get pending cases.
     */
    public function getPendingCases(): int
    {
        return $this->pending_carryover + $this->total_filed - $this->total_resolved;
    }

    /**
     * Get resolution rate percentage.
     */
    public function getResolutionRate(): float
    {
        if ($this->total_filed === 0) {
            return 0;
        }

        return ($this->total_resolved / $this->total_filed) * 100;
    }

    /**
     * Get settlement rate percentage.
     */
    public function getSettlementRate(): float
    {
        return $this->settlement_rate ?? 0;
    }

    /**
     * Get formatted month name.
     */
    public function getMonthName(): string
    {
        return now()->month($this->month)->format('F');
    }
}
