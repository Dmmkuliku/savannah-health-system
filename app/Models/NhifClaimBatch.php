<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NhifClaimBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_no',
        'period_from',
        'period_to',
        'status',
        'claims_count',
        'total_amount',
        'submitted_at',
        'created_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'period_from' => 'date',
            'period_to' => 'date',
            'claims_count' => 'integer',
            'total_amount' => 'decimal:2',
            'submitted_at' => 'datetime',
        ];
    }

    public function claims(): HasMany
    {
        return $this->hasMany(NhifClaim::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
