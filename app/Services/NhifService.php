<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Patient;
use Illuminate\Support\Str;

/**
 * NHIF member verification stub.
 * Replace verify() body with live NHIF API client when credentials are available.
 */
class NhifService
{
    public function verify(Patient $patient, ?string $cardNo = null): array
    {
        $card = trim((string) ($cardNo ?: $patient->nhif_card_no));

        if ($card === '') {
            return [
                'success' => false,
                'status' => 'missing',
                'message' => 'NHIF card number is required.',
                'data' => null,
            ];
        }

        // Demo rule: cards starting with "99" are invalid; others valid.
        $valid = ! Str::startsWith($card, '99');

        $payload = [
            'card_no' => $card,
            'member_name' => $valid
                ? ($patient->full_name ?: 'NHIF Member')
                : null,
            'scheme' => $valid ? 'NHIF Formal Sector' : null,
            'authorization_code' => $valid ? 'AUTH-'.strtoupper(Str::random(8)) : null,
            'eligible' => $valid,
            'facility_code' => config('services.nhif.facility_code', 'MPT-DOD'),
            'verified_via' => 'stub',
            'verified_at' => now()->toIso8601String(),
        ];

        $patient->update([
            'nhif_card_no' => $card,
            'nhif_member_name' => $payload['member_name'],
            'nhif_status' => $valid ? 'active' : 'invalid',
            'nhif_verified_at' => now(),
            'nhif_response' => $payload,
            'payment_category' => $valid ? 'nhif' : $patient->payment_category,
        ]);

        return [
            'success' => $valid,
            'status' => $valid ? 'active' : 'invalid',
            'message' => $valid
                ? 'NHIF member verified successfully (stub).'
                : 'NHIF card rejected by stub verifier.',
            'data' => $payload,
        ];
    }
}
