@extends('layouts.hospital')

@section('title', __('hospital.rch.record_immunization'))
@section('eyebrow', __('hospital.rch.eyebrow'))
@section('heading', __('hospital.rch.record_immunization'))

@section('actions')
    <a href="{{ route('rch.index', ['tab' => 'immunizations']) }}" class="mp-btn-secondary">{{ __('hospital.rch.back') }}</a>
@endsection

@section('content')
<div class="mp-card max-w-2xl">
    <form method="POST" action="{{ route('rch.immunizations.store') }}">
        @csrf
        <div class="space-y-4">
            <div>
                <label class="mp-label" for="patient_id">{{ __('hospital.rch.child') }} *</label>
                <select class="mp-input" name="patient_id" id="patient_id" required>
                    <option value="">— {{ __('hospital.rch.select_child') }} —</option>
                    @foreach($patients as $patient)
                        <option value="{{ $patient->id }}" @selected(old('patient_id', $selectedPatient?->id) == $patient->id)>
                            {{ $patient->full_name }} ({{ $patient->mrn }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mp-label" for="vaccine_code">{{ __('hospital.rch.vaccine') }} *</label>
                <select class="mp-input" name="vaccine_code" id="vaccine_code" required>
                    <option value="">— {{ __('hospital.rch.select_vaccine') }} —</option>
                    @foreach($vaccines as $vaccine)
                        <option value="{{ $vaccine['code'] }}" @selected(old('vaccine_code') === $vaccine['code'])>
                            {{ $vaccine['code'] }} — {{ $vaccine['name'] }} ({{ __('hospital.rch.dose') }} {{ $vaccine['dose'] }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mp-label" for="given_at">{{ __('hospital.rch.given_at') }} *</label>
                <input class="mp-input" type="date" name="given_at" id="given_at" value="{{ old('given_at', today()->format('Y-m-d')) }}" required max="{{ today()->format('Y-m-d') }}">
            </div>

            <div>
                <label class="mp-label" for="batch_no">{{ __('hospital.rch.batch_no') }}</label>
                <input class="mp-input" type="text" name="batch_no" id="batch_no" value="{{ old('batch_no') }}">
            </div>

            <div>
                <label class="mp-label" for="notes">{{ __('hospital.rch.notes') }}</label>
                <textarea class="mp-input" name="notes" id="notes" rows="3">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="mp-btn">{{ __('hospital.rch.record_immunization') }}</button>
            <a href="{{ route('rch.index') }}" class="mp-btn-secondary">{{ __('hospital.rch.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
