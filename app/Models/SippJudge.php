<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SippJudge extends Model
{
    use HasFactory;

    /**
     * The table name.
     *
     * @var string
     */
    protected $table = 'sipp_judges';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'external_id',
        'judge_code',
        'full_name',
        'title',
        'specialization',
        'chamber',
        'is_active',
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
            'is_active' => 'boolean',
            'last_sync_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get court schedules for this judge.
     */
    public function courtSchedules(): HasMany
    {
        return $this->hasMany(CourtSchedule::class, 'judge_name', 'full_name');
    }

    /**
     * Scope query for active judges.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get formatted name with title.
     */
    public function getFormattedName(): string
    {
        if ($this->title) {
            return $this->title.' '.$this->full_name;
        }

        return $this->full_name;
    }
}
