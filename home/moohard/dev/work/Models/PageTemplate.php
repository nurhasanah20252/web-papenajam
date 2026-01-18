<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PageTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'structure',
        'is_default',
    ];

    protected $casts = [
        'structure' => 'array',
        'is_default' => 'boolean',
    ];

    /**
     * Get pages using this template.
     */
    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }
}
