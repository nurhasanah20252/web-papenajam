<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'month',
        'case_type',
        'total_cases',
        'resolved_cases',
        'pending_cases',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'total_cases' => 'integer',
        'resolved_cases' => 'integer',
        'pending_cases' => 'integer',
    ];
}
