<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Patient;
use Carbon\Carbon;

class ExemptionService
{
    /**
     * Resolve Tanzanian public-facility exemption eligibility.
     *
     * @return array{eligible: bool, type: ?string, reason: string, auto_applied: bool}
     */
    public function resolve(Patient $patient): array
    {
        $age = $this->ageYears($patient);

        if ($age !== null && $age < 5) {
            return [
                'eligible' => true,
                'type' => 'under_five',
                'reason' => 'Under 5 years (Msamaha wa chini ya miaka 5)',
                'auto_applied' => true,
            ];
        }

        if ($age !== null && $age >= 60) {
            return [
                'eligible' => true,
                'type' => 'elderly',
                'reason' => 'Elderly 60+ (Msamaha wa wazee)',
                'auto_applied' => true,
            ];
        }

        if ($patient->exemption_type === 'pregnant' || $patient->payment_category === 'exemption' && $patient->exemption_type === 'pregnant') {
            return [
                'eligible' => true,
                'type' => 'pregnant',
                'reason' => 'Pregnant woman (Msamaha wa wajawazito)',
                'auto_applied' => true,
            ];
        }

        if (in_array($patient->exemption_type, ['disability', 'staff', 'other'], true)) {
            return [
                'eligible' => true,
                'type' => $patient->exemption_type,
                'reason' => 'Registered exemption: '.$patient->exemption_type,
                'auto_applied' => true,
            ];
        }

        if ($patient->payment_category === 'exemption' && $patient->exemption_type) {
            return [
                'eligible' => true,
                'type' => $patient->exemption_type,
                'reason' => 'Patient marked as exempt',
                'auto_applied' => true,
            ];
        }

        return [
            'eligible' => false,
            'type' => null,
            'reason' => 'Not eligible for automatic exemption',
            'auto_applied' => false,
        ];
    }

    public function applyToPatient(Patient $patient, bool $forcePregnant = false): array
    {
        if ($forcePregnant) {
            $patient->update([
                'payment_category' => 'exemption',
                'exemption_type' => 'pregnant',
            ]);

            return $this->resolve($patient->fresh());
        }

        $result = $this->resolve($patient);

        if ($result['eligible'] && $result['type']) {
            $patient->update([
                'payment_category' => 'exemption',
                'exemption_type' => $result['type'],
            ]);
        }

        return $result;
    }

    public function shouldWaiveFees(Patient $patient): bool
    {
        return $this->resolve($patient)['eligible'];
    }

    private function ageYears(Patient $patient): ?int
    {
        if ($patient->date_of_birth) {
            return Carbon::parse($patient->date_of_birth)->age;
        }

        return $patient->age_years !== null ? (int) $patient->age_years : null;
    }
}
