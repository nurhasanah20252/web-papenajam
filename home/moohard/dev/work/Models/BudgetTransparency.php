<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetTransparency extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'title',
        'description',
        'amount',
        'category',
        'document_path',
    ];

    protected $casts = [
        'year' => 'integer',
        'amount' => 'decimal:2',
    ];
}
