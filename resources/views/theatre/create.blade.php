@extends('layouts.hospital')

@section('title', __('hospital.theatre.schedule'))
@section('eyebrow', __('hospital.theatre.eyebrow'))
@section('heading', __('hospital.theatre.schedule'))

@section('actions')
    <a href="{{ route('theatre.index') }}" class="mp-btn-secondary">{{ __('hospital.theatre.cases') }}</a>
@endsection

@section('content')
@if($visit)
    <div class="mp-card mb-4 rounded-xl bg-brand-50/60 p-4 text-sm">
        <strong>{{ $visit->patient->full_name }}</strong> · {{ $visit->patient->mrn }} · Visit {{ $visit->visit_no }}
    </div>
@endif

<div class="mp-card max-w-3xl">
    <form method="POST" action="{{ route('theatre.store') }}">
        @csrf
        @if($visit)
            <input type="hidden" name="visit_id" value="{{ $visit->id }}">
            <input type="hidden" name="patient_id" value="{{ $visit->patient_id }}">
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            @unless($visit)
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

            <div class="sm:col-span-2">
                <label class="mp-label" for="procedure_name">{{ __('hospital.theatre.procedure') }} *</label>
                <input class="mp-input" type="text" name="procedure_name" id="procedure_name" value="{{ old('procedure_name') }}" required>
            </div>

            <div>
                <label class="mp-label" for="urgency">{{ __('hospital.theatre.urgency') }} *</label>
                <select class="mp-input" name="urgency" id="urgency" required>
                    @foreach(['elective', 'urgent', 'emergency'] as $level)
                        <option value="{{ $level }}" @selected(old('urgency', 'elective') === $level)>{{ ucfirst($level) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mp-label" for="scheduled_at">Scheduled at</label>
                <input class="mp-input" type="datetime-local" name="scheduled_at" id="scheduled_at" value="{{ old('scheduled_at') }}">
            </div>

            <div>
                <label class="mp-label" for="theatre_room_id">{{ __('hospital.theatre.rooms') }}</label>
                <select class="mp-input" name="theatre_room_id" id="theatre_room_id">
                    <option value="">— Select room —</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" @selected(old('theatre_room_id') == $room->id)>
                            {{ $room->code }} — {{ $room->name_sw ?? $room->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mp-label" for="surgeon_id">{{ __('hospital.theatre.surgeon') }}</label>
                <select class="mp-input" name="surgeon_id" id="surgeon_id">
                    <option value="">— Select surgeon —</option>
                    @foreach($doctors as $doctor)
                        <option value="{{ $doctor->id }}" @selected(old('surgeon_id') == $doctor->id)>{{ $doctor->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mp-label" for="anaesthetist_id">{{ __('hospital.theatre.anaesthetist') }}</label>
                <select class="mp-input" name="anaesthetist_id" id="anaesthetist_id">
                    <option value="">— Select anaesthetist —</option>
                    @foreach($doctors as $doctor)
                        <option value="{{ $doctor->id }}" @selected(old('anaesthetist_id') == $doctor->id)>{{ $doctor->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mp-label" for="asa_class">ASA class</label>
                <select class="mp-input" name="asa_class" id="asa_class">
                    <option value="">—</option>
                    @foreach(['I', 'II', 'III', 'IV', 'V', 'E'] as $asa)
                        <option value="{{ $asa }}" @selected(old('asa_class') === $asa)>{{ $asa }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mp-label" for="diagnosis">Diagnosis</label>
                <input class="mp-input" type="text" name="diagnosis" id="diagnosis" value="{{ old('diagnosis') }}">
            </div>

            <div class="sm:col-span-2">
                <label class="mp-label" for="pre_op_notes">Pre-op notes</label>
                <textarea class="mp-input" name="pre_op_notes" id="pre_op_notes" rows="3">{{ old('pre_op_notes') }}</textarea>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="mp-btn">{{ __('hospital.theatre.schedule') }}</button>
            <a href="{{ route('theatre.index') }}" class="mp-btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
