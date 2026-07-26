<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\Visit;
use App\Models\Ward;
use App\Support\Hospital;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdmissionController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'admitted');

        $admissions = Admission::query()
            ->with(['patient', 'ward', 'bed', 'visit', 'admittingDoctor'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('admitted_at')
            ->paginate(20)
            ->withQueryString();

        return view('admissions.index', [
            'admissions' => $admissions,
            'status' => $status,
        ]);
    }

    public function create(Visit $visit): View
    {
        $visit->load('patient');

        $wards = Ward::with(['beds' => fn ($q) => $q->where('status', 'available')])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admissions.create', compact('visit', 'wards'));
    }

    public function store(Request $request, Visit $visit): RedirectResponse
    {
        $validated = $request->validate([
            'ward_id' => ['required', 'exists:wards,id'],
            'bed_id' => ['required', 'exists:beds,id'],
            'admission_reason' => ['nullable', 'string', 'max:500'],
        ]);

        if (Admission::where('visit_id', $visit->id)->where('status', 'admitted')->exists()) {
            return back()->withErrors(['error' => 'Patient is already admitted for this visit.']);
        }

        try {
            $admission = DB::transaction(function () use ($validated, $visit) {
                $bed = Bed::lockForUpdate()->findOrFail($validated['bed_id']);

                if ($bed->ward_id != $validated['ward_id']) {
                    throw new \RuntimeException('Selected bed does not belong to the chosen ward.');
                }

                if ($bed->status !== 'available') {
                    throw new \RuntimeException('Selected bed is not available.');
                }

                $bed->update(['status' => 'occupied']);

                $admission = Admission::create([
                    'admission_no' => Hospital::nextNumber('ADM'),
                    'visit_id' => $visit->id,
                    'patient_id' => $visit->patient_id,
                    'ward_id' => $validated['ward_id'],
                    'bed_id' => $bed->id,
                    'admitting_doctor_id' => auth()->id(),
                    'admission_reason' => $validated['admission_reason'] ?? null,
                    'status' => 'admitted',
                    'admitted_at' => now(),
                ]);

                $visit->update([
                    'status' => 'admitted',
                    'visit_type' => 'ipd',
                ]);

                return $admission;
            });

            return redirect()
                ->route('admissions.index')
                ->with('success', 'Patient admitted. Admission No: '.$admission->admission_no);
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to admit patient.']);
        }
    }

    public function discharge(Request $request, Admission $admission): RedirectResponse
    {
        $validated = $request->validate([
            'discharge_summary' => ['nullable', 'string'],
        ]);

        if ($admission->status !== 'admitted') {
            return back()->withErrors(['error' => 'Only active admissions can be discharged.']);
        }

        try {
            DB::transaction(function () use ($validated, $admission) {
                if ($admission->bed_id) {
                    Bed::where('id', $admission->bed_id)->update(['status' => 'available']);
                }

                $admission->update([
                    'status' => 'discharged',
                    'discharged_at' => now(),
                    'discharge_summary' => $validated['discharge_summary'] ?? null,
                ]);

                if ($admission->visit) {
                    $admission->visit->update([
                        'status' => 'discharged',
                        'completed_at' => now(),
                    ]);
                }
            });

            return redirect()
                ->route('admissions.index')
                ->with('success', 'Patient discharged successfully. Bed freed.');
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to discharge patient.']);
        }
    }
}
