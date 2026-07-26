<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\GepgBill;
use App\Models\Invoice;
use App\Services\GepgService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GepgController extends Controller
{
    public function generate(Request $request, Invoice $invoice, GepgService $gepg): RedirectResponse
    {
        $validated = $request->validate([
            'payer_phone' => ['nullable', 'string', 'max:20'],
        ]);

        try {
            $bill = $gepg->generateBill($invoice, $validated['payer_phone'] ?? $invoice->patient->phone);

            return back()->with('success', 'GePG control number generated: '.$bill->control_number);
        } catch (\Throwable $e) {
            return back()->withErrors(['gepg' => $e->getMessage()]);
        }
    }

    public function simulatePay(GepgBill $gepgBill, GepgService $gepg): RedirectResponse
    {
        try {
            $payment = $gepg->simulatePayment($gepgBill);

            return redirect()
                ->route('billing.show', $gepgBill->invoice_id)
                ->with('success', 'GePG payment recorded. Receipt '.$payment->receipt_no);
        } catch (\Throwable $e) {
            return back()->withErrors(['gepg' => $e->getMessage()]);
        }
    }
}
