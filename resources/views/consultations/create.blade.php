@extends('layouts.hospital')

@section('title', 'Consultation')
@section('eyebrow', 'Clinical')
@section('heading', 'Consultation — '.$visit->visit_no)

@section('actions')
    <a href="{{ route('visits.show', $visit) }}" class="mp-btn-secondary">Back to visit</a>
@endsection

@section('content')
<div class="mp-card mb-4 rounded-xl bg-brand-50/60 p-4 text-sm">
    <strong>{{ $visit->patient->full_name }}</strong> · {{ $visit->patient->mrn }}
    @if($visit->patient->allergies)
        · <span class="text-red-700">Allergies: {{ $visit->patient->allergies }}</span>
    @endif
</div>

<form method="POST" action="{{ route('consultations.store', $visit) }}">
    @csrf

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="mp-card space-y-4">
            <h2 class="font-display text-lg text-ink-900">Clinical notes</h2>
            <div>
                <label class="mp-label" for="history_of_present_illness">History of present illness</label>
                <textarea class="mp-input" name="history_of_present_illness" id="history_of_present_illness" rows="3">{{ old('history_of_present_illness') }}</textarea>
            </div>
            <div>
                <label class="mp-label" for="past_medical_history">Past medical history</label>
                <textarea class="mp-input" name="past_medical_history" id="past_medical_history" rows="2">{{ old('past_medical_history') }}</textarea>
            </div>
            <div>
                <label class="mp-label" for="examination_findings">Examination findings</label>
                <textarea class="mp-input" name="examination_findings" id="examination_findings" rows="3">{{ old('examination_findings') }}</textarea>
            </div>
            <div>
                <label class="mp-label" for="diagnosis_summary">Diagnosis summary</label>
                <input class="mp-input" type="text" name="diagnosis_summary" id="diagnosis_summary" value="{{ old('diagnosis_summary') }}">
            </div>
            <div>
                <label class="mp-label" for="icd10_codes">ICD-10 codes</label>
                <input class="mp-input" type="text" name="icd10_codes" id="icd10_codes" value="{{ old('icd10_codes') }}" placeholder="e.g. J06.9">
            </div>
            <div>
                <label class="mp-label" for="treatment_plan">Treatment plan</label>
                <textarea class="mp-input" name="treatment_plan" id="treatment_plan" rows="2">{{ old('treatment_plan') }}</textarea>
            </div>
            <div>
                <label class="mp-label" for="advice">Advice / patient education</label>
                <textarea class="mp-input" name="advice" id="advice" rows="2">{{ old('advice') }}</textarea>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mp-label" for="outcome">Outcome *</label>
                    <select class="mp-input" name="outcome" id="outcome" required>
                        @foreach(['treated' => 'Treated & discharged', 'referred' => 'Referred', 'admitted' => 'Admitted', 'follow_up' => 'Follow-up', 'discharged' => 'Discharged'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('outcome', 'treated') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mp-label" for="follow_up_date">Follow-up date</label>
                    <input class="mp-input" type="date" name="follow_up_date" id="follow_up_date" value="{{ old('follow_up_date') }}">
                </div>
                <div class="sm:col-span-2">
                    <label class="mp-label" for="visit_status">Next visit status</label>
                    <select class="mp-input" name="visit_status" id="visit_status">
                        <option value="">Auto (based on orders)</option>
                        @foreach(['investigations','pharmacy','billing','admitted','completed'] as $s)
                            <option value="{{ $s }}" @selected(old('visit_status') === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="mp-card">
                <h2 class="font-display text-lg text-ink-900">Laboratory tests</h2>
                <div class="mt-3 max-h-48 overflow-y-auto space-y-2">
                    @foreach($labTests as $test)
                        <label class="flex items-start gap-2 text-sm">
                            <input type="checkbox" name="lab_test_ids[]" value="{{ $test->id }}" @checked(in_array($test->id, old('lab_test_ids', []))) class="mt-1 rounded border-brand-300 text-brand-700">
                            <span>{{ $test->name }} <span class="text-brand-700">({{ \App\Support\Hospital::money($test->price) }})</span></span>
                        </label>
                    @endforeach
                </div>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mp-label" for="lab_priority">Priority</label>
                        <select class="mp-input" name="lab_priority" id="lab_priority">
                            @foreach(['routine','urgent','stat'] as $p)
                                <option value="{{ $p }}" @selected(old('lab_priority', 'routine') === $p)>{{ ucfirst($p) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <label class="mp-label" for="lab_clinical_notes">Lab clinical notes</label>
                    <textarea class="mp-input" name="lab_clinical_notes" id="lab_clinical_notes" rows="2">{{ old('lab_clinical_notes') }}</textarea>
                </div>
            </div>

            <div class="mp-card">
                <h2 class="font-display text-lg text-ink-900">Radiology</h2>
                <div class="mt-3 max-h-40 overflow-y-auto space-y-2">
                    @foreach($radiologyServices as $service)
                        <label class="flex items-start gap-2 text-sm">
                            <input type="checkbox" name="radiology_service_ids[]" value="{{ $service->id }}" @checked(in_array($service->id, old('radiology_service_ids', []))) class="mt-1 rounded border-brand-300 text-brand-700">
                            <span>{{ $service->name }} <span class="text-brand-700">({{ \App\Support\Hospital::money($service->price) }})</span></span>
                        </label>
                    @endforeach
                </div>
                <div class="mt-3">
                    <label class="mp-label" for="radiology_clinical_info">Radiology clinical info</label>
                    <textarea class="mp-input" name="radiology_clinical_info" id="radiology_clinical_info" rows="2">{{ old('radiology_clinical_info') }}</textarea>
                </div>
            </div>

            <div class="mp-card">
                <h2 class="font-display text-lg text-ink-900">Prescription</h2>
                <div id="rx-rows" class="space-y-3">
                    @for($i = 0; $i < max(1, count(old('prescription_items', []))); $i++)
                        @php $item = old('prescription_items.'.$i, []); @endphp
                        <div class="rounded-xl border border-brand-100 bg-brand-50/30 p-3 rx-row">
                            <div class="grid gap-2 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <label class="mp-label text-xs">Medicine</label>
                                    <select class="mp-input" name="prescription_items[{{ $i }}][medicine_id]">
                                        <option value="">— Select —</option>
                                        @foreach($medicines as $med)
                                            <option value="{{ $med->id }}" @selected(($item['medicine_id'] ?? '') == $med->id)>{{ $med->name }} ({{ $med->strength }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="mp-label text-xs">Dosage</label>
                                    <input class="mp-input" type="text" name="prescription_items[{{ $i }}][dosage]" value="{{ $item['dosage'] ?? '' }}" placeholder="e.g. 500mg">
                                </div>
                                <div>
                                    <label class="mp-label text-xs">Frequency</label>
                                    <input class="mp-input" type="text" name="prescription_items[{{ $i }}][frequency]" value="{{ $item['frequency'] ?? '' }}" placeholder="e.g. BD">
                                </div>
                                <div>
                                    <label class="mp-label text-xs">Duration</label>
                                    <input class="mp-input" type="text" name="prescription_items[{{ $i }}][duration]" value="{{ $item['duration'] ?? '' }}" placeholder="e.g. 5 days">
                                </div>
                                <div>
                                    <label class="mp-label text-xs">Quantity *</label>
                                    <input class="mp-input" type="number" name="prescription_items[{{ $i }}][quantity]" min="1" value="{{ $item['quantity'] ?? '' }}">
                                </div>
                            </div>
                        </div>
                    @endfor
                </div>
                <button type="button" id="add-rx-row" class="mt-3 text-sm font-semibold text-brand-700">+ Add medicine row</button>
            </div>
        </div>
    </div>

    <div class="mt-6 flex gap-3">
        <button type="submit" class="mp-btn">Save consultation</button>
        <a href="{{ route('visits.show', $visit) }}" class="mp-btn-secondary">Cancel</a>
    </div>
</form>

<script>
document.getElementById('add-rx-row')?.addEventListener('click', function () {
    const container = document.getElementById('rx-rows');
    const index = container.querySelectorAll('.rx-row').length;
    const template = container.querySelector('.rx-row').cloneNode(true);
    template.querySelectorAll('[name]').forEach(el => {
        el.name = el.name.replace(/\[\d+\]/, '[' + index + ']');
        if (el.tagName === 'SELECT') el.selectedIndex = 0;
        else el.value = '';
    });
    container.appendChild(template);
});
</script>
@endsection
