<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Newborn extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_id',
        'mother_id',
        'patient_id',
        'name',
        'sex',
        'birth_weight_kg',
        'apgar_1',
        'apgar_5',
        'status',
        'breastfeeding_initiated',
        'bcg_given',
        'opv0_given',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'birth_weight_kg' => 'decimal:3',
            'apgar_1' => 'integer',
            'apgar_5' => 'integer',
            'breastfeeding_initiated' => 'boolean',
            'bcg_given' => 'boolean',
            'opv0_given' => 'boolean',
        ];
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function mother(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'mother_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
