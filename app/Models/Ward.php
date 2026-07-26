<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ward extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'gender',
        'capacity',
        'department_id',
        'daily_rate',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'daily_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function beds(): HasMany
    {
        return $this->hasMany(Bed::class);
    }

    public function admissions(): HasMany
    {
        return $this->hasMany(Admission::class);
    }
}
