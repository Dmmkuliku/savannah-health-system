<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BloodRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_no',
        'patient_id',
        'visit_id',
        'theatre_case_id',
        'requested_by',
        'blood_group',
        'component',
        'units_requested',
        'units_issued',
        'priority',
        'status',
        'indication',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'units_requested' => 'integer',
            'units_issued' => 'integer',
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

    public function theatreCase(): BelongsTo
    {
        return $this->belongsTo(TheatreCase::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(BloodIssue::class);
    }
}
