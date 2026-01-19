<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SippCourtRoom extends Model
{
    use HasFactory;

    /**
     * The table name.
     *
     * @var string
     */
    protected $table = 'sipp_court_rooms';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'building',
        'floor',
        'capacity',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get court schedules for this room.
     */
    public function courtSchedules(): HasMany
    {
        return $this->hasMany(CourtSchedule::class, 'court_room', 'name');
    }

    /**
     * Scope query for active rooms.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query by building.
     */
    public function scopeByBuilding($query, string $building)
    {
        return $query->where('building', $building);
    }
}
