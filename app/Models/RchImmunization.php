<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RchImmunization extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'vaccine_code',
        'vaccine_name',
        'dose',
        'given_at',
        'batch_no',
        'next_due',
        'notes',
        'given_by',
    ];

    protected function casts(): array
    {
        return [
            'given_at' => 'date',
            'next_due' => 'date',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function givenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'given_by');
    }
}
