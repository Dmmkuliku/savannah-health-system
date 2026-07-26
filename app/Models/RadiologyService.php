<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RadiologyService extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'modality',
        'price',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function radiologyOrders(): HasMany
    {
        return $this->hasMany(RadiologyOrder::class);
    }
}
