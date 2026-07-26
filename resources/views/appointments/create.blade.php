@extends('layouts.hospital')

@section('title', 'Schedule Appointment')
@section('eyebrow', 'Scheduling')
@section('heading', 'Schedule Appointment')

@section('actions')
    <a href="{{ route('appointments.index') }}" class="mp-btn-secondary">Back</a>
@endsection

@section('content')
<div class="mp-card max-w-2xl">
    <form method="POST" action="{{ route('appointments.store') }}">
        @csrf
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="mp-label" for="patient_id">Patient *</label>
                <select class="mp-input" name="patient_id" id="patient_id" required>
                    <option value="">— Select patient —</option>
                    @foreach($patients as $p)
                        <option value="{{ $p->id }}" @selected(old('patient_id') == $p->id)>{{ $p->mrn }} — {{ $p->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mp-label" for="doctor_id">Doctor</label>
                <select class="mp-input" name="doctor_id" id="doctor_id">
                    <option value="">— Any / TBD —</option>
                    @foreach($doctors as $doc)
                        <option value="{{ $doc->id }}" @selected(old('doctor_id') == $doc->id)>{{ $doc->name }}</option>
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
                <label class="mp-label" for="appointment_date">Date *</label>
                <input class="mp-input" type="date" name="appointment_date" id="appointment_date" value="{{ old('appointment_date', now()->toDateString()) }}" required>
            </div>
            <div>
                <label class="mp-label" for="appointment_time">Time</label>
                <input class="mp-input" type="time" name="appointment_time" id="appointment_time" value="{{ old('appointment_time') }}">
            </div>
            <div class="sm:col-span-2">
                <label class="mp-label" for="reason">Reason / notes</label>
                <textarea class="mp-input" name="reason" id="reason" rows="2">{{ old('reason') }}</textarea>
            </div>
        </div>
        <div class="mt-6 flex gap-3">
            <button type="submit" class="mp-btn">Schedule</button>
            <a href="{{ route('appointments.index') }}" class="mp-btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
