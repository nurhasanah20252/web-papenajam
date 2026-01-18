<?php

namespace App\Models\Sipp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseType extends Model
{
    /** @use HasFactory<\Database\Factories\CaseTypeFactory> */
    use HasFactory;

    protected $table = 'sipp_case_types';

    protected $fillable = [
        'code',
        'name',
        'category',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $hidden = [];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'perdata' => 'Perdata',
            'pidana' => 'Pidana',
            'agama' => 'Agama',
            'hubungan_industrial' => 'Hubungan Industrial',
            'kontrak' => 'Kontrak',
            default => ucfirst(str_replace('_', ' ', $this->category ?? '')),
        };
    }

    public function getFullNameAttribute(): string
    {
        return $this->name ?? $this->code;
    }
}
