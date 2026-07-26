<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_id',
        'patient_id',
        'doctor_id',
        'history_of_present_illness',
        'past_medical_history',
        'examination_findings',
        'diagnosis_summary',
        'icd10_codes',
        'treatment_plan',
        'advice',
        'outcome',
        'follow_up_date',
    ];

    protected function casts(): array
    {
        return [
            'follow_up_date' => 'date',
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

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}
