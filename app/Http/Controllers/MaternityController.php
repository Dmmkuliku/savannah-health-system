<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Newborn;
use App\Models\Patient;
use App\Models\RchImmunization;
use App\Models\RchPregnancy;
use App\Support\EpiVaccines;
use App\Support\Hospital;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MaternityController extends Controller
{
    public function index(): View
    {
        $todayDeliveries = Delivery::query()
            ->with(['patient', 'newborns', 'attendant'])
            ->whereDate('delivered_at', today())
            ->latest('delivered_at')
            ->get();

        $recentDeliveries = Delivery::query()
            ->with(['patient', 'newborns'])
            ->whereDate('delivered_at', '<', today())
            ->latest('delivered_at')
            ->limit(20)
            ->get();

        $newbornCountToday = Newborn::query()
            ->whereHas('delivery', fn ($q) => $q->whereDate('delivered_at', today()))
            ->count();

        return view('maternity.index', compact(
            'todayDeliveries',
            'recentDeliveries',
            'newbornCountToday',
        ));
    }

    public function create(Request $request): View
    {
        $patients = Patient::query()
            ->where('gender', 'female')
            ->orderBy('first_name')
            ->limit(200)
            ->get();

        $selectedPatient = $request->query('patient_id')
            ? Patient::find($request->query('patient_id'))
            : null;

        $activePregnancies = $selectedPatient
            ? RchPregnancy::query()
                ->where('patient_id', $selectedPatient->id)
                ->where('status', 'active')
                ->get()
            : collect();

        return view('maternity.create', compact('patients', 'selectedPatient', 'activePregnancies'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'rch_pregnancy_id' => ['nullable', 'exists:rch_pregnancies,id'],
            'admission_id' => ['nullable', 'exists:admissions,id'],
            'visit_id' => ['nullable', 'exists:visits,id'],
            'delivered_at' => ['required', 'date'],
            'delivery_type' => ['required', 'in:spontaneous_vaginal,assisted_vaginal,caesarean,breech'],
            'place' => ['required', 'in:labour_ward,theatre,home_brought,other'],
            'gestational_weeks' => ['nullable', 'integer', 'min:20', 'max:45'],
            'outcome' => ['required', 'in:live_birth,stillbirth,neonatal_death'],
            'babies_count' => ['required', 'integer', 'min:1', 'max:5'],
            'mother_alive' => ['nullable', 'boolean'],
            'complications' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'newborns' => ['required', 'array', 'min:1'],
            'newborns.*.sex' => ['required', 'in:male,female,unknown'],
            'newborns.*.birth_weight_kg' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'newborns.*.apgar_1' => ['nullable', 'integer', 'min:0', 'max:10'],
            'newborns.*.apgar_5' => ['nullable', 'integer', 'min:0', 'max:10'],
            'newborns.*.status' => ['required', 'in:alive,stillbirth,died'],
            'newborns.*.name' => ['nullable', 'string', 'max:255'],
            'newborns.*.patient_id' => ['nullable', 'exists:patients,id'],
            'newborns.*.breastfeeding_initiated' => ['nullable', 'boolean'],
            'newborns.*.bcg_given' => ['nullable', 'boolean'],
            'newborns.*.opv0_given' => ['nullable', 'boolean'],
            'newborns.*.notes' => ['nullable', 'string'],
        ]);

        $mother = Patient::findOrFail($validated['patient_id']);

        if (! empty($validated['rch_pregnancy_id'])) {
            $pregnancy = RchPregnancy::where('id', $validated['rch_pregnancy_id'])
                ->where('patient_id', $mother->id)
                ->first();

            if (! $pregnancy) {
                return back()->withInput()->withErrors(['rch_pregnancy_id' => __('hospital.maternity.invalid_pregnancy')]);
            }
        }

        try {
            $delivery = DB::transaction(function () use ($validated, $mother) {
                $delivery = Delivery::create([
                    'delivery_no' => Hospital::nextNumber('DLV'),
                    'patient_id' => $mother->id,
                    'rch_pregnancy_id' => $validated['rch_pregnancy_id'] ?? null,
                    'admission_id' => $validated['admission_id'] ?? null,
                    'visit_id' => $validated['visit_id'] ?? null,
                    'delivered_at' => Carbon::parse($validated['delivered_at']),
                    'delivery_type' => $validated['delivery_type'],
                    'place' => $validated['place'],
                    'gestational_weeks' => $validated['gestational_weeks'] ?? null,
                    'outcome' => $validated['outcome'],
                    'babies_count' => $validated['babies_count'],
                    'mother_alive' => (bool) ($validated['mother_alive'] ?? true),
                    'complications' => $validated['complications'] ?? null,
                    'attendant_id' => auth()->id(),
                    'notes' => $validated['notes'] ?? null,
                ]);

                if (! empty($validated['rch_pregnancy_id'])) {
                    RchPregnancy::where('id', $validated['rch_pregnancy_id'])
                        ->update(['status' => 'delivered']);
                }

                $vaccineMap = collect(EpiVaccines::schedule())->keyBy('code');
                $isLiveBirth = $validated['outcome'] === 'live_birth';
                $givenAt = Carbon::parse($validated['delivered_at'])->toDateString();

                foreach ($validated['newborns'] as $newbornData) {
                    $newborn = Newborn::create([
                        'delivery_id' => $delivery->id,
                        'mother_id' => $mother->id,
                        'patient_id' => $newbornData['patient_id'] ?? null,
                        'name' => $newbornData['name'] ?? null,
                        'sex' => $newbornData['sex'],
                        'birth_weight_kg' => $newbornData['birth_weight_kg'] ?? null,
                        'apgar_1' => $newbornData['apgar_1'] ?? null,
                        'apgar_5' => $newbornData['apgar_5'] ?? null,
                        'status' => $newbornData['status'],
                        'breastfeeding_initiated' => (bool) ($newbornData['breastfeeding_initiated'] ?? false),
                        'bcg_given' => (bool) ($newbornData['bcg_given'] ?? false),
                        'opv0_given' => (bool) ($newbornData['opv0_given'] ?? false),
                        'notes' => $newbornData['notes'] ?? null,
                    ]);

                    if ($isLiveBirth && $newborn->status === 'alive' && $newborn->patient_id) {
                        if ($newborn->bcg_given && $vaccineMap->has('BCG')) {
                            $this->recordBirthVaccine($newborn->patient_id, $vaccineMap->get('BCG'), $givenAt);
                        }

                        if ($newborn->opv0_given && $vaccineMap->has('OPV0')) {
                            $this->recordBirthVaccine($newborn->patient_id, $vaccineMap->get('OPV0'), $givenAt);
                        }
                    }
                }

                return $delivery;
            });

            return redirect()
                ->route('maternity.show', $delivery)
                ->with('success', __('hospital.maternity.delivery_recorded').' — '.$delivery->delivery_no);
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['error' => __('hospital.maternity.delivery_failed')]);
        }
    }

    public function show(Delivery $delivery): View
    {
        $delivery->load([
            'patient',
            'pregnancy',
            'admission',
            'visit',
            'attendant',
            'newborns.patient',
            'pncVisits.attendedBy',
        ]);

        return view('maternity.show', compact('delivery'));
    }

    /**
     * @param  array{code: string, name: string, dose: string, due_weeks: ?int}  $vaccine
     */
    private function recordBirthVaccine(int $patientId, array $vaccine, string $givenAt): void
    {
        $exists = RchImmunization::query()
            ->where('patient_id', $patientId)
            ->where('vaccine_code', $vaccine['code'])
            ->exists();

        if ($exists) {
            return;
        }

        RchImmunization::create([
            'patient_id' => $patientId,
            'vaccine_code' => $vaccine['code'],
            'vaccine_name' => $vaccine['name'],
            'dose' => $vaccine['dose'],
            'given_at' => $givenAt,
            'given_by' => auth()->id(),
        ]);
    }
}
