<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourtSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'case_number',
        'case_title',
        'case_type',
        'judge_name',
        'court_room',
        'scheduled_date',
        'scheduled_time',
        'status',
        'agenda',
        'notes',
        'sipp_case_id',
        'sync_status',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'scheduled_time' => 'time',
    ];
}
