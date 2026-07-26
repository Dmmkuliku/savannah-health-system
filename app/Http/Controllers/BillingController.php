<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Dispensing;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LabOrderItem;
use App\Models\Payment;
use App\Models\PrescriptionItem;
use App\Models\RadiologyOrder;
use App\Models\ServiceCharge;
use App\Models\Visit;
use App\Services\ExemptionService;
use App\Support\Hospital;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $invoices = Invoice::query()
            ->with(['patient', 'visit'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('billing.index', [
            'invoices' => $invoices,
            'status' => $status,
            'paymentCategories' => Hospital::paymentCategories(),
        ]);
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['patient', 'visit', 'items', 'payments.receivedBy', 'createdBy', 'gepgBills']);

        return view('billing.show', compact('invoice'));
    }

    public function createFromVisit(Visit $visit): RedirectResponse
    {
        try {
            $invoice = DB::transaction(function () use ($visit) {
                $existing = Invoice::where('visit_id', $visit->id)
                    ->whereNotIn('status', ['cancelled', 'paid', 'waived'])
                    ->first();

                if ($existing) {
                    return $existing;
                }

                $items = [];
                $subtotal = 0.0;

                $consultationCharge = ServiceCharge::where('code', 'CONS-OPD')->first();
                if ($consultationCharge && $visit->consultation) {
                    $items[] = [
                        'item_type' => 'service_charge',
                        'item_id' => $consultationCharge->id,
                        'description' => $consultationCharge->name,
                        'quantity' => 1,
                        'unit_price' => (float) $consultationCharge->price,
                        'total' => (float) $consultationCharge->price,
                    ];
                    $subtotal += (float) $consultationCharge->price;
                }

                $labItems = LabOrderItem::query()
                    ->whereHas('labOrder', fn ($q) => $q->where('visit_id', $visit->id)->where('status', 'completed'))
                    ->with('labTest')
                    ->get();

                foreach ($labItems as $labItem) {
                    $items[] = [
                        'item_type' => 'lab_test',
                        'item_id' => $labItem->lab_test_id,
                        'description' => 'Lab: '.$labItem->labTest->name,
                        'quantity' => 1,
                        'unit_price' => (float) $labItem->price,
                        'total' => (float) $labItem->price,
                    ];
                    $subtotal += (float) $labItem->price;
                }

                $radiologyOrders = RadiologyOrder::where('visit_id', $visit->id)
                    ->where('status', 'completed')
                    ->with('radiologyService')
                    ->get();

                foreach ($radiologyOrders as $radOrder) {
                    $items[] = [
                        'item_type' => 'radiology',
                        'item_id' => $radOrder->radiology_service_id,
                        'description' => 'Radiology: '.$radOrder->radiologyService->name,
                        'quantity' => 1,
                        'unit_price' => (float) $radOrder->price,
                        'total' => (float) $radOrder->price,
                    ];
                    $subtotal += (float) $radOrder->price;
                }

                $prescriptionItems = PrescriptionItem::query()
                    ->whereHas('prescription', fn ($q) => $q->where('visit_id', $visit->id))
                    ->where('quantity_dispensed', '>', 0)
                    ->with('medicine')
                    ->get();

                foreach ($prescriptionItems as $rxItem) {
                    $lineTotal = (float) $rxItem->unit_price * $rxItem->quantity_dispensed;
                    $items[] = [
                        'item_type' => 'medicine',
                        'item_id' => $rxItem->medicine_id,
                        'description' => 'Pharmacy: '.$rxItem->medicine->name,
                        'quantity' => $rxItem->quantity_dispensed,
                        'unit_price' => (float) $rxItem->unit_price,
                        'total' => $lineTotal,
                    ];
                    $subtotal += $lineTotal;
                }

                $dispensings = Dispensing::query()
                    ->whereHas('prescription', fn ($q) => $q->where('visit_id', $visit->id))
                    ->with('medicine')
                    ->get();

                $dispensedMedicineIds = $prescriptionItems->pluck('medicine_id')->all();
                foreach ($dispensings as $dispensing) {
                    if (in_array($dispensing->medicine_id, $dispensedMedicineIds, true)) {
                        continue;
                    }

                    $items[] = [
                        'item_type' => 'medicine',
                        'item_id' => $dispensing->medicine_id,
                        'description' => 'Pharmacy: '.$dispensing->medicine->name,
                        'quantity' => $dispensing->quantity,
                        'unit_price' => (float) ($dispensing->total_price / max($dispensing->quantity, 1)),
                        'total' => (float) $dispensing->total_price,
                    ];
                    $subtotal += (float) $dispensing->total_price;
                }

                if (empty($items)) {
                    throw new \RuntimeException('No billable items found for this visit.');
                }

                $visit->loadMissing('patient');
                $exemption = app(ExemptionService::class)->applyToPatient($visit->patient);
                $waive = $exemption['eligible'];
                $paymentCategory = $waive ? 'exemption' : $visit->payment_category;

                $invoice = Invoice::create([
                    'invoice_no' => Hospital::nextNumber('INV'),
                    'visit_id' => $visit->id,
                    'patient_id' => $visit->patient_id,
                    'payment_category' => $paymentCategory,
                    'subtotal' => $subtotal,
                    'discount' => $waive ? $subtotal : 0,
                    'total' => $waive ? 0 : $subtotal,
                    'paid_amount' => 0,
                    'balance' => $waive ? 0 : $subtotal,
                    'status' => $waive ? 'waived' : 'unpaid',
                    'created_by' => auth()->id(),
                ]);

                foreach ($items as $item) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        ...$item,
                        'description' => $item['description'].($waive ? ' (Exempt / Msamaha)' : ''),
                    ]);
                }

                $visit->update(['status' => $waive ? 'completed' : 'billing']);

                return $invoice;
            });

            $msg = 'Invoice '.$invoice->invoice_no.' created from visit charges.';
            if ($invoice->status === 'waived') {
                $msg .= ' Fees waived under government exemption (Msamaha).';
            }

            return redirect()
                ->route('billing.show', $invoice)
                ->with('success', $msg);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Failed to create invoice from visit.']);
        }
    }

    public function storePayment(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'method' => ['required', 'in:cash,mobile_money,bank,nhif,exemption,card'],
            'reference' => ['nullable', 'string', 'max:100'],
            'mobile_provider' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        if ((float) $validated['amount'] > (float) $invoice->balance) {
            return back()->withInput()->withErrors(['amount' => 'Payment amount exceeds invoice balance.']);
        }

        try {
            DB::transaction(function () use ($validated, $invoice) {
                Payment::create([
                    'receipt_no' => Hospital::nextNumber('RCP'),
                    'invoice_id' => $invoice->id,
                    'patient_id' => $invoice->patient_id,
                    'amount' => $validated['amount'],
                    'method' => $validated['method'],
                    'reference' => $validated['reference'] ?? null,
                    'mobile_provider' => $validated['mobile_provider'] ?? null,
                    'received_by' => auth()->id(),
                    'notes' => $validated['notes'] ?? null,
                ]);

                $paidAmount = (float) $invoice->paid_amount + (float) $validated['amount'];
                $balance = max(0, (float) $invoice->total - $paidAmount);

                $status = match (true) {
                    $balance <= 0 => 'paid',
                    $paidAmount > 0 => 'partial',
                    default => 'unpaid',
                };

                $invoice->update([
                    'paid_amount' => $paidAmount,
                    'balance' => $balance,
                    'status' => $status,
                ]);

                if ($status === 'paid' && $invoice->visit) {
                    $invoice->visit->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]);
                }
            });

            return redirect()
                ->route('billing.show', $invoice)
                ->with('success', 'Payment recorded successfully.');
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to record payment.']);
        }
    }

    public function printReceipt(Payment $payment): View
    {
        $payment->load(['invoice.patient', 'invoice.items', 'receivedBy']);

        return view('billing.receipt', [
            'payment' => $payment,
            'facilityName' => Hospital::facilityName(),
        ]);
    }
}
