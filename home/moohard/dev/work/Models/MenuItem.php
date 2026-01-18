<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'parent_id',
        'title',
        'type',
        'url',
        'page_id',
        'order',
        'icon',
        'css_class',
        'target_blank',
        'is_active',
        'conditional_rules',
    ];

    protected $casts = [
        'order' => 'integer',
        'target_blank' => 'boolean',
        'is_active' => 'boolean',
        'conditional_rules' => 'array',
    ];

    /**
     * Get the menu this item belongs to.
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Get the parent item.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Get child items.
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('order');
    }

    /**
     * Get the linked page.
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * Move the item up in the ordering.
     */
    public function moveOrderUp(): int
    {
        $this->moveOrder(-1);

        return $this->order;
    }

    /**
     * Move the item down in the ordering.
     */
    public function moveOrderDown(): int
    {
        $this->moveOrder(1);

        return $this->order;
    }

    /**
     * Move the item by a given offset.
     */
    protected function moveOrder(int $offset): void
    {
        $item = $this;
        $sibling = static::where('menu_id', $item->menu_id)
            ->where('parent_id', $item->parent_id)
            ->where('order', $item->order + $offset)
            ->first();

        if ($sibling) {
            $sibling->update(['order' => $item->order]);
            $item->update(['order' => $item->order + $offset]);
        }
    }
}
