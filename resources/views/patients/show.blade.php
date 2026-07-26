@extends('layouts.hospital')

@section('title', $patient->mrn)
@section('eyebrow', 'Patient record')
@section('heading', $patient->full_name)

@section('actions')
    <a href="{{ route('patients.edit', $patient) }}" class="mp-btn-secondary">Edit</a>
    <a href="{{ route('visits.create', ['patient_id' => $patient->id]) }}" class="mp-btn">Start visit</a>
@endsection

@section('content')
<div class="grid gap-6 lg:grid-cols-3">
    <div class="mp-card lg:col-span-2">
        <h2 class="font-display text-lg text-ink-900">Demographics</h2>
        <dl class="mt-4 grid gap-3 sm:grid-cols-2 text-sm">
            <div><dt class="text-ink-700/60">MRN</dt><dd class="font-semibold text-brand-800">{{ $patient->mrn }}</dd></div>
            <div><dt class="text-ink-700/60">Gender</dt><dd class="capitalize">{{ $patient->gender }}</dd></div>
            <div><dt class="text-ink-700/60">Date of birth</dt><dd>{{ $patient->date_of_birth?->format('d M Y') ?? '—' }}</dd></div>
            <div><dt class="text-ink-700/60">Age</dt><dd>{{ $patient->age_years ? $patient->age_years.' years' : '—' }}</dd></div>
            <div><dt class="text-ink-700/60">Phone</dt><dd>{{ $patient->phone ?? '—' }}</dd></div>
            <div><dt class="text-ink-700/60">{{ __('hospital.nhif.card_no') }}</dt><dd>{{ $patient->nhif_card_no ?? '—' }}</dd></div>
            <div><dt class="text-ink-700/60">{{ __('hospital.nhif.member') }}</dt><dd>{{ $patient->nhif_member_name ?? '—' }}</dd></div>
            <div><dt class="text-ink-700/60">NHIF status</dt>
                <dd>
                    @if($patient->nhif_status === 'active')
                        <span class="mp-badge bg-brand-100 text-brand-800">{{ __('hospital.nhif.verified') }}</span>
                    @elseif($patient->nhif_status)
                        <span class="mp-badge bg-red-100 text-red-700">{{ $patient->nhif_status }}</span>
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div><dt class="text-ink-700/60">Payment category</dt><dd class="capitalize">{{ str_replace('_', ' ', $patient->payment_category) }}</dd></div>
            @if($patient->exemption_type)
                <div><dt class="text-ink-700/60">Exemption (Msamaha)</dt><dd>{{ str_replace('_', ' ', $patient->exemption_type) }}</dd></div>
            @endif
            <div><dt class="text-ink-700/60">Region</dt><dd>{{ $patient->region ?? '—' }}</dd></div>
            <div><dt class="text-ink-700/60">District / Ward</dt><dd>{{ collect([$patient->district, $patient->ward_village])->filter()->implode(', ') ?: '—' }}</dd></div>
            <div><dt class="text-ink-700/60">Next of kin</dt><dd>{{ $patient->next_of_kin ?? '—' }} {{ $patient->next_of_kin_phone ? '('.$patient->next_of_kin_phone.')' : '' }}</dd></div>
            <div><dt class="text-ink-700/60">Blood group</dt><dd>{{ $patient->blood_group ?? '—' }}</dd></div>
        </dl>
        @if($patient->allergies)
            <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                <strong>Allergies:</strong> {{ $patient->allergies }}
            </div>
        @endif
        @if($patient->chronic_conditions)
            <div class="mt-3 rounded-xl border border-sand-200 bg-sand-50 p-3 text-sm">
                <strong>Chronic conditions:</strong> {{ $patient->chronic_conditions }}
            </div>
        @endif
    </div>

    <div class="space-y-6">
        <div class="mp-card">
            <h2 class="font-display text-lg text-ink-900">{{ __('hospital.nhif.verify') }}</h2>
            <p class="mt-1 text-xs text-ink-700/60">{{ __('hospital.nhif.stub_note') }}</p>
            <form method="POST" action="{{ route('nhif.verify', $patient) }}" class="mt-4 space-y-3">
                @csrf
                <div>
                    <label class="mp-label" for="nhif_card_no">{{ __('hospital.nhif.card_no') }}</label>
                    <input class="mp-input" type="text" name="nhif_card_no" id="nhif_card_no" value="{{ old('nhif_card_no', $patient->nhif_card_no) }}" placeholder="e.g. 10123456789">
                </div>
                @if($patient->nhif_verified_at)
                    <p class="text-xs text-brand-700">{{ __('hospital.nhif.last_check') }}: {{ $patient->nhif_verified_at->format('d M Y H:i') }}</p>
                @endif
                <button type="submit" class="mp-btn w-full">{{ __('hospital.nhif.verify') }}</button>
            </form>
        </div>

        <div class="mp-card">
            <h2 class="font-display text-lg text-ink-900">Recent visits</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @forelse($patient->visits as $visit)
                    <li class="flex items-center justify-between rounded-lg bg-brand-50/50 px-3 py-2">
                        <span>{{ $visit->visit_no }}</span>
                        <a href="{{ route('visits.show', $visit) }}" class="font-semibold text-brand-700">Open</a>
                    </li>
                @empty
                    <li class="text-ink-700/60">No visits yet.</li>
                @endforelse
            </ul>
        </div>

        <div class="mp-card">
            <h2 class="font-display text-lg text-ink-900">Admissions</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @forelse($patient->admissions as $admission)
                    <li class="rounded-lg bg-sand-50 px-3 py-2">
                        {{ $admission->admission_no }}
                        <span class="mp-badge ml-2 bg-brand-50 text-brand-800">{{ $admission->status }}</span>
                    </li>
                @empty
                    <li class="text-ink-700/60">No admissions.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
