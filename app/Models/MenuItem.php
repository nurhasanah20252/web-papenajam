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
        'type',
        'route_name',
        'page_id',
        'custom_url',
        'icon',
        'class_name',
        'order',
        'target_blank',
        'target',
        'is_active',
        'conditions',
        'display_rules',
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
            'display_rules' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::saving(function (MenuItem $item) {
            // Sync target string with target_blank boolean
            if ($item->isDirty('target')) {
                $item->target_blank = $item->target === '_blank';
            } elseif ($item->isDirty('target_blank')) {
                $item->target = $item->target_blank ? '_blank' : '_self';
            }

            // Sync type with url_type
            if ($item->isDirty('type') && $item->type) {
                $item->url_type = UrlType::from($item->type);
            } elseif ($item->isDirty('url_type')) {
                $item->type = $item->url_type->value;
            }
        });
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
        try {
            return match ($this->url_type) {
                UrlType::Route => $this->route_name ? route($this->route_name) : '#',
                UrlType::Page => $this->page?->getUrl() ?? '#',
                UrlType::Custom => $this->custom_url ?? '#',
                UrlType::External => $this->custom_url ?? '#',
                default => '#',
            };
        } catch (\Exception $e) {
            return '#';
        }
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
     * Check if the menu item should be visible based on display rules.
     */
    public function isVisible(?User $user = null): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if (empty($this->display_rules)) {
            return true;
        }

        // Logic for roles (example: ['roles' => ['admin', 'editor']])
        if (isset($this->display_rules['roles']) && is_array($this->display_rules['roles'])) {
            if (! $user) {
                return false;
            }

            return $user->hasAnyRole($this->display_rules['roles']);
        }

        // Logic for permissions (example: ['permissions' => ['view_dashboard']])
        if (isset($this->display_rules['permissions']) && is_array($this->display_rules['permissions'])) {
            if (! $user) {
                return false;
            }

            return $user->hasAnyPermission($this->display_rules['permissions']);
        }

        // Logic for authentication status
        if (isset($this->display_rules['auth'])) {
            if ($this->display_rules['auth'] === 'guest') {
                return ! $user;
            }

            if ($this->display_rules['auth'] === 'logged_in') {
                return (bool) $user;
            }
        }

        return true;
    }

    /**
     * Get the target attribute for the link.
     */
    public function getTarget(): string
    {
        return $this->target ?? ($this->target_blank ? '_blank' : '_self');
    }

    /**
     * Get the menu item with children (for tree building).
     */
    public function withChildren(bool $onlyActive = true, ?User $user = null): array
    {
        $data = $this->toArray();
        $data['url'] = $this->getUrl();

        if ($this->hasChildren()) {
            $query = $this->children();

            if ($onlyActive) {
                $query->where('is_active', true);
            }

            $data['children'] = $query->get()
                ->filter(fn ($child) => $child->isVisible($user))
                ->map(fn ($child) => $child->withChildren($onlyActive, $user))
                ->values()
                ->toArray();
        } else {
            $data['children'] = [];
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
