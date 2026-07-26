<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RchPregnancy extends Model
{
    use HasFactory;

    protected $fillable = [
        'anc_no',
        'patient_id',
        'lmp',
        'edd',
        'gravida',
        'para',
        'abortions',
        'blood_group',
        'hiv_tested',
        'hiv_status',
        'tt_given',
        'risk_factors',
        'status',
        'registered_by',
    ];

    protected function casts(): array
    {
        return [
            'lmp' => 'date',
            'edd' => 'date',
            'gravida' => 'integer',
            'para' => 'integer',
            'abortions' => 'integer',
            'hiv_tested' => 'boolean',
            'tt_given' => 'boolean',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function ancVisits(): HasMany
    {
        return $this->hasMany(RchAncVisit::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }
}
