<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BloodIssue;
use App\Models\BloodRequest;
use App\Models\BloodUnit;
use App\Models\Patient;
use App\Models\TheatreCase;
use App\Models\Visit;
use App\Support\Hospital;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BloodBankController extends Controller
{
    public function index(): View
    {
        $stockByGroup = BloodUnit::query()
            ->where('status', 'available')
            ->where('expiry_date', '>=', now()->toDateString())
            ->selectRaw('blood_group, count(*) as total')
            ->groupBy('blood_group')
            ->orderBy('blood_group')
            ->pluck('total', 'blood_group');

        $pendingRequests = BloodRequest::query()
            ->with(['patient', 'requestedBy', 'theatreCase'])
            ->whereIn('status', ['pending', 'partial'])
            ->latest()
            ->limit(20)
            ->get();

        $expiringSoon = BloodUnit::query()
            ->where('status', 'available')
            ->whereBetween('expiry_date', [now()->toDateString(), now()->addDays(14)->toDateString()])
            ->orderBy('expiry_date')
            ->limit(20)
            ->get();

        return view('blood.index', compact('stockByGroup', 'pendingRequests', 'expiringSoon'));
    }

    public function createUnit(): View
    {
        return view('blood.create-unit');
    }

    public function storeUnit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'blood_group' => ['required', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'component' => ['required', 'in:whole_blood,packed_rbc,fresh_frozen_plasma,platelets,cryoprecipitate'],
            'volume_ml' => ['nullable', 'integer', 'min:1'],
            'donor_name' => ['nullable', 'string', 'max:255'],
            'collected_at' => ['nullable', 'date'],
            'expiry_date' => ['required', 'date', 'after:today'],
            'storage_location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $unit = BloodUnit::create([
            ...$validated,
            'unit_no' => Hospital::nextNumber('BU'),
            'volume_ml' => $validated['volume_ml'] ?? 450,
            'status' => 'available',
            'received_by' => auth()->id(),
        ]);

        return redirect()
            ->route('blood.index')
            ->with('success', __('hospital.blood.add_unit').' — '.$unit->unit_no);
    }

    public function createRequest(Request $request): View
    {
        $visitId = $request->query('visit_id');
        $theatreCaseId = $request->query('theatre_case_id');

        $visit = $visitId ? Visit::with('patient')->find($visitId) : null;
        $theatreCase = $theatreCaseId ? TheatreCase::with('patient')->find($theatreCaseId) : null;

        $patients = Patient::query()
            ->orderBy('first_name')
            ->limit(200)
            ->get();

        $theatreCases = TheatreCase::query()
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->with('patient')
            ->latest()
            ->limit(50)
            ->get();

        return view('blood.create-request', compact('patients', 'visit', 'theatreCase', 'theatreCases'));
    }

    public function storeRequest(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'visit_id' => ['nullable', 'exists:visits,id'],
            'theatre_case_id' => ['nullable', 'exists:theatre_cases,id'],
            'blood_group' => ['required', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'component' => ['required', 'in:whole_blood,packed_rbc,fresh_frozen_plasma,platelets,cryoprecipitate'],
            'units_requested' => ['required', 'integer', 'min:1', 'max:10'],
            'priority' => ['required', 'in:routine,urgent,emergency'],
            'indication' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $bloodRequest = BloodRequest::create([
            ...$validated,
            'request_no' => Hospital::nextNumber('BR'),
            'units_issued' => 0,
            'status' => 'pending',
            'requested_by' => auth()->id(),
        ]);

        return redirect()
            ->route('blood.requests.show', $bloodRequest)
            ->with('success', __('hospital.blood.request').' — '.$bloodRequest->request_no);
    }

    public function showRequest(BloodRequest $bloodRequest): View
    {
        $bloodRequest->load([
            'patient',
            'visit',
            'theatreCase',
            'requestedBy',
            'issues.bloodUnit',
            'issues.issuedBy',
        ]);

        $availableUnits = BloodUnit::query()
            ->where('status', 'available')
            ->where('blood_group', $bloodRequest->blood_group)
            ->where('component', $bloodRequest->component)
            ->where('expiry_date', '>=', now()->toDateString())
            ->orderBy('expiry_date')
            ->get();

        return view('blood.show-request', compact('bloodRequest', 'availableUnits'));
    }

    public function issue(Request $request, BloodRequest $bloodRequest): RedirectResponse
    {
        if (in_array($bloodRequest->status, ['fulfilled', 'cancelled'], true)) {
            return back()->withErrors(['error' => 'This request cannot accept further issues.']);
        }

        if ($bloodRequest->units_issued >= $bloodRequest->units_requested) {
            return back()->withErrors(['error' => 'All requested units have already been issued.']);
        }

        $validated = $request->validate([
            'blood_unit_id' => ['nullable', 'exists:blood_units,id'],
            'crossmatch_result' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            DB::transaction(function () use ($validated, $bloodRequest) {
                $unitQuery = BloodUnit::query()
                    ->where('status', 'available')
                    ->where('blood_group', $bloodRequest->blood_group)
                    ->where('component', $bloodRequest->component)
                    ->where('expiry_date', '>=', now()->toDateString())
                    ->orderBy('expiry_date');

                if (! empty($validated['blood_unit_id'])) {
                    $unit = $unitQuery->lockForUpdate()->findOrFail($validated['blood_unit_id']);
                } else {
                    $unit = $unitQuery->lockForUpdate()->first();

                    if (! $unit) {
                        throw new \RuntimeException('No matching blood unit available.');
                    }
                }

                if ($unit->status !== 'available') {
                    throw new \RuntimeException('Selected blood unit is not available.');
                }

                $unit->update(['status' => 'issued']);

                BloodIssue::create([
                    'blood_request_id' => $bloodRequest->id,
                    'blood_unit_id' => $unit->id,
                    'patient_id' => $bloodRequest->patient_id,
                    'issued_by' => auth()->id(),
                    'issued_at' => now(),
                    'crossmatch_result' => $validated['crossmatch_result'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]);

                $unitsIssued = $bloodRequest->units_issued + 1;
                $status = $unitsIssued >= $bloodRequest->units_requested ? 'fulfilled' : 'partial';

                $bloodRequest->update([
                    'units_issued' => $unitsIssued,
                    'status' => $status,
                ]);
            });

            return redirect()
                ->route('blood.requests.show', $bloodRequest)
                ->with('success', __('hospital.blood.issue').' recorded successfully.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Failed to issue blood unit.']);
        }
    }
}
