<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GepgBill;
use App\Models\Invoice;
use App\Models\Payment;
use App\Support\Hospital;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Government Electronic Payment Gateway (GePG) stub for Tanzania.
 * Swap generateControlNumber() / confirmPayment() with live GePG XML/API calls.
 */
class GepgService
{
    public function generateBill(Invoice $invoice, ?string $payerPhone = null): GepgBill
    {
        return DB::transaction(function () use ($invoice, $payerPhone) {
            $amount = (float) $invoice->balance;
            if ($amount <= 0) {
                throw new \RuntimeException('Invoice has no outstanding balance.');
            }

            $billId = 'SHS'.now()->format('ymdHis').random_int(10, 99);
            // Demo control numbers look like real GePG patterns (digits only).
            $controlNo = '99'.now()->format('ymd').str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $request = [
                'BillId' => $billId,
                'Amt' => $amount,
                'Ccy' => 'TZS',
                'PyrName' => $invoice->patient->full_name,
                'PyrCellNum' => $payerPhone,
                'BillDesc' => 'Hospital services - '.$invoice->invoice_no,
                'FacilityCode' => config('services.gepg.facility_code', 'MPT001'),
            ];

            $response = [
                'TrxStsCode' => '7101',
                'PayCtrNum' => $controlNo,
                'message' => 'Control number generated via GePG stub',
            ];

            return GepgBill::create([
                'bill_id' => $billId,
                'invoice_id' => $invoice->id,
                'patient_id' => $invoice->patient_id,
                'amount' => $amount,
                'control_number' => $controlNo,
                'status' => 'pending',
                'payer_phone' => $payerPhone,
                'expires_at' => now()->addDays(7),
                'request_payload' => $request,
                'response_payload' => $response,
                'created_by' => auth()->id(),
            ]);
        });
    }

    public function simulatePayment(GepgBill $bill): Payment
    {
        if ($bill->status === 'paid') {
            throw new \RuntimeException('GePG bill already paid.');
        }

        return DB::transaction(function () use ($bill) {
            $invoice = $bill->invoice()->lockForUpdate()->firstOrFail();
            $amount = min((float) $bill->amount, (float) $invoice->balance);

            $payment = Payment::create([
                'receipt_no' => Hospital::nextNumber('RCP'),
                'invoice_id' => $invoice->id,
                'patient_id' => $invoice->patient_id,
                'amount' => $amount,
                'method' => 'mobile_money',
                'reference' => $bill->control_number,
                'mobile_provider' => 'GePG',
                'gepg_control_no' => $bill->control_number,
                'gepg_status' => 'paid',
                'gepg_paid_at' => now(),
                'gepg_response' => [
                    'TrxId' => 'GEPG-'.strtoupper(Str::random(10)),
                    'PayCtrNum' => $bill->control_number,
                    'PaidAmt' => $amount,
                    'via' => 'stub',
                ],
                'received_by' => auth()->id(),
                'notes' => 'Paid via GePG control number '.$bill->control_number,
            ]);

            $paid = (float) $invoice->paid_amount + $amount;
            $balance = max(0, (float) $invoice->total - $paid);

            $invoice->update([
                'paid_amount' => $paid,
                'balance' => $balance,
                'status' => $balance <= 0 ? 'paid' : 'partial',
            ]);

            $bill->update([
                'status' => 'paid',
                'paid_at' => now(),
                'response_payload' => array_merge($bill->response_payload ?? [], [
                    'payment_id' => $payment->id,
                    'paid_at' => now()->toIso8601String(),
                ]),
            ]);

            return $payment;
        });
    }
}
