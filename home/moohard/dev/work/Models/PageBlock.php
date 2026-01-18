<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PageBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_id',
        'type',
        'content',
        'order',
        'parent_id',
    ];

    protected $casts = [
        'content' => 'array',
        'order' => 'integer',
    ];

    /**
     * Get the page this block belongs to.
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * Get the parent block.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Get child blocks.
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('order');
    }
}
