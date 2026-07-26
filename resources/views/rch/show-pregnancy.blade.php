@extends('layouts.hospital')

@section('title', $pregnancy->anc_no)
@section('eyebrow', __('hospital.rch.eyebrow'))
@section('heading', $pregnancy->anc_no.' — '.$pregnancy->patient->full_name)

@section('actions')
    <a href="{{ route('maternity.create', ['patient_id' => $pregnancy->patient_id, 'rch_pregnancy_id' => $pregnancy->id]) }}" class="mp-btn-secondary">{{ __('hospital.maternity.record_delivery') }}</a>
    <a href="{{ route('rch.index') }}" class="mp-btn-secondary">{{ __('hospital.rch.back') }}</a>
@endsection

@section('content')
<div class="mp-card mb-6">
    <dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 text-sm">
        <div><dt class="text-ink-700/60">{{ __('hospital.rch.mother') }}</dt><dd class="font-semibold">{{ $pregnancy->patient->full_name }} ({{ $pregnancy->patient->mrn }})</dd></div>
        <div><dt class="text-ink-700/60">{{ __('hospital.rch.lmp') }}</dt><dd>{{ $pregnancy->lmp?->format('d M Y') ?? '—' }}</dd></div>
        <div><dt class="text-ink-700/60">{{ __('hospital.rch.edd') }}</dt><dd>{{ $pregnancy->edd?->format('d M Y') ?? '—' }}</dd></div>
        <div><dt class="text-ink-700/60">{{ __('hospital.rch.gravida_para') }}</dt><dd>G{{ $pregnancy->gravida ?? '?' }} P{{ $pregnancy->para ?? '?' }} · A{{ $pregnancy->abortions }}</dd></div>
        <div><dt class="text-ink-700/60">{{ __('hospital.rch.blood_group') }}</dt><dd>{{ $pregnancy->blood_group ?? '—' }}</dd></div>
        <div><dt class="text-ink-700/60">{{ __('hospital.rch.hiv_status') }}</dt><dd>{{ $pregnancy->hiv_status ? ucfirst(str_replace('_', ' ', $pregnancy->hiv_status)) : '—' }}</dd></div>
        <div><dt class="text-ink-700/60">Status</dt><dd><span class="mp-badge bg-brand-50 text-brand-800">{{ $pregnancy->status }}</span></dd></div>
        <div><dt class="text-ink-700/60">{{ __('hospital.rch.registered_by') }}</dt><dd>{{ $pregnancy->registeredBy->name ?? '—' }}</dd></div>
        @if($pregnancy->risk_factors)
            <div class="sm:col-span-2 lg:col-span-4"><dt class="text-ink-700/60">{{ __('hospital.rch.risk_factors') }}</dt><dd>{{ $pregnancy->risk_factors }}</dd></div>
        @endif
    </dl>
</div>

@if($pregnancy->status === 'active')
    <div class="mp-card mb-6">
        <h2 class="font-display text-lg text-ink-900">{{ __('hospital.rch.record_anc') }}</h2>
        <form method="POST" action="{{ route('rch.anc.store', $pregnancy) }}" class="mt-4">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="mp-label" for="visit_date">{{ __('hospital.rch.visit_date') }} *</label>
                    <input class="mp-input" type="date" name="visit_date" id="visit_date" value="{{ old('visit_date', today()->format('Y-m-d')) }}" required max="{{ today()->format('Y-m-d') }}">
                </div>
                <div>
                    <label class="mp-label" for="weight_kg">{{ __('hospital.rch.weight_kg') }}</label>
                    <input class="mp-input" type="number" step="0.1" name="weight_kg" id="weight_kg" value="{{ old('weight_kg') }}">
                </div>
                <div>
                    <label class="mp-label" for="hb_gdl">{{ __('hospital.rch.hb_gdl') }}</label>
                    <input class="mp-input" type="number" step="0.1" name="hb_gdl" id="hb_gdl" value="{{ old('hb_gdl') }}">
                </div>
                <div>
                    <label class="mp-label" for="bp_systolic">{{ __('hospital.rch.bp_systolic') }}</label>
                    <input class="mp-input" type="number" name="bp_systolic" id="bp_systolic" value="{{ old('bp_systolic') }}">
                </div>
                <div>
                    <label class="mp-label" for="bp_diastolic">{{ __('hospital.rch.bp_diastolic') }}</label>
                    <input class="mp-input" type="number" name="bp_diastolic" id="bp_diastolic" value="{{ old('bp_diastolic') }}">
                </div>
                <div>
                    <label class="mp-label" for="urine_protein">{{ __('hospital.rch.urine_protein') }}</label>
                    <input class="mp-input" type="text" name="urine_protein" id="urine_protein" value="{{ old('urine_protein') }}">
                </div>
                <div class="sm:col-span-2 lg:col-span-3 flex flex-wrap gap-6">
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="fetal_heart_heard" value="1" @checked(old('fetal_heart_heard'))>
                        {{ __('hospital.rch.fetal_heart') }}
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="ipt_given" value="1" @checked(old('ipt_given'))>
                        {{ __('hospital.rch.ipt_given') }}
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="iron_folate_given" value="1" @checked(old('iron_folate_given'))>
                        {{ __('hospital.rch.iron_folate') }}
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="mosquito_net_given" value="1" @checked(old('mosquito_net_given'))>
                        {{ __('hospital.rch.mosquito_net') }}
                    </label>
                </div>
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="mp-label" for="notes">{{ __('hospital.rch.notes') }}</label>
                    <textarea class="mp-input" name="notes" id="notes" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="mp-btn">{{ __('hospital.rch.record_anc') }}</button>
            </div>
        </form>
    </div>
@endif

<div class="mp-card">
    <h2 class="font-display text-lg text-ink-900">{{ __('hospital.rch.anc_visits') }}</h2>
    <div class="mt-4 overflow-x-auto">
        <table class="mp-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('hospital.rch.visit_date') }}</th>
                    <th>{{ __('hospital.rch.gestational_weeks') }}</th>
                    <th>{{ __('hospital.rch.weight_kg') }}</th>
                    <th>BP</th>
                    <th>{{ __('hospital.rch.hb_gdl') }}</th>
                    <th>{{ __('hospital.rch.attended_by') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-50">
                @forelse($pregnancy->ancVisits as $anc)
                    <tr>
                        <td class="font-semibold text-brand-800">{{ $anc->visit_number }}</td>
                        <td>{{ $anc->visit_date->format('d M Y') }}</td>
                        <td>{{ $anc->gestational_weeks ?? '—' }}</td>
                        <td>{{ $anc->weight_kg ?? '—' }}</td>
                        <td>{{ $anc->bp_systolic && $anc->bp_diastolic ? $anc->bp_systolic.'/'.$anc->bp_diastolic : '—' }}</td>
                        <td>{{ $anc->hb_gdl ?? '—' }}</td>
                        <td>{{ $anc->attendedBy->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-6 text-center text-ink-700/60">{{ __('hospital.rch.no_anc_visits') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($pregnancy->deliveries->isNotEmpty())
    <div class="mp-card mt-6">
        <h2 class="font-display text-lg text-ink-900">{{ __('hospital.maternity.deliveries') }}</h2>
        <ul class="mt-4 space-y-2 text-sm">
            @foreach($pregnancy->deliveries as $delivery)
                <li>
                    <a href="{{ route('maternity.show', $delivery) }}" class="font-semibold text-brand-700">{{ $delivery->delivery_no }}</a>
                    · {{ $delivery->delivered_at->format('d M Y H:i') }}
                </li>
            @endforeach
        </ul>
    </div>
@endif
@endsection
