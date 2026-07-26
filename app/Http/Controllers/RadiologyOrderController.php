<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\RadiologyOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RadiologyOrderController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'pending');

        $orders = RadiologyOrder::query()
            ->with(['patient', 'visit', 'radiologyService', 'orderedBy'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('radiology.index', [
            'orders' => $orders,
            'status' => $status,
        ]);
    }

    public function show(RadiologyOrder $radiologyOrder): View
    {
        $radiologyOrder->load(['patient', 'visit', 'radiologyService', 'orderedBy', 'reportedBy']);

        return view('radiology.show', compact('radiologyOrder'));
    }

    public function updateReport(Request $request, RadiologyOrder $radiologyOrder): RedirectResponse
    {
        $validated = $request->validate([
            'findings' => ['required', 'string'],
            'impression' => ['required', 'string'],
            'status' => ['nullable', 'in:in_progress,completed'],
        ]);

        try {
            $radiologyOrder->update([
                'findings' => $validated['findings'],
                'impression' => $validated['impression'],
                'status' => $validated['status'] ?? 'completed',
                'reported_by' => auth()->id(),
                'completed_at' => now(),
            ]);

            if ($radiologyOrder->visit && $radiologyOrder->visit->status === 'investigations') {
                $radiologyOrder->visit->update(['status' => 'billing']);
            }

            return redirect()
                ->route('radiology.orders.show', $radiologyOrder)
                ->with('success', 'Radiology report saved successfully.');
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to save radiology report.']);
        }
    }
}
