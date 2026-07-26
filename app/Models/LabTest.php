<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
        'sample_type',
        'price',
        'unit',
        'normal_range',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function labOrderItems(): HasMany
    {
        return $this->hasMany(LabOrderItem::class);
    }
}
