<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'generic_name',
        'category',
        'form',
        'strength',
        'unit',
        'unit_price',
        'stock_qty',
        'reorder_level',
        'expiry_date',
        'batch_no',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'stock_qty' => 'integer',
            'reorder_level' => 'integer',
            'expiry_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function prescriptionItems(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
