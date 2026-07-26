<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RadiologyOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_no',
        'visit_id',
        'patient_id',
        'radiology_service_id',
        'ordered_by',
        'reported_by',
        'status',
        'clinical_info',
        'findings',
        'impression',
        'price',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
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

    public function radiologyService(): BelongsTo
    {
        return $this->belongsTo(RadiologyService::class);
    }

    public function orderedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ordered_by');
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
