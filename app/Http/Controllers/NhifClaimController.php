<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\NhifAuthorization;
use App\Models\NhifClaim;
use App\Models\NhifClaimBatch;
use App\Models\Patient;
use App\Models\Visit;
use App\Services\NhifClaimService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NhifClaimController extends Controller
{
    public function index(): View
    {
        $authorizations = NhifAuthorization::query()
            ->with(['patient', 'visit'])
            ->latest()
            ->limit(20)
            ->get();

        $claims = NhifClaim::query()
            ->with(['patient', 'visit', 'invoice', 'batch'])
            ->latest()
            ->limit(30)
            ->get();

        $openBatches = NhifClaimBatch::query()
            ->with(['createdBy'])
            ->where('status', 'open')
            ->latest()
            ->get();

        return view('nhif.index', compact('authorizations', 'claims', 'openBatches'));
    }

    public function authorizeForm(Request $request): View
    {
        $patients = Patient::query()
            ->orderBy('first_name')
            ->limit(200)
            ->get();

        $visit = null;
        if ($request->filled('visit_id')) {
            $visit = Visit::with(['patient', 'consultation'])->find($request->query('visit_id'));
        }

        return view('nhif.authorize', compact('patients', 'visit'));
    }

    public function storeAuthorization(Request $request, NhifClaimService $service): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'visit_id' => ['nullable', 'exists:visits,id'],
            'diagnosis' => ['nullable', 'string', 'max:500'],
        ]);

        $patient = Patient::findOrFail($validated['patient_id']);
        $visit = isset($validated['visit_id'])
            ? Visit::find($validated['visit_id'])
            : null;

        try {
            $authorization = $service->requestAuthorization(
                $patient,
                $visit,
                $validated['diagnosis'] ?? null
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['nhif' => $e->getMessage()]);
        }

        if ($authorization->status === 'rejected') {
            return redirect()
                ->route('nhif.claims.index')
                ->withErrors(['nhif' => __('hospital.nhif_claims.auth_rejected')]);
        }

        return redirect()
            ->route('nhif.claims.index')
            ->with('success', __('hospital.nhif_claims.auth_approved', [
                'code' => $authorization->authorization_code,
            ]));
    }

    public function createClaim(Request $request, NhifClaimService $service): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_id' => ['required', 'exists:invoices,id'],
        ]);

        $invoice = Invoice::with(['patient', 'items'])->findOrFail($validated['invoice_id']);

        try {
            $claim = $service->createClaimFromInvoice($invoice);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['nhif' => $e->getMessage()]);
        }

        return redirect()
            ->route('nhif.claims.show', $claim)
            ->with('success', __('hospital.nhif_claims.claim_created', ['no' => $claim->claim_no]));
    }

    public function showClaim(NhifClaim $claim): View
    {
        $claim->load(['patient', 'visit', 'invoice.items', 'authorization', 'batch', 'createdBy']);

        return view('nhif.claim-show', compact('claim'));
    }

    public function batchesIndex(): View
    {
        $batches = NhifClaimBatch::query()
            ->with(['createdBy'])
            ->latest()
            ->paginate(20);

        return view('nhif.batches', compact('batches'));
    }

    public function batchesCreate(): View
    {
        $readyClaims = NhifClaim::query()
            ->with(['patient', 'invoice'])
            ->where('status', 'ready')
            ->whereNull('nhif_claim_batch_id')
            ->orderBy('created_at')
            ->get();

        return view('nhif.batch-create', compact('readyClaims'));
    }

    public function batchesStore(Request $request, NhifClaimService $service): RedirectResponse
    {
        $validated = $request->validate([
            'period_from' => ['required', 'date'],
            'period_to' => ['required', 'date', 'after_or_equal:period_from'],
            'claim_ids' => ['required', 'array', 'min:1'],
            'claim_ids.*' => ['integer', 'exists:nhif_claims,id'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $batch = $service->addClaimsToBatch(
                $validated['claim_ids'],
                $validated['period_from'],
                $validated['period_to']
            );

            if (! empty($validated['notes'])) {
                $batch->update(['notes' => $validated['notes']]);
            }
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['batch' => $e->getMessage()]);
        }

        return redirect()
            ->route('nhif.batches.index')
            ->with('success', __('hospital.nhif_claims.batch_created', ['no' => $batch->batch_no]));
    }

    public function submitBatch(NhifClaimBatch $batch, NhifClaimService $service): RedirectResponse
    {
        try {
            $service->submitBatch($batch);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['batch' => $e->getMessage()]);
        }

        return redirect()
            ->route('nhif.batches.index')
            ->with('success', __('hospital.nhif_claims.batch_submitted', ['no' => $batch->batch_no]));
    }
}
