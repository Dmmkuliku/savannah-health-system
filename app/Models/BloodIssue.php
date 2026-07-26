<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BloodIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'blood_request_id',
        'blood_unit_id',
        'patient_id',
        'issued_by',
        'issued_at',
        'crossmatch_result',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
        ];
    }

    public function bloodRequest(): BelongsTo
    {
        return $this->belongsTo(BloodRequest::class);
    }

    public function bloodUnit(): BelongsTo
    {
        return $this->belongsTo(BloodUnit::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
