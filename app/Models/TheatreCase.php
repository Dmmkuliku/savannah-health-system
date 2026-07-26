<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TheatreCase extends Model
{
    use HasFactory;

    protected $fillable = [
        'case_no',
        'patient_id',
        'visit_id',
        'admission_id',
        'theatre_room_id',
        'surgeon_id',
        'anaesthetist_id',
        'procedure_name',
        'urgency',
        'asa_class',
        'status',
        'scheduled_at',
        'started_at',
        'ended_at',
        'diagnosis',
        'pre_op_notes',
        'operative_notes',
        'post_op_notes',
        'anaesthesia_type',
        'estimated_blood_loss_ml',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'estimated_blood_loss_ml' => 'decimal:1',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function theatreRoom(): BelongsTo
    {
        return $this->belongsTo(TheatreRoom::class);
    }

    public function surgeon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'surgeon_id');
    }

    public function anaesthetist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'anaesthetist_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bloodRequests(): HasMany
    {
        return $this->hasMany(BloodRequest::class);
    }
}
