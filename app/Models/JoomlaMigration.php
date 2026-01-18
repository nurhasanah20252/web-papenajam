<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JoomlaMigration extends Model
{
    use HasFactory;

    /**
     * The table name.
     *
     * @var string
     */
    protected $table = 'joomla_migrations';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'source_table',
        'source_id',
        'target_id',
        'data_hash',
        'migration_status',
        'error_message',
        'migrated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'source_id' => 'integer',
            'target_id' => 'integer',
            'migration_status' => 'string',
            'migrated_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Scope query by source table.
     */
    public function scopeBySourceTable($query, string $table)
    {
        return $query->where('source_table', $table);
    }

    /**
     * Scope query by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('migration_status', $status);
    }

    /**
     * Scope query for successful migrations.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('migration_status', 'success');
    }

    /**
     * Scope query for failed migrations.
     */
    public function scopeFailed($query)
    {
        return $query->where('migration_status', 'failed');
    }

    /**
     * Check if migration was successful.
     */
    public function isSuccessful(): bool
    {
        return $this->migration_status === 'success';
    }

    /**
     * Check if migration failed.
     */
    public function isFailed(): bool
    {
        return $this->migration_status === 'failed';
    }
}
