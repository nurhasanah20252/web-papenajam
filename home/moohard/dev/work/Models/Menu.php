<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'max_depth',
        'is_active',
    ];

    protected $casts = [
        'max_depth' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get items for this menu.
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->whereNull('parent_id')->orderBy('order');
    }
}
