<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Visit extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_no',
        'patient_id',
        'visit_type',
        'department_id',
        'payment_category',
        'chief_complaint',
        'status',
        'doctor_id',
        'created_by',
        'visited_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function consultation(): HasOne
    {
        return $this->hasOne(Consultation::class);
    }

    public function vitalSigns(): HasMany
    {
        return $this->hasMany(VitalSign::class);
    }

    public function labOrders(): HasMany
    {
        return $this->hasMany(LabOrder::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }
}
