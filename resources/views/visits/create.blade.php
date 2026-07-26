@extends('layouts.hospital')

@section('title', 'New Visit')
@section('eyebrow', 'Outpatient')
@section('heading', 'Register OPD Visit')

@section('actions')
    <a href="{{ route('visits.index') }}" class="mp-btn-secondary">Back</a>
@endsection

@section('content')
<div class="mp-card max-w-3xl">
    <form method="POST" action="{{ route('visits.store') }}">
        @csrf
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="mp-label" for="patient_id">Patient *</label>
                <select class="mp-input" name="patient_id" id="patient_id" required>
                    <option value="">— Select patient —</option>
                    @foreach($patients as $p)
                        <option value="{{ $p->id }}" @selected(old('patient_id', $patient?->id) == $p->id)>{{ $p->mrn }} — {{ $p->full_name }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-brand-700"><a href="{{ route('patients.create') }}" class="font-semibold underline">Register new patient</a></p>
            </div>
            <div>
                <label class="mp-label" for="visit_type">Visit type *</label>
                <select class="mp-input" name="visit_type" id="visit_type" required>
                    @foreach(['opd' => 'OPD', 'emergency' => 'Emergency', 'rch' => 'RCH', 'dental' => 'Dental', 'eye' => 'Eye', 'specialist' => 'Specialist', 'ipd' => 'IPD'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('visit_type', 'opd') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mp-label" for="department_id">Department</label>
                <select class="mp-input" name="department_id" id="department_id">
                    <option value="">— General —</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mp-label" for="payment_category">Payment category *</label>
                <select class="mp-input" name="payment_category" id="payment_category" required>
                    @foreach($paymentCategories as $key => $label)
                        <option value="{{ $key }}" @selected(old('payment_category', $patient?->payment_category ?? 'cash') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mp-label" for="priority">Queue priority</label>
                <select class="mp-input" name="priority" id="priority">
                    @foreach(['normal' => 'Normal', 'urgent' => 'Urgent', 'emergency' => 'Emergency'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('priority', 'normal') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="mp-label" for="chief_complaint">Chief complaint</label>
                <textarea class="mp-input" name="chief_complaint" id="chief_complaint" rows="2">{{ old('chief_complaint') }}</textarea>
            </div>
            <div class="sm:col-span-2">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="charge_consultation_fee" value="1" @checked(old('charge_consultation_fee')) class="rounded border-brand-300 text-brand-700 focus:ring-brand-500">
                    Charge OPD consultation fee on registration
                </label>
            </div>
        </div>
        <div class="mt-6 flex gap-3">
            <button type="submit" class="mp-btn">Register visit</button>
            <a href="{{ route('visits.index') }}" class="mp-btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
