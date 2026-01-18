<?php

namespace App\Models;

use App\Enums\UrlType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'menu_id',
        'parent_id',
        'title',
        'url_type',
        'route_name',
        'page_id',
        'custom_url',
        'icon',
        'order',
        'target_blank',
        'is_active',
        'conditions',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'url_type' => UrlType::class,
            'order' => 'integer',
            'target_blank' => 'boolean',
            'is_active' => 'boolean',
            'conditions' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the menu that owns this item.
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    /**
     * Get the parent menu item.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * Get the child menu items.
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('order');
    }

    /**
     * Get the page if url_type is page.
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'page_id');
    }

    /**
     * Get the URL for this menu item.
     */
    public function getUrl(): string
    {
        return match ($this->url_type) {
            UrlType::Route => route($this->route_name),
            UrlType::Page => $this->page?->getUrl() ?? '/',
            UrlType::Custom => $this->custom_url ?? '/',
            UrlType::External => $this->custom_url ?? '/',
            default => '/',
        };
    }

    /**
     * Check if menu item is active.
     */
    public function isActive(string $currentPath): bool
    {
        return $this->getUrl() === $currentPath;
    }

    /**
     * Check if menu item has children.
     */
    public function hasChildren(): bool
    {
        return $this->children()->count() > 0;
    }

    /**
     * Get menu item with children (for tree building).
     */
    public function withChildren(): array
    {
        $data = $this->toArray();

        if ($this->hasChildren()) {
            $data['children'] = $this->children()
                ->where('is_active', true)
                ->get()
                ->map(fn($child) => $child->withChildren())
                ->toArray();
        }

        return $data;
    }

    /**
     * Scope query for active items.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query by menu.
     */
    public function scopeByMenu($query, $menuId)
    {
        return $query->where('menu_id', $menuId);
    }
}
