<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GepgBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'invoice_id',
        'patient_id',
        'amount',
        'control_number',
        'status',
        'payer_phone',
        'expires_at',
        'paid_at',
        'request_payload',
        'response_payload',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
            'request_payload' => 'array',
            'response_payload' => 'array',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
