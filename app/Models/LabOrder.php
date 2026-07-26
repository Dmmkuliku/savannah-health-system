<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_no',
        'visit_id',
        'patient_id',
        'ordered_by',
        'processed_by',
        'priority',
        'status',
        'clinical_notes',
        'collected_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'collected_at' => 'datetime',
            'completed_at' => 'datetime',
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

    public function orderedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ordered_by');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(LabOrderItem::class);
    }
}
