<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RchPncVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'delivery_id',
        'visit_id',
        'visit_date',
        'days_postpartum',
        'mother_condition',
        'baby_condition',
        'breastfeeding',
        'family_planning_counselled',
        'notes',
        'attended_by',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'days_postpartum' => 'integer',
            'breastfeeding' => 'boolean',
            'family_planning_counselled' => 'boolean',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
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
