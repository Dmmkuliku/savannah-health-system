@extends('layouts.hospital')

@section('title', __('hospital.rch.title'))
@section('eyebrow', __('hospital.rch.eyebrow'))
@section('heading', __('hospital.rch.title'))

@section('actions')
    <a href="{{ route('maternity.index') }}" class="mp-btn-secondary">{{ __('hospital.maternity.title') }}</a>
    <a href="{{ route('rch.immunizations.create') }}" class="mp-btn-secondary">{{ __('hospital.rch.record_immunization') }}</a>
    <a href="{{ route('rch.pregnancies.create') }}" class="mp-btn">{{ __('hospital.rch.register_pregnancy') }}</a>
@endsection

@section('content')
<div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="mp-card rounded-xl p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-ink-700/60">{{ __('hospital.rch.active_pregnancies') }}</p>
        <p class="mt-1 font-display text-2xl text-brand-800">{{ $stats['active_pregnancies'] }}</p>
    </div>
    <div class="mp-card rounded-xl p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-ink-700/60">{{ __('hospital.rch.recent_anc') }}</p>
        <p class="mt-1 font-display text-2xl text-brand-800">{{ $stats['recent_anc'] }}</p>
        <p class="text-xs text-ink-700/60">{{ __('hospital.rch.last_30_days') }}</p>
    </div>
    <div class="mp-card rounded-xl p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-ink-700/60">{{ __('hospital.rch.recent_immunizations') }}</p>
        <p class="mt-1 font-display text-2xl text-brand-800">{{ $stats['recent_immunizations'] }}</p>
        <p class="text-xs text-ink-700/60">{{ __('hospital.rch.last_30_days') }}</p>
    </div>
    <div class="mp-card rounded-xl p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-ink-700/60">{{ __('hospital.rch.due_vaccines') }}</p>
        <p class="mt-1 font-display text-2xl text-amber-700">{{ $stats['upcoming_due_vaccines'] }}</p>
    </div>
</div>

<div class="mp-card mb-6">
    <h2 class="font-display text-lg text-ink-900">{{ __('hospital.rch.quick_register') }}</h2>
    <form method="GET" action="{{ route('rch.pregnancies.create') }}" class="mt-4 flex flex-wrap items-end gap-3">
        <div class="min-w-[240px] flex-1">
            <label class="mp-label" for="patient_id">{{ __('hospital.rch.select_mother') }}</label>
            <select class="mp-input" name="patient_id" id="patient_id">
                <option value="">— {{ __('hospital.rch.select_mother') }} —</option>
                @foreach($patients as $patient)
                    <option value="{{ $patient->id }}">{{ $patient->full_name }} ({{ $patient->mrn }})</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="mp-btn">{{ __('hospital.rch.register_pregnancy') }}</button>
    </form>
</div>

<div class="mb-4 flex flex-wrap gap-2 border-b border-brand-100 pb-2">
    @foreach(['pregnancies' => __('hospital.rch.tab_pregnancies'), 'anc' => __('hospital.rch.tab_anc'), 'immunizations' => __('hospital.rch.tab_immunizations')] as $key => $label)
        <a href="{{ route('rch.index', ['tab' => $key]) }}"
           class="rounded-lg px-4 py-2 text-sm font-semibold {{ $tab === $key ? 'bg-brand-700 text-white' : 'bg-brand-50 text-brand-800 hover:bg-brand-100' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

@if($tab === 'pregnancies')
    <div class="mp-card">
        <h2 class="font-display text-lg text-ink-900">{{ __('hospital.rch.active_pregnancies') }}</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="mp-table">
                <thead>
                    <tr>
                        <th>{{ __('hospital.rch.anc_no') }}</th>
                        <th>{{ __('hospital.rch.mother') }}</th>
                        <th>{{ __('hospital.rch.lmp') }}</th>
                        <th>{{ __('hospital.rch.edd') }}</th>
                        <th>{{ __('hospital.rch.gravida_para') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-50">
                    @forelse($activePregnancies as $pregnancy)
                        <tr>
                            <td class="font-semibold text-brand-800">{{ $pregnancy->anc_no }}</td>
                            <td>{{ $pregnancy->patient->full_name }}</td>
                            <td>{{ $pregnancy->lmp?->format('d M Y') ?? '—' }}</td>
                            <td>{{ $pregnancy->edd?->format('d M Y') ?? '—' }}</td>
                            <td>G{{ $pregnancy->gravida ?? '?' }} P{{ $pregnancy->para ?? '?' }}</td>
                            <td class="text-right">
                                <a href="{{ route('rch.pregnancies.show', $pregnancy) }}" class="font-semibold text-brand-700">{{ __('hospital.rch.view') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-6 text-center text-ink-700/60">{{ __('hospital.rch.no_active_pregnancies') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@elseif($tab === 'anc')
    <div class="mp-card">
        <h2 class="font-display text-lg text-ink-900">{{ __('hospital.rch.recent_anc_visits') }}</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="mp-table">
                <thead>
                    <tr>
                        <th>{{ __('hospital.rch.visit_date') }}</th>
                        <th>{{ __('hospital.rch.mother') }}</th>
                        <th>{{ __('hospital.rch.anc_no') }}</th>
                        <th>{{ __('hospital.rch.visit_number') }}</th>
                        <th>{{ __('hospital.rch.gestational_weeks') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-50">
                    @forelse($recentAncVisits as $anc)
                        <tr>
                            <td>{{ $anc->visit_date->format('d M Y') }}</td>
                            <td>{{ $anc->patient->full_name }}</td>
                            <td>{{ $anc->pregnancy->anc_no ?? '—' }}</td>
                            <td>{{ $anc->visit_number }}</td>
                            <td>{{ $anc->gestational_weeks ?? '—' }}</td>
                            <td class="text-right">
                                @if($anc->pregnancy)
                                    <a href="{{ route('rch.pregnancies.show', $anc->pregnancy) }}" class="font-semibold text-brand-700">{{ __('hospital.rch.view') }}</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-6 text-center text-ink-700/60">{{ __('hospital.rch.no_anc_visits') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="mp-card">
        <h2 class="font-display text-lg text-ink-900">{{ __('hospital.rch.recent_immunizations') }}</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="mp-table">
                <thead>
                    <tr>
                        <th>{{ __('hospital.rch.given_at') }}</th>
                        <th>{{ __('hospital.rch.child') }}</th>
                        <th>{{ __('hospital.rch.vaccine') }}</th>
                        <th>{{ __('hospital.rch.dose') }}</th>
                        <th>{{ __('hospital.rch.batch_no') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-50">
                    @forelse($recentImmunizations as $imm)
                        <tr>
                            <td>{{ $imm->given_at->format('d M Y') }}</td>
                            <td>{{ $imm->patient->full_name }}</td>
                            <td>{{ $imm->vaccine_name }} ({{ $imm->vaccine_code }})</td>
                            <td>{{ $imm->dose ?? '—' }}</td>
                            <td>{{ $imm->batch_no ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-ink-700/60">{{ __('hospital.rch.no_immunizations') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
