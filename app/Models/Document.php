<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'file_path',
        'file_name',
        'file_size',
        'file_type',
        'mime_type',
        'category_id',
        'uploaded_by',
        'download_count',
        'is_public',
        'published_at',
        'version',
        'checksum',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'download_count' => 'integer',
            'is_public' => 'boolean',
            'published_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the category of the document.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get the uploader of the document.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get all versions of the document.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the current version of the document.
     */
    public function currentVersion(): HasMany
    {
        return $this->hasMany(DocumentVersion::class)->where('is_current', true);
    }

    /**
     * Increment download count.
     */
    public function incrementDownloads(): void
    {
        $this->increment('download_count');
    }

    /**
     * Get file size in human readable format.
     */
    public function getHumanFileSize(): string
    {
        $size = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, 2).' '.$units[$i];
    }

    /**
     * Get the file URL.
     */
    public function getFileUrl(): string
    {
        return asset('storage/'.$this->file_path);
    }

    /**
     * Check if document is downloadable.
     */
    public function isDownloadable(): bool
    {
        return $this->is_public
            && ($this->published_at === null || $this->published_at->isPast());
    }

    /**
     * Scope query for public documents.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope query by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Validate file checksum.
     */
    public function validateChecksum(string $filePath): bool
    {
        if (! $this->checksum) {
            return true;
        }

        $currentChecksum = hash_file('sha256', $filePath);

        return $currentChecksum === $this->checksum;
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($document) {
            if (empty($document->slug)) {
                $document->slug = Str::slug($document->title);
                $originalSlug = $document->slug;
                $counter = 1;

                while (static::withTrashed()->where('slug', $document->slug)->exists()) {
                    $document->slug = $originalSlug.'-'.$counter++;
                }
            }
        });

        static::updating(function ($document) {
            if ($document->isDirty('title') && empty($document->slug)) {
                $document->slug = Str::slug($document->title);
            }
        });
    }
}
