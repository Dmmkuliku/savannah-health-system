<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MedicineController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $medicines = Medicine::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('generic_name', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('medicines.index', [
            'medicines' => $medicines,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('medicines.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:medicines,code'],
            'name' => ['required', 'string', 'max:255'],
            'generic_name' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'form' => ['nullable', 'string', 'max:100'],
            'strength' => ['nullable', 'string', 'max:100'],
            'unit' => ['nullable', 'string', 'max:30'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'stock_qty' => ['nullable', 'integer', 'min:0'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
            'expiry_date' => ['nullable', 'date', 'after:today'],
            'batch_no' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        try {
            $medicine = Medicine::create([
                ...$validated,
                'stock_qty' => $validated['stock_qty'] ?? 0,
                'reorder_level' => $validated['reorder_level'] ?? 10,
                'unit' => $validated['unit'] ?? 'tablet',
                'is_active' => $request->boolean('is_active', true),
            ]);

            return redirect()
                ->route('medicines.index')
                ->with('success', 'Medicine added successfully.');
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to add medicine.']);
        }
    }

    public function edit(Medicine $medicine): View
    {
        return view('medicines.edit', compact('medicine'));
    }

    public function update(Request $request, Medicine $medicine): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:medicines,code,'.$medicine->id],
            'name' => ['required', 'string', 'max:255'],
            'generic_name' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'form' => ['nullable', 'string', 'max:100'],
            'strength' => ['nullable', 'string', 'max:100'],
            'unit' => ['nullable', 'string', 'max:30'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
            'expiry_date' => ['nullable', 'date'],
            'batch_no' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        try {
            $medicine->update([
                ...$validated,
                'is_active' => $request->boolean('is_active', true),
            ]);

            return redirect()
                ->route('medicines.index')
                ->with('success', 'Medicine updated successfully.');
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to update medicine.']);
        }
    }

    public function stockIn(Request $request, Medicine $medicine): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'batch_no' => ['nullable', 'string', 'max:50'],
            'expiry_date' => ['nullable', 'date', 'after:today'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            DB::transaction(function () use ($validated, $medicine) {
                $medicine->lockForUpdate();
                $medicine->increment('stock_qty', $validated['quantity']);

                if (! empty($validated['batch_no'])) {
                    $medicine->update(['batch_no' => $validated['batch_no']]);
                }

                if (! empty($validated['expiry_date'])) {
                    $medicine->update(['expiry_date' => $validated['expiry_date']]);
                }

                $medicine->refresh();

                StockMovement::create([
                    'medicine_id' => $medicine->id,
                    'type' => 'in',
                    'quantity' => $validated['quantity'],
                    'balance_after' => $medicine->stock_qty,
                    'reference' => 'STOCK-IN',
                    'notes' => $validated['notes'] ?? 'Stock received',
                    'user_id' => auth()->id(),
                ]);
            });

            return back()->with('success', 'Stock added successfully.');
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to add stock.']);
        }
    }
}
