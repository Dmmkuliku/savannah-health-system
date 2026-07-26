<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VitalSign extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_id',
        'patient_id',
        'recorded_by',
        'temperature_c',
        'pulse',
        'respiratory_rate',
        'bp_systolic',
        'bp_diastolic',
        'spo2',
        'weight_kg',
        'height_cm',
        'bmi',
        'pain_score',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'temperature_c' => 'decimal:1',
            'pulse' => 'integer',
            'respiratory_rate' => 'integer',
            'bp_systolic' => 'integer',
            'bp_diastolic' => 'integer',
            'spo2' => 'integer',
            'weight_kg' => 'decimal:2',
            'height_cm' => 'decimal:2',
            'bmi' => 'decimal:2',
            'pain_score' => 'integer',
        ];
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
