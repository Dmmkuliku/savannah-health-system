<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use App\Models\NhifAuthorization;
use App\Models\NhifClaim;
use App\Models\NhifClaimBatch;
use App\Models\Patient;
use App\Models\Visit;
use App\Support\Hospital;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NhifClaimService
{
    public function requestAuthorization(Patient $patient, ?Visit $visit = null, ?string $diagnosis = null): NhifAuthorization
    {
        $card = trim((string) $patient->nhif_card_no);

        if ($card === '') {
            throw new \InvalidArgumentException('NHIF card number is required.');
        }

        $approved = ! Str::startsWith($card, '99');
        $authCode = $approved ? 'AUTH-'.strtoupper(Str::random(8)) : null;
        $approvedAmount = $approved ? 50000.00 : null;
        $expiresAt = $approved ? now()->addDays(7) : null;

        $payload = [
            'card_no' => $card,
            'member_name' => $approved ? ($patient->full_name ?: 'NHIF Member') : null,
            'scheme' => $approved ? 'NHIF Formal Sector' : null,
            'authorization_code' => $authCode,
            'eligible' => $approved,
            'facility_code' => config('services.nhif.facility_code', 'MPT-DOD'),
            'verified_via' => 'stub',
            'requested_at' => now()->toIso8601String(),
        ];

        $patient->update([
            'nhif_card_no' => $card,
            'nhif_member_name' => $payload['member_name'],
            'nhif_status' => $approved ? 'active' : 'invalid',
            'nhif_verified_at' => now(),
            'nhif_response' => $payload,
            'payment_category' => $approved ? 'nhif' : $patient->payment_category,
        ]);

        return NhifAuthorization::create([
            'auth_no' => Hospital::nextNumber('NA'),
            'patient_id' => $patient->id,
            'visit_id' => $visit?->id,
            'card_no' => $card,
            'authorization_code' => $authCode,
            'diagnosis' => $diagnosis,
            'status' => $approved ? 'approved' : 'rejected',
            'approved_amount' => $approvedAmount,
            'approved_at' => $approved ? now() : null,
            'expires_at' => $expiresAt,
            'response_payload' => $payload,
            'requested_by' => Auth::id(),
        ]);
    }

    public function createClaimFromInvoice(Invoice $invoice): NhifClaim
    {
        $invoice->loadMissing(['patient', 'visit.consultation', 'items']);

        $patient = $invoice->patient;

        if (! $patient || trim((string) $patient->nhif_card_no) === '') {
            throw new \InvalidArgumentException('Patient must have a valid NHIF card to create a claim.');
        }

        $existing = NhifClaim::query()
            ->where('invoice_id', $invoice->id)
            ->whereNotIn('status', ['rejected'])
            ->first();

        if ($existing) {
            return $existing;
        }

        $authorization = NhifAuthorization::query()
            ->where('patient_id', $patient->id)
            ->where('status', 'approved')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->first();

        $diagnosis = $invoice->visit?->consultation?->diagnosis_summary;

        $itemsSnapshot = $invoice->items->map(fn ($item) => [
            'description' => $item->description,
            'item_type' => $item->item_type,
            'quantity' => $item->quantity,
            'unit_price' => (float) $item->unit_price,
            'total' => (float) $item->total,
        ])->values()->all();

        return NhifClaim::create([
            'claim_no' => Hospital::nextNumber('CLM'),
            'patient_id' => $patient->id,
            'visit_id' => $invoice->visit_id,
            'invoice_id' => $invoice->id,
            'nhif_authorization_id' => $authorization?->id,
            'card_no' => $patient->nhif_card_no,
            'diagnosis' => $diagnosis,
            'amount' => (float) $invoice->total,
            'status' => 'ready',
            'items_snapshot' => $itemsSnapshot,
            'created_by' => Auth::id(),
        ]);
    }

    public function addClaimsToBatch(array $claimIds, string $periodFrom, string $periodTo): NhifClaimBatch
    {
        return DB::transaction(function () use ($claimIds, $periodFrom, $periodTo) {
            $claims = NhifClaim::query()
                ->whereIn('id', $claimIds)
                ->where('status', 'ready')
                ->whereNull('nhif_claim_batch_id')
                ->lockForUpdate()
                ->get();

            if ($claims->isEmpty()) {
                throw new \InvalidArgumentException('No ready claims available for batching.');
            }

            $batch = NhifClaimBatch::create([
                'batch_no' => Hospital::nextNumber('BAT'),
                'period_from' => $periodFrom,
                'period_to' => $periodTo,
                'status' => 'open',
                'claims_count' => 0,
                'total_amount' => 0,
                'created_by' => Auth::id(),
            ]);

            NhifClaim::whereIn('id', $claims->pluck('id'))
                ->update(['nhif_claim_batch_id' => $batch->id]);

            $this->refreshBatchTotals($batch);

            return $batch->fresh(['claims.patient']);
        });
    }

    public function submitBatch(NhifClaimBatch $batch): NhifClaimBatch
    {
        return DB::transaction(function () use ($batch) {
            if ($batch->status !== 'open') {
                throw new \InvalidArgumentException('Only open batches can be submitted.');
            }

            $batch->update([
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

            NhifClaim::query()
                ->where('nhif_claim_batch_id', $batch->id)
                ->update([
                    'status' => 'submitted',
                    'submitted_at' => now(),
                ]);

            return $batch->fresh(['claims.patient']);
        });
    }

    protected function refreshBatchTotals(NhifClaimBatch $batch): void
    {
        $totals = NhifClaim::query()
            ->where('nhif_claim_batch_id', $batch->id)
            ->selectRaw('COUNT(*) as claims_count, COALESCE(SUM(amount), 0) as total_amount')
            ->first();

        $batch->update([
            'claims_count' => (int) ($totals->claims_count ?? 0),
            'total_amount' => (float) ($totals->total_amount ?? 0),
        ]);
    }
}
