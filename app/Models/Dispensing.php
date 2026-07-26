<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dispensing extends Model
{
    use HasFactory;

    protected $fillable = [
        'prescription_id',
        'prescription_item_id',
        'medicine_id',
        'pharmacist_id',
        'quantity',
        'total_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'total_price' => 'decimal:2',
        ];
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    public function prescriptionItem(): BelongsTo
    {
        return $this->belongsTo(PrescriptionItem::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function pharmacist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pharmacist_id');
    }
}
