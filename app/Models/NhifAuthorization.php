<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NhifAuthorization extends Model
{
    use HasFactory;

    protected $fillable = [
        'auth_no',
        'patient_id',
        'visit_id',
        'card_no',
        'authorization_code',
        'diagnosis',
        'status',
        'approved_amount',
        'approved_at',
        'expires_at',
        'response_payload',
        'requested_by',
    ];

    protected function casts(): array
    {
        return [
            'approved_amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'expires_at' => 'datetime',
            'response_payload' => 'array',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(NhifClaim::class);
    }
}
