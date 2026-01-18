<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PpidRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'applicant_name',
        'nik',
        'address',
        'phone',
        'email',
        'request_type',
        'subject',
        'description',
        'status',
        'response',
        'responded_at',
        'processed_by',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    /**
     * Get the processor.
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
