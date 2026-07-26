<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BloodUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_no',
        'blood_group',
        'component',
        'volume_ml',
        'donor_name',
        'collected_at',
        'expiry_date',
        'status',
        'storage_location',
        'notes',
        'received_by',
    ];

    protected function casts(): array
    {
        return [
            'collected_at' => 'date',
            'expiry_date' => 'date',
            'volume_ml' => 'integer',
        ];
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function bloodIssues(): HasMany
    {
        return $this->hasMany(BloodIssue::class);
    }
}
