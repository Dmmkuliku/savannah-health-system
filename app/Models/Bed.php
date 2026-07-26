<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bed extends Model
{
    use HasFactory;

    protected $fillable = [
        'ward_id',
        'bed_number',
        'status',
    ];

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }
}
