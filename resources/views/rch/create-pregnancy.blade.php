@extends('layouts.hospital')

@section('title', __('hospital.rch.register_pregnancy'))
@section('eyebrow', __('hospital.rch.eyebrow'))
@section('heading', __('hospital.rch.register_pregnancy'))

@section('actions')
    <a href="{{ route('rch.index') }}" class="mp-btn-secondary">{{ __('hospital.rch.back') }}</a>
@endsection

@section('content')
<div class="mp-card max-w-3xl">
    <form method="POST" action="{{ route('rch.pregnancies.store') }}">
        @csrf
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="mp-label" for="patient_id">{{ __('hospital.rch.mother') }} *</label>
                <select class="mp-input" name="patient_id" id="patient_id" required>
                    <option value="">— {{ __('hospital.rch.select_mother') }} —</option>
                    @foreach($patients as $patient)
                        <option value="{{ $patient->id }}" @selected(old('patient_id', $selectedPatient?->id) == $patient->id)>
                            {{ $patient->full_name }} ({{ $patient->mrn }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mp-label" for="lmp">{{ __('hospital.rch.lmp') }}</label>
                <input class="mp-input" type="date" name="lmp" id="lmp" value="{{ old('lmp') }}" max="{{ today()->format('Y-m-d') }}">
                <p class="mt-1 text-xs text-ink-700/60">{{ __('hospital.rch.edd_auto') }}</p>
            </div>

            <div>
                <label class="mp-label" for="blood_group">{{ __('hospital.rch.blood_group') }}</label>
                <select class="mp-input" name="blood_group" id="blood_group">
                    <option value="">—</option>
                    @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                        <option value="{{ $bg }}" @selected(old('blood_group', $selectedPatient?->blood_group) === $bg)>{{ $bg }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mp-label" for="gravida">{{ __('hospital.rch.gravida') }}</label>
                <input class="mp-input" type="number" name="gravida" id="gravida" min="0" max="20" value="{{ old('gravida') }}">
            </div>

            <div>
                <label class="mp-label" for="para">{{ __('hospital.rch.para') }}</label>
                <input class="mp-input" type="number" name="para" id="para" min="0" max="20" value="{{ old('para') }}">
            </div>

            <div>
                <label class="mp-label" for="abortions">{{ __('hospital.rch.abortions') }}</label>
                <input class="mp-input" type="number" name="abortions" id="abortions" min="0" max="20" value="{{ old('abortions', 0) }}">
            </div>

            <div>
                <label class="mp-label" for="hiv_status">{{ __('hospital.rch.hiv_status') }}</label>
                <select class="mp-input" name="hiv_status" id="hiv_status">
                    <option value="">—</option>
                    @foreach(['negative', 'positive', 'unknown', 'not_tested'] as $status)
                        <option value="{{ $status }}" @selected(old('hiv_status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 flex flex-wrap gap-6">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="hiv_tested" value="1" @checked(old('hiv_tested'))>
                    {{ __('hospital.rch.hiv_tested') }}
                </label>
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="tt_given" value="1" @checked(old('tt_given'))>
                    {{ __('hospital.rch.tt_given') }}
                </label>
            </div>

            <div class="sm:col-span-2">
                <label class="mp-label" for="risk_factors">{{ __('hospital.rch.risk_factors') }}</label>
                <textarea class="mp-input" name="risk_factors" id="risk_factors" rows="3">{{ old('risk_factors') }}</textarea>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="mp-btn">{{ __('hospital.rch.register_pregnancy') }}</button>
            <a href="{{ route('rch.index') }}" class="mp-btn-secondary">{{ __('hospital.rch.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
