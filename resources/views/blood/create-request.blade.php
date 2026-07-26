@extends('layouts.hospital')

@section('title', __('hospital.blood.request'))
@section('eyebrow', __('hospital.blood.eyebrow'))
@section('heading', __('hospital.blood.request'))

@section('actions')
    <a href="{{ route('blood.index') }}" class="mp-btn-secondary">Back</a>
@endsection

@section('content')
@if($visit)
    <div class="mp-card mb-4 rounded-xl bg-brand-50/60 p-4 text-sm">
        <strong>{{ $visit->patient->full_name }}</strong> · {{ $visit->patient->mrn }} · Visit {{ $visit->visit_no }}
    </div>
@elseif($theatreCase)
    <div class="mp-card mb-4 rounded-xl bg-brand-50/60 p-4 text-sm">
        <strong>{{ $theatreCase->patient->full_name }}</strong> · {{ $theatreCase->case_no }} · {{ $theatreCase->procedure_name }}
    </div>
@endif

<div class="mp-card max-w-3xl">
    <form method="POST" action="{{ route('blood.requests.store') }}">
        @csrf
        @if($visit)
            <input type="hidden" name="visit_id" value="{{ $visit->id }}">
            <input type="hidden" name="patient_id" value="{{ $visit->patient_id }}">
        @elseif($theatreCase)
            <input type="hidden" name="theatre_case_id" value="{{ $theatreCase->id }}">
            <input type="hidden" name="patient_id" value="{{ $theatreCase->patient_id }}">
            @if($theatreCase->visit_id)
                <input type="hidden" name="visit_id" value="{{ $theatreCase->visit_id }}">
            @endif
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            @unless($visit || $theatreCase)
                <div class="sm:col-span-2">
                    <label class="mp-label" for="patient_id">Patient *</label>
                    <select class="mp-input" name="patient_id" id="patient_id" required>
                        <option value="">— Select patient —</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->id }}" @selected(old('patient_id') == $patient->id)>
                                {{ $patient->full_name }} ({{ $patient->mrn }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @endunless

            @unless($theatreCase)
                <div class="sm:col-span-2">
                    <label class="mp-label" for="theatre_case_id">Theatre case (optional)</label>
                    <select class="mp-input" name="theatre_case_id" id="theatre_case_id">
                        <option value="">— None —</option>
                        @foreach($theatreCases as $case)
                            <option value="{{ $case->id }}" @selected(old('theatre_case_id') == $case->id)>
                                {{ $case->case_no }} — {{ $case->patient->full_name }} — {{ $case->procedure_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endunless

            <div>
                <label class="mp-label" for="blood_group">{{ __('hospital.blood.blood_group') }} *</label>
                <select class="mp-input" name="blood_group" id="blood_group" required>
                    @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $group)
                        <option value="{{ $group }}" @selected(old('blood_group', $theatreCase?->patient?->blood_group) === $group)>{{ $group }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mp-label" for="component">{{ __('hospital.blood.component') }} *</label>
                <select class="mp-input" name="component" id="component" required>
                    @foreach(['whole_blood', 'packed_rbc', 'fresh_frozen_plasma', 'platelets', 'cryoprecipitate'] as $component)
                        <option value="{{ $component }}" @selected(old('component', 'whole_blood') === $component)>{{ str_replace('_', ' ', ucfirst($component)) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mp-label" for="units_requested">Units requested *</label>
                <input class="mp-input" type="number" min="1" max="10" name="units_requested" id="units_requested" value="{{ old('units_requested', 1) }}" required>
            </div>

            <div>
                <label class="mp-label" for="priority">Priority *</label>
                <select class="mp-input" name="priority" id="priority" required>
                    @foreach(['routine', 'urgent', 'emergency'] as $level)
                        <option value="{{ $level }}" @selected(old('priority', 'routine') === $level)>{{ ucfirst($level) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="mp-label" for="indication">Indication</label>
                <input class="mp-input" type="text" name="indication" id="indication" value="{{ old('indication') }}">
            </div>

            <div class="sm:col-span-2">
                <label class="mp-label" for="notes">Notes</label>
                <textarea class="mp-input" name="notes" id="notes" rows="3">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="mp-btn">{{ __('hospital.blood.request') }}</button>
            <a href="{{ route('blood.index') }}" class="mp-btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
