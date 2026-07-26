<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_no',
        'patient_id',
        'rch_pregnancy_id',
        'admission_id',
        'visit_id',
        'delivered_at',
        'delivery_type',
        'place',
        'gestational_weeks',
        'outcome',
        'babies_count',
        'mother_alive',
        'complications',
        'attendant_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'delivered_at' => 'datetime',
            'gestational_weeks' => 'integer',
            'babies_count' => 'integer',
            'mother_alive' => 'boolean',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function pregnancy(): BelongsTo
    {
        return $this->belongsTo(RchPregnancy::class, 'rch_pregnancy_id');
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function attendant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attendant_id');
    }

    public function newborns(): HasMany
    {
        return $this->hasMany(Newborn::class);
    }

    public function pncVisits(): HasMany
    {
        return $this->hasMany(RchPncVisit::class);
    }
}
