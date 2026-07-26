<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'mrn',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'date_of_birth',
        'age_years',
        'phone',
        'email',
        'national_id',
        'nhif_card_no',
        'nhif_member_name',
        'nhif_status',
        'nhif_verified_at',
        'nhif_response',
        'payment_category',
        'exemption_type',
        'next_of_kin',
        'next_of_kin_phone',
        'region',
        'district',
        'ward_village',
        'street_address',
        'occupation',
        'marital_status',
        'blood_group',
        'allergies',
        'chronic_conditions',
        'registered_by',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'age_years' => 'integer',
            'nhif_verified_at' => 'datetime',
            'nhif_response' => 'array',
        ];
    }

    protected function fullName(): Attribute
    {
        return Attribute::get(function (): string {
            return trim(collect([
                $this->first_name,
                $this->middle_name,
                $this->last_name,
            ])->filter()->implode(' '));
        });
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function admissions(): HasMany
    {
        return $this->hasMany(Admission::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
