<?php

namespace App\Models;

use App\Enums\BlockType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageBlock extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'page_id',
        'type',
        'content',
        'settings',
        'meta',
        'css_class',
        'anchor_id',
        'order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => BlockType::class,
            'content' => 'array',
            'settings' => 'array',
            'meta' => 'array',
            'order' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the page that owns this block.
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'page_id');
    }

    /**
     * Scope query by block type.
     */
    public function scopeByType($query, BlockType $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get the block's content as array.
     */
    public function getContent(): array
    {
        return $this->content ?? [];
    }

    /**
     * Get the block's settings as array.
     */
    public function getSettings(): array
    {
        return $this->settings ?? [];
    }
}
