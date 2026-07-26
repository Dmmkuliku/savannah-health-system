@extends('layouts.hospital')

@section('title', $delivery->delivery_no)
@section('eyebrow', __('hospital.maternity.eyebrow'))
@section('heading', $delivery->delivery_no.' — '.$delivery->patient->full_name)

@section('actions')
    <a href="{{ route('maternity.index') }}" class="mp-btn-secondary">{{ __('hospital.rch.back') }}</a>
    @if($delivery->pregnancy)
        <a href="{{ route('rch.pregnancies.show', $delivery->pregnancy) }}" class="mp-btn-secondary">{{ __('hospital.rch.view') }} ANC</a>
    @endif
@endsection

@section('content')
<div class="mp-card mb-6">
    <dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 text-sm">
        <div><dt class="text-ink-700/60">{{ __('hospital.rch.mother') }}</dt><dd class="font-semibold">{{ $delivery->patient->full_name }} ({{ $delivery->patient->mrn }})</dd></div>
        <div><dt class="text-ink-700/60">{{ __('hospital.maternity.delivered_at') }}</dt><dd>{{ $delivery->delivered_at->format('d M Y H:i') }}</dd></div>
        <div><dt class="text-ink-700/60">{{ __('hospital.maternity.delivery_type') }}</dt><dd class="capitalize">{{ str_replace('_', ' ', $delivery->delivery_type) }}</dd></div>
        <div><dt class="text-ink-700/60">{{ __('hospital.maternity.place') }}</dt><dd class="capitalize">{{ str_replace('_', ' ', $delivery->place) }}</dd></div>
        <div><dt class="text-ink-700/60">{{ __('hospital.maternity.outcome') }}</dt><dd class="capitalize">{{ str_replace('_', ' ', $delivery->outcome) }}</dd></div>
        <div><dt class="text-ink-700/60">{{ __('hospital.rch.gestational_weeks') }}</dt><dd>{{ $delivery->gestational_weeks ?? '—' }}</dd></div>
        <div><dt class="text-ink-700/60">{{ __('hospital.maternity.attendant') }}</dt><dd>{{ $delivery->attendant->name ?? '—' }}</dd></div>
        <div><dt class="text-ink-700/60">{{ __('hospital.maternity.mother_alive') }}</dt><dd>{{ $delivery->mother_alive ? __('hospital.maternity.yes') : __('hospital.maternity.no') }}</dd></div>
        @if($delivery->pregnancy)
            <div><dt class="text-ink-700/60">{{ __('hospital.rch.anc_no') }}</dt><dd>{{ $delivery->pregnancy->anc_no }}</dd></div>
        @endif
        @if($delivery->complications)
            <div class="sm:col-span-2"><dt class="text-ink-700/60">{{ __('hospital.maternity.complications') }}</dt><dd>{{ $delivery->complications }}</dd></div>
        @endif
        @if($delivery->notes)
            <div class="sm:col-span-2 lg:col-span-4"><dt class="text-ink-700/60">{{ __('hospital.rch.notes') }}</dt><dd>{{ $delivery->notes }}</dd></div>
        @endif
    </dl>
</div>

<div class="mp-card mb-6">
    <h2 class="font-display text-lg text-ink-900">{{ __('hospital.maternity.newborns') }}</h2>
    <div class="mt-4 overflow-x-auto">
        <table class="mp-table">
            <thead>
                <tr>
                    <th>{{ __('hospital.maternity.newborn_name') }}</th>
                    <th>{{ __('hospital.maternity.sex') }}</th>
                    <th>{{ __('hospital.maternity.birth_weight') }}</th>
                    <th>Apgar 1 / 5</th>
                    <th>{{ __('hospital.maternity.status') }}</th>
                    <th>BCG / OPV0</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-50">
                @forelse($delivery->newborns as $newborn)
                    <tr>
                        <td>{{ $newborn->name ?? ($newborn->patient->full_name ?? '—') }}</td>
                        <td class="capitalize">{{ $newborn->sex }}</td>
                        <td>{{ $newborn->birth_weight_kg ? number_format((float) $newborn->birth_weight_kg, 3).' kg' : '—' }}</td>
                        <td>{{ $newborn->apgar_1 ?? '—' }} / {{ $newborn->apgar_5 ?? '—' }}</td>
                        <td class="capitalize">{{ $newborn->status }}</td>
                        <td>
                            @if($newborn->bcg_given) BCG @endif
                            @if($newborn->opv0_given) OPV0 @endif
                            @if(!$newborn->bcg_given && !$newborn->opv0_given) — @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-6 text-center text-ink-700/60">{{ __('hospital.maternity.no_newborns') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-2">
    <div class="mp-card">
        <h2 class="font-display text-lg text-ink-900">{{ __('hospital.rch.record_pnc') }}</h2>
        <form method="POST" action="{{ route('rch.pnc.store') }}" class="mt-4">
            @csrf
            <input type="hidden" name="patient_id" value="{{ $delivery->patient_id }}">
            <input type="hidden" name="delivery_id" value="{{ $delivery->id }}">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mp-label" for="visit_date">{{ __('hospital.rch.visit_date') }} *</label>
                    <input class="mp-input" type="date" name="visit_date" id="visit_date" value="{{ today()->format('Y-m-d') }}" required max="{{ today()->format('Y-m-d') }}">
                </div>
                <div>
                    <label class="mp-label" for="days_postpartum">{{ __('hospital.rch.days_postpartum') }}</label>
                    <input class="mp-input" type="number" name="days_postpartum" id="days_postpartum" min="0" max="365">
                </div>
                <div>
                    <label class="mp-label" for="mother_condition">{{ __('hospital.rch.mother_condition') }}</label>
                    <input class="mp-input" type="text" name="mother_condition" id="mother_condition">
                </div>
                <div>
                    <label class="mp-label" for="baby_condition">{{ __('hospital.rch.baby_condition') }}</label>
                    <input class="mp-input" type="text" name="baby_condition" id="baby_condition">
                </div>
                <div class="sm:col-span-2 flex flex-wrap gap-6">
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="breastfeeding" value="1">
                        {{ __('hospital.rch.breastfeeding') }}
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="family_planning_counselled" value="1">
                        {{ __('hospital.rch.family_planning') }}
                    </label>
                </div>
                <div class="sm:col-span-2">
                    <label class="mp-label" for="pnc_notes">{{ __('hospital.rch.notes') }}</label>
                    <textarea class="mp-input" name="notes" id="pnc_notes" rows="2"></textarea>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="mp-btn">{{ __('hospital.rch.record_pnc') }}</button>
            </div>
        </form>
    </div>

    <div class="mp-card">
        <h2 class="font-display text-lg text-ink-900">{{ __('hospital.rch.pnc_visits') }}</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="mp-table">
                <thead>
                    <tr>
                        <th>{{ __('hospital.rch.visit_date') }}</th>
                        <th>{{ __('hospital.rch.days_postpartum') }}</th>
                        <th>{{ __('hospital.rch.mother_condition') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-50">
                    @forelse($delivery->pncVisits as $pnc)
                        <tr>
                            <td>{{ $pnc->visit_date->format('d M Y') }}</td>
                            <td>{{ $pnc->days_postpartum ?? '—' }}</td>
                            <td>{{ $pnc->mother_condition ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-6 text-center text-ink-700/60">{{ __('hospital.rch.no_pnc_visits') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
