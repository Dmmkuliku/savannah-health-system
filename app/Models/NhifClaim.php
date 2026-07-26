<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NhifClaim extends Model
{
    use HasFactory;

    protected $fillable = [
        'claim_no',
        'nhif_claim_batch_id',
        'patient_id',
        'visit_id',
        'invoice_id',
        'nhif_authorization_id',
        'card_no',
        'diagnosis',
        'amount',
        'status',
        'submitted_at',
        'items_snapshot',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'submitted_at' => 'datetime',
            'items_snapshot' => 'array',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(NhifClaimBatch::class, 'nhif_claim_batch_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function authorization(): BelongsTo
    {
        return $this->belongsTo(NhifAuthorization::class, 'nhif_authorization_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
