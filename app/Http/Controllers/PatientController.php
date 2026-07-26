<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Support\Hospital;
use App\Services\ExemptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $patients = Patient::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('mrn', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('nhif_card_no', 'like', "%{$search}%")
                        ->orWhereRaw("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) LIKE ?", ["%{$search}%"]);
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('patients.index', [
            'patients' => $patients,
            'search' => $search,
            'paymentCategories' => Hospital::paymentCategories(),
            'exemptionTypes' => Hospital::exemptionTypes(),
            'regions' => Hospital::tanzaniaRegions(),
        ]);
    }

    public function create(): View
    {
        return view('patients.create', [
            'paymentCategories' => Hospital::paymentCategories(),
            'exemptionTypes' => Hospital::exemptionTypes(),
            'regions' => Hospital::tanzaniaRegions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'age_years' => ['nullable', 'integer', 'min:0', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'national_id' => ['nullable', 'string', 'max:30'],
            'nhif_card_no' => ['nullable', 'string', 'max:40'],
            'payment_category' => ['required', 'in:cash,nhif,exemption,corporate,insurance'],
            'exemption_type' => ['nullable', 'string', 'max:255'],
            'next_of_kin' => ['nullable', 'string', 'max:255'],
            'next_of_kin_phone' => ['nullable', 'string', 'max:20'],
            'region' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'ward_village' => ['nullable', 'string', 'max:255'],
            'street_address' => ['nullable', 'string', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'marital_status' => ['nullable', 'in:single,married,divorced,widowed,unknown'],
            'blood_group' => ['nullable', 'string', 'max:5'],
            'allergies' => ['nullable', 'string'],
            'chronic_conditions' => ['nullable', 'string'],
        ]);

        try {
            $patient = Patient::create([
                ...$validated,
                'mrn' => Hospital::nextMrn(),
                'registered_by' => auth()->id(),
                'marital_status' => $validated['marital_status'] ?? 'unknown',
            ]);

            $exemption = app(ExemptionService::class)->applyToPatient($patient);
            $message = 'Patient registered successfully. MRN: '.$patient->mrn;
            if ($exemption['eligible']) {
                $message .= ' · Auto exemption: '.$exemption['reason'];
            }

            return redirect()
                ->route('patients.show', $patient->fresh())
                ->with('success', $message);
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to register patient. Please try again.']);
        }
    }

    public function show(Patient $patient): View
    {
        $patient->load(['visits' => fn ($q) => $q->latest()->limit(10), 'admissions' => fn ($q) => $q->latest()->limit(5)]);

        return view('patients.show', compact('patient'));
    }

    public function edit(Patient $patient): View
    {
        return view('patients.edit', [
            'patient' => $patient,
            'paymentCategories' => Hospital::paymentCategories(),
            'exemptionTypes' => Hospital::exemptionTypes(),
            'regions' => Hospital::tanzaniaRegions(),
        ]);
    }

    public function update(Request $request, Patient $patient): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'age_years' => ['nullable', 'integer', 'min:0', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'national_id' => ['nullable', 'string', 'max:30'],
            'nhif_card_no' => ['nullable', 'string', 'max:40'],
            'payment_category' => ['required', 'in:cash,nhif,exemption,corporate,insurance'],
            'exemption_type' => ['nullable', 'string', 'max:255'],
            'next_of_kin' => ['nullable', 'string', 'max:255'],
            'next_of_kin_phone' => ['nullable', 'string', 'max:20'],
            'region' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'ward_village' => ['nullable', 'string', 'max:255'],
            'street_address' => ['nullable', 'string', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'marital_status' => ['nullable', 'in:single,married,divorced,widowed,unknown'],
            'blood_group' => ['nullable', 'string', 'max:5'],
            'allergies' => ['nullable', 'string'],
            'chronic_conditions' => ['nullable', 'string'],
        ]);

        try {
            $patient->update($validated);
            $exemption = app(ExemptionService::class)->applyToPatient($patient->fresh());
            $message = 'Patient record updated successfully.';
            if ($exemption['eligible']) {
                $message .= ' · Exemption: '.$exemption['reason'];
            }

            return redirect()
                ->route('patients.show', $patient)
                ->with('success', $message);
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to update patient record.']);
        }
    }
}
