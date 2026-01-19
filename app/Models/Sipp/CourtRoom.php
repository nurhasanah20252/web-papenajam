<?php

namespace App\Models\Sipp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourtRoom extends Model
{
    /** @use HasFactory<\Database\Factories\CourtRoomFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'building',
        'floor',
        'capacity',
        'is_active',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $hidden = [];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByBuilding($query, $building)
    {
        return $query->where('building', $building);
    }

    public function scopeByFloor($query, $floor)
    {
        return $query->where('floor', $floor);
    }

    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    public function getFullLocationAttribute(): string
    {
        $parts = array_filter([
            $this->building,
            $this->floor ? "Lantai {$this->floor}" : null,
            $this->name,
        ]);

        return implode(' - ', $parts);
    }
}
