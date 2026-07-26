<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\LabOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LabOrderController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'pending');

        $orders = LabOrder::query()
            ->with(['patient', 'visit', 'orderedBy', 'items.labTest'])
            ->when($status === 'pending', fn ($q) => $q->whereIn('status', ['pending', 'sample_collected']))
            ->when($status === 'processing', fn ($q) => $q->where('status', 'processing'))
            ->when($status === 'completed', fn ($q) => $q->where('status', 'completed'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('lab.orders.index', [
            'orders' => $orders,
            'status' => $status,
        ]);
    }

    public function show(LabOrder $labOrder): View
    {
        $labOrder->load(['patient', 'visit', 'orderedBy', 'processedBy', 'items.labTest']);

        return view('lab.orders.show', compact('labOrder'));
    }

    public function updateResults(Request $request, LabOrder $labOrder): RedirectResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'exists:lab_order_items,id'],
            'items.*.result' => ['required', 'string', 'max:500'],
            'items.*.result_flag' => ['nullable', 'string', 'max:20'],
            'items.*.remarks' => ['nullable', 'string'],
        ]);

        try {
            DB::transaction(function () use ($validated, $labOrder) {
                foreach ($validated['items'] as $itemData) {
                    $item = $labOrder->items()->findOrFail($itemData['id']);

                    $item->update([
                        'result' => $itemData['result'],
                        'result_flag' => $itemData['result_flag'] ?? null,
                        'remarks' => $itemData['remarks'] ?? null,
                        'status' => 'completed',
                    ]);
                }

                $labOrder->update([
                    'status' => 'completed',
                    'processed_by' => auth()->id(),
                    'completed_at' => now(),
                ]);
            });

            return redirect()
                ->route('lab.orders.show', $labOrder)
                ->with('success', 'Lab results saved and order marked completed.');
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to save lab results.']);
        }
    }
}
