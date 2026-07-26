<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\RchAncVisit;
use App\Models\RchImmunization;
use App\Models\RchPncVisit;
use App\Models\RchPregnancy;
use App\Services\ExemptionService;
use App\Support\EpiVaccines;
use App\Support\Hospital;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RchController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'pregnancies');

        $stats = [
            'active_pregnancies' => RchPregnancy::where('status', 'active')->count(),
            'recent_anc' => RchAncVisit::where('visit_date', '>=', now()->subDays(30))->count(),
            'recent_immunizations' => RchImmunization::where('given_at', '>=', now()->subDays(30))->count(),
            'upcoming_due_vaccines' => $this->countDueVaccines(),
        ];

        $activePregnancies = RchPregnancy::query()
            ->with(['patient', 'registeredBy'])
            ->where('status', 'active')
            ->latest()
            ->limit(20)
            ->get();

        $recentAncVisits = RchAncVisit::query()
            ->with(['patient', 'pregnancy', 'attendedBy'])
            ->latest('visit_date')
            ->limit(20)
            ->get();

        $recentImmunizations = RchImmunization::query()
            ->with(['patient', 'givenBy'])
            ->latest('given_at')
            ->limit(20)
            ->get();

        $patients = Patient::query()
            ->where('gender', 'female')
            ->orderBy('first_name')
            ->limit(200)
            ->get();

        return view('rch.index', compact(
            'tab',
            'stats',
            'activePregnancies',
            'recentAncVisits',
            'recentImmunizations',
            'patients',
        ));
    }

    public function createPregnancy(Request $request): View
    {
        $patients = Patient::query()
            ->where('gender', 'female')
            ->orderBy('first_name')
            ->limit(200)
            ->get();

        $selectedPatient = $request->query('patient_id')
            ? Patient::find($request->query('patient_id'))
            : null;

        return view('rch.create-pregnancy', compact('patients', 'selectedPatient'));
    }

    public function storePregnancy(Request $request, ExemptionService $exemptionService): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'lmp' => ['nullable', 'date', 'before_or_equal:today'],
            'gravida' => ['nullable', 'integer', 'min:0', 'max:20'],
            'para' => ['nullable', 'integer', 'min:0', 'max:20'],
            'abortions' => ['nullable', 'integer', 'min:0', 'max:20'],
            'blood_group' => ['nullable', 'string', 'max:5'],
            'hiv_tested' => ['nullable', 'boolean'],
            'hiv_status' => ['nullable', 'string', 'max:20'],
            'tt_given' => ['nullable', 'boolean'],
            'risk_factors' => ['nullable', 'string'],
        ]);

        $patient = Patient::findOrFail($validated['patient_id']);

        $lmp = isset($validated['lmp']) ? Carbon::parse($validated['lmp']) : null;
        $edd = $lmp ? $lmp->copy()->addDays(280) : null;

        $pregnancy = RchPregnancy::create([
            'anc_no' => Hospital::nextNumber('ANC'),
            'patient_id' => $patient->id,
            'lmp' => $lmp,
            'edd' => $edd,
            'gravida' => $validated['gravida'] ?? null,
            'para' => $validated['para'] ?? null,
            'abortions' => $validated['abortions'] ?? 0,
            'blood_group' => $validated['blood_group'] ?? $patient->blood_group,
            'hiv_tested' => (bool) ($validated['hiv_tested'] ?? false),
            'hiv_status' => $validated['hiv_status'] ?? null,
            'tt_given' => (bool) ($validated['tt_given'] ?? false),
            'risk_factors' => $validated['risk_factors'] ?? null,
            'status' => 'active',
            'registered_by' => auth()->id(),
        ]);

        $exemptionService->applyToPatient($patient, forcePregnant: true);

        return redirect()
            ->route('rch.pregnancies.show', $pregnancy)
            ->with('success', __('hospital.rch.pregnancy_registered').' — '.$pregnancy->anc_no);
    }

    public function showPregnancy(RchPregnancy $pregnancy): View
    {
        $pregnancy->load([
            'patient',
            'registeredBy',
            'ancVisits' => fn ($q) => $q->with('attendedBy')->orderBy('visit_number'),
            'deliveries',
        ]);

        return view('rch.show-pregnancy', compact('pregnancy'));
    }

    public function storeAncVisit(Request $request, RchPregnancy $pregnancy): RedirectResponse
    {
        if ($pregnancy->status !== 'active') {
            return back()->withErrors(['error' => __('hospital.rch.pregnancy_not_active')]);
        }

        $validated = $request->validate([
            'visit_date' => ['required', 'date', 'before_or_equal:today'],
            'visit_id' => ['nullable', 'exists:visits,id'],
            'weight_kg' => ['nullable', 'numeric', 'min:0', 'max:200'],
            'bp_systolic' => ['nullable', 'integer', 'min:50', 'max:300'],
            'bp_diastolic' => ['nullable', 'integer', 'min:30', 'max:200'],
            'hb_gdl' => ['nullable', 'numeric', 'min:0', 'max:25'],
            'fetal_heart_heard' => ['nullable', 'boolean'],
            'urine_protein' => ['nullable', 'string', 'max:50'],
            'ipt_given' => ['nullable', 'boolean'],
            'iron_folate_given' => ['nullable', 'boolean'],
            'mosquito_net_given' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $visitNumber = ($pregnancy->ancVisits()->max('visit_number') ?? 0) + 1;
        $visitDate = Carbon::parse($validated['visit_date']);

        $gestationalWeeks = null;
        if ($pregnancy->lmp) {
            $gestationalWeeks = (int) floor($pregnancy->lmp->diffInDays($visitDate) / 7);
            if ($gestationalWeeks < 0) {
                $gestationalWeeks = null;
            }
        }

        RchAncVisit::create([
            'rch_pregnancy_id' => $pregnancy->id,
            'patient_id' => $pregnancy->patient_id,
            'visit_id' => $validated['visit_id'] ?? null,
            'visit_number' => $visitNumber,
            'visit_date' => $visitDate,
            'gestational_weeks' => $gestationalWeeks,
            'weight_kg' => $validated['weight_kg'] ?? null,
            'bp_systolic' => $validated['bp_systolic'] ?? null,
            'bp_diastolic' => $validated['bp_diastolic'] ?? null,
            'hb_gdl' => $validated['hb_gdl'] ?? null,
            'fetal_heart_heard' => $validated['fetal_heart_heard'] ?? null,
            'urine_protein' => $validated['urine_protein'] ?? null,
            'ipt_given' => (bool) ($validated['ipt_given'] ?? false),
            'iron_folate_given' => (bool) ($validated['iron_folate_given'] ?? false),
            'mosquito_net_given' => (bool) ($validated['mosquito_net_given'] ?? false),
            'notes' => $validated['notes'] ?? null,
            'attended_by' => auth()->id(),
        ]);

        return redirect()
            ->route('rch.pregnancies.show', $pregnancy)
            ->with('success', __('hospital.rch.anc_recorded'));
    }

    public function createImmunization(Request $request): View
    {
        $vaccines = EpiVaccines::schedule();

        $patients = Patient::query()
            ->orderBy('first_name')
            ->limit(200)
            ->get();

        $selectedPatient = $request->query('patient_id')
            ? Patient::find($request->query('patient_id'))
            : null;

        return view('rch.immunization-create', compact('vaccines', 'patients', 'selectedPatient'));
    }

    public function storeImmunization(Request $request): RedirectResponse
    {
        $vaccineCodes = collect(EpiVaccines::schedule())->pluck('code')->all();

        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'vaccine_code' => ['required', 'string', 'in:'.implode(',', $vaccineCodes)],
            'given_at' => ['required', 'date', 'before_or_equal:today'],
            'batch_no' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $vaccine = collect(EpiVaccines::schedule())
            ->firstWhere('code', $validated['vaccine_code']);

        if (! $vaccine) {
            return back()->withInput()->withErrors(['vaccine_code' => __('hospital.rch.invalid_vaccine')]);
        }

        $patient = Patient::findOrFail($validated['patient_id']);
        $givenAt = Carbon::parse($validated['given_at']);

        $nextDue = null;
        if ($patient->date_of_birth && $vaccine['due_weeks'] !== null) {
            $dueDate = Carbon::parse($patient->date_of_birth)->addWeeks($vaccine['due_weeks']);
            if ($dueDate->isFuture()) {
                $nextDue = $dueDate;
            }
        }

        RchImmunization::create([
            'patient_id' => $patient->id,
            'vaccine_code' => $vaccine['code'],
            'vaccine_name' => $vaccine['name'],
            'dose' => $vaccine['dose'],
            'given_at' => $givenAt,
            'batch_no' => $validated['batch_no'] ?? null,
            'next_due' => $nextDue,
            'notes' => $validated['notes'] ?? null,
            'given_by' => auth()->id(),
        ]);

        return redirect()
            ->route('rch.index', ['tab' => 'immunizations'])
            ->with('success', __('hospital.rch.immunization_recorded'));
    }

    public function storePncVisit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'delivery_id' => ['nullable', 'exists:deliveries,id'],
            'visit_id' => ['nullable', 'exists:visits,id'],
            'visit_date' => ['required', 'date', 'before_or_equal:today'],
            'days_postpartum' => ['nullable', 'integer', 'min:0', 'max:365'],
            'mother_condition' => ['nullable', 'string', 'max:255'],
            'baby_condition' => ['nullable', 'string', 'max:255'],
            'breastfeeding' => ['nullable', 'boolean'],
            'family_planning_counselled' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $pncVisit = RchPncVisit::create([
            ...$validated,
            'breastfeeding' => $validated['breastfeeding'] ?? null,
            'family_planning_counselled' => (bool) ($validated['family_planning_counselled'] ?? false),
            'attended_by' => auth()->id(),
        ]);

        $redirect = $validated['delivery_id']
            ? route('maternity.show', $validated['delivery_id'])
            : route('rch.index', ['tab' => 'pregnancies']);

        return redirect($redirect)
            ->with('success', __('hospital.rch.pnc_recorded'));
    }

    private function countDueVaccines(): int
    {
        $schedule = EpiVaccines::schedule();
        $count = 0;

        $childPatients = Patient::query()
            ->where(function ($q) {
                $q->where('date_of_birth', '>=', now()->subYears(5))
                    ->orWhere('age_years', '<', 5);
            })
            ->get(['id', 'date_of_birth', 'age_years']);

        if ($childPatients->isEmpty()) {
            return 0;
        }

        $givenByPatient = RchImmunization::query()
            ->whereIn('patient_id', $childPatients->pluck('id'))
            ->get(['patient_id', 'vaccine_code'])
            ->groupBy('patient_id')
            ->map(fn ($rows) => $rows->pluck('vaccine_code')->all());

        foreach ($childPatients as $patient) {
            $ageWeeks = $this->patientAgeWeeks($patient);
            if ($ageWeeks === null) {
                continue;
            }

            $given = $givenByPatient->get($patient->id, []);

            foreach ($schedule as $vaccine) {
                if ($vaccine['due_weeks'] === null || $ageWeeks < $vaccine['due_weeks']) {
                    continue;
                }

                if (! in_array($vaccine['code'], $given, true)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    private function patientAgeWeeks(Patient $patient): ?int
    {
        if ($patient->date_of_birth) {
            return (int) floor(Carbon::parse($patient->date_of_birth)->diffInDays(now()) / 7);
        }

        if ($patient->age_years !== null) {
            return (int) ($patient->age_years * 52);
        }

        return null;
    }
}
