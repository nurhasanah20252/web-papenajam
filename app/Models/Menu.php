<?php

namespace App\Models;

use App\Enums\MenuLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'location',
        'max_depth',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'location' => MenuLocation::class,
            'max_depth' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the menu items for this menu.
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'menu_id')->orderBy('order');
    }

    /**
     * Get the menu items as a hierarchical tree.
     */
    public function getTree(): array
    {
        return $this->items()
            ->whereNull('parent_id')
            ->get()
            ->map(fn($item) => $item->withChildren())
            ->toArray();
    }

    /**
     * Scope query by location.
     */
    public function scopeByLocation($query, MenuLocation $location)
    {
        return $this->where('location', $location);
    }

    /**
     * Check if menu has items.
     */
    public function hasItems(): bool
    {
        return $this->items()->count() > 0;
    }
}
