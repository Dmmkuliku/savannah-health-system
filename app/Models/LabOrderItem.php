<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_order_id',
        'lab_test_id',
        'result',
        'result_flag',
        'remarks',
        'status',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function labOrder(): BelongsTo
    {
        return $this->belongsTo(LabOrder::class);
    }

    public function labTest(): BelongsTo
    {
        return $this->belongsTo(LabTest::class);
    }
}
