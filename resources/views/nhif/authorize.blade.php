@extends('layouts.hospital')

@section('title', __('hospital.nhif_claims.request_auth'))
@section('eyebrow', __('hospital.nhif_claims.eyebrow'))
@section('heading', __('hospital.nhif_claims.request_auth'))

@section('actions')
    <a href="{{ route('nhif.claims.index') }}" class="mp-btn-secondary">{{ __('hospital.nhif_claims.back') }}</a>
@endsection

@section('content')
@if($visit)
    <div class="mp-card mb-4 rounded-xl bg-brand-50/60 p-4 text-sm">
        <strong>{{ $visit->patient->full_name }}</strong> · {{ $visit->patient->mrn }} · {{ $visit->visit_no }}
        @if($visit->patient->nhif_card_no)
            · NHIF {{ $visit->patient->nhif_card_no }}
        @endif
    </div>
@endif

<div class="mp-card max-w-3xl">
    <form method="POST" action="{{ route('nhif.authorize.store') }}">
        @csrf

        <div class="grid gap-4 sm:grid-cols-2">
            @if($visit)
                <input type="hidden" name="patient_id" value="{{ $visit->patient_id }}">
                <input type="hidden" name="visit_id" value="{{ $visit->id }}">
            @else
                <div class="sm:col-span-2">
                    <label class="mp-label" for="patient_id">{{ __('hospital.common.patient') }} *</label>
                    <select class="mp-input" name="patient_id" id="patient_id" required>
                        <option value="">— {{ __('hospital.nhif_claims.select_patient') }} —</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->id }}" @selected(old('patient_id') == $patient->id)>
                                {{ $patient->full_name }} ({{ $patient->mrn }}) @if($patient->nhif_card_no) · NHIF {{ $patient->nhif_card_no }} @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="mp-label" for="visit_id">{{ __('hospital.nhif_claims.visit_optional') }}</label>
                    <input class="mp-input" type="number" name="visit_id" id="visit_id" value="{{ old('visit_id') }}" placeholder="Visit ID">
                </div>
            @endif

            <div class="sm:col-span-2">
                <label class="mp-label" for="diagnosis">{{ __('hospital.nhif_claims.diagnosis') }}</label>
                <textarea class="mp-input" name="diagnosis" id="diagnosis" rows="3">{{ old('diagnosis', $visit?->consultation?->diagnosis_summary) }}</textarea>
            </div>
        </div>

        @error('nhif')
            <p class="mt-3 text-sm text-red-600">{{ $message }}</p>
        @enderror

        <div class="mt-6 flex gap-3">
            <button type="submit" class="mp-btn-primary">{{ __('hospital.nhif_claims.request_auth') }}</button>
            <a href="{{ route('nhif.claims.index') }}" class="mp-btn-secondary">{{ __('hospital.common.cancel') }}</a>
        </div>
    </form>
</div>

<p class="mt-4 text-xs text-ink-700/60">{{ __('hospital.nhif.stub_note') }}</p>
@endsection
