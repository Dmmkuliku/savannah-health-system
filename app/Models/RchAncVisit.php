<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RchAncVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'rch_pregnancy_id',
        'patient_id',
        'visit_id',
        'visit_number',
        'visit_date',
        'gestational_weeks',
        'weight_kg',
        'bp_systolic',
        'bp_diastolic',
        'hb_gdl',
        'fetal_heart_heard',
        'urine_protein',
        'ipt_given',
        'iron_folate_given',
        'mosquito_net_given',
        'notes',
        'attended_by',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'visit_number' => 'integer',
            'gestational_weeks' => 'integer',
            'weight_kg' => 'decimal:2',
            'bp_systolic' => 'integer',
            'bp_diastolic' => 'integer',
            'hb_gdl' => 'decimal:1',
            'fetal_heart_heard' => 'boolean',
            'ipt_given' => 'boolean',
            'iron_folate_given' => 'boolean',
            'mosquito_net_given' => 'boolean',
        ];
    }

    public function pregnancy(): BelongsTo
    {
        return $this->belongsTo(RchPregnancy::class, 'rch_pregnancy_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function attendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attended_by');
    }
}
