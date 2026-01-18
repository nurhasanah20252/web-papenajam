<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'file_path',
        'file_size',
        'mime_type',
        'category_id',
        'is_public',
        'download_count',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'is_public' => 'boolean',
        'download_count' => 'integer',
    ];

    /**
     * Get the category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the uploader.
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
