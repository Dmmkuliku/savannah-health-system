<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Dispensing;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PharmacyController extends Controller
{
    public function index(): View
    {
        $prescriptions = Prescription::query()
            ->with(['patient', 'visit', 'doctor', 'items.medicine'])
            ->whereIn('status', ['pending', 'partial'])
            ->latest()
            ->paginate(20);

        return view('pharmacy.index', compact('prescriptions'));
    }

    public function show(Prescription $prescription): View
    {
        $prescription->load(['patient', 'visit', 'doctor', 'items.medicine', 'dispensings']);

        return view('pharmacy.show', compact('prescription'));
    }

    public function dispense(Request $request, Prescription $prescription): RedirectResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.prescription_item_id' => ['required', 'exists:prescription_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            DB::transaction(function () use ($validated, $prescription) {
                foreach ($validated['items'] as $dispenseData) {
                    /** @var PrescriptionItem $item */
                    $item = $prescription->items()->findOrFail($dispenseData['prescription_item_id']);
                    $quantity = (int) $dispenseData['quantity'];
                    $remaining = $item->quantity - $item->quantity_dispensed;

                    if ($quantity > $remaining) {
                        throw new \RuntimeException("Cannot dispense more than prescribed for {$item->medicine->name}.");
                    }

                    /** @var Medicine $medicine */
                    $medicine = Medicine::lockForUpdate()->findOrFail($item->medicine_id);

                    if ($medicine->stock_qty < $quantity) {
                        throw new \RuntimeException("Insufficient stock for {$medicine->name}. Available: {$medicine->stock_qty}.");
                    }

                    $medicine->decrement('stock_qty', $quantity);
                    $medicine->refresh();

                    StockMovement::create([
                        'medicine_id' => $medicine->id,
                        'type' => 'out',
                        'quantity' => -$quantity,
                        'balance_after' => $medicine->stock_qty,
                        'reference' => $prescription->prescription_no,
                        'notes' => 'Dispensed to patient',
                        'user_id' => auth()->id(),
                    ]);

                    $totalPrice = $item->unit_price * $quantity;

                    Dispensing::create([
                        'prescription_id' => $prescription->id,
                        'prescription_item_id' => $item->id,
                        'medicine_id' => $medicine->id,
                        'pharmacist_id' => auth()->id(),
                        'quantity' => $quantity,
                        'total_price' => $totalPrice,
                    ]);

                    $item->increment('quantity_dispensed', $quantity);
                }

                $prescription->load('items');
                $allDispensed = $prescription->items->every(
                    fn (PrescriptionItem $item) => $item->quantity_dispensed >= $item->quantity
                );
                $anyDispensed = $prescription->items->contains(
                    fn (PrescriptionItem $item) => $item->quantity_dispensed > 0
                );

                $prescription->update([
                    'status' => $allDispensed ? 'dispensed' : ($anyDispensed ? 'partial' : 'pending'),
                ]);

                if ($allDispensed && $prescription->visit) {
                    $prescription->visit->update(['status' => 'billing']);
                }
            });

            return redirect()
                ->route('pharmacy.show', $prescription)
                ->with('success', 'Medicines dispensed successfully.');
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to dispense medicines.']);
        }
    }
}
