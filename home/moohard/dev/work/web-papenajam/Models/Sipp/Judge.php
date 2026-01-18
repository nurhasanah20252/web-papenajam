<?php

namespace App\Models\Sipp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Judge extends Model
{
    /** @use HasFactory<\Database\Factories\JudgeFactory> */
    use HasFactory;

    protected $fillable = [
        'sipp_id',
        'name',
        'position',
        'court_name',
    ];

    protected $casts = [];

    protected $hidden = [];

    public function scopeByPosition($query, $position)
    {
        return $query->where('position', $position);
    }

    public function getPositionLabelAttribute(): string
    {
        return match ($this->position) {
            'ketua' => 'Ketua',
            'anggota' => 'Anggota',
            'hakim' => 'Hakim',
            'hakim_pegawai' => 'Hakim Pegawai',
            default => ucfirst($this->position),
        };
    }

    public function getFullNameAttribute(): string
    {
        return $this->name ?? '';
    }
}
